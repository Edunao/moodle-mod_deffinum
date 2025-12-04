<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Test file.
 *
 * @package    deffinumreport_detailed
 * @copyright  2025 Edunao SAS (contact@edunao.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
declare(strict_types=1);

require_once(__DIR__.'/../../../../../config.php');
require_once($CFG->dirroot.'/mod/deffinum/report/detailed/classes/json_parser.php');

$relpath = optional_param('file', '', PARAM_RAW);
$basedir = __DIR__.'/data';

/**
 * List files.
 * @param string $dir
 * @param string $prefix
 * @return array
 */
function list_files(string $dir, string $prefix=''): array {
    $out = [];
    foreach (scandir($dir) as $e) {
        if ($e === '.' || $e === '..') {
            continue;
        }
        $p = "$dir/$e"; $r = ltrim("$prefix/$e", '/');
        if (is_dir($p)) {
            $out = array_merge($out, list_files($p, $r));
        } else if (substr($e, -5) === '.json') {
            $out[] = $r;
        }
    }
    return $out;
}
$allfiles = list_files($basedir);

$context = context_system::instance();
require_login();
require_capability('moodle/site:config', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/mod/deffinum/report/detailed/tests/test.php', ['file' => $relpath]));
$PAGE->set_title('DEFFINUM Report Tester');
$PAGE->set_heading('DEFFINUM Report Tester');
$PAGE->set_pagelayout('report');

echo $OUTPUT->header();

echo html_writer::tag('h3', get_string('selectdatafile', 'deffinumreport_detailed'));
$items = [];
foreach ($allfiles as $f) {
    $items[] = html_writer::link(
        new moodle_url($PAGE->url, ['file' => $f]),
        s($f)
    );
}
echo html_writer::alist($items);

if ($relpath !== '') {
    $fullpath = realpath("$basedir/$relpath");
    if (!$fullpath || strpos($fullpath, realpath($basedir)) !== 0) {
        echo $OUTPUT->notification('Invalid file path', 'notifyproblem');
    } else if (!is_readable($fullpath)) {
        echo $OUTPUT->notification('File not readable', 'notifyproblem');
    } else {
        try {
            $raw = file_get_contents($fullpath);
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

            if (isset($data['detailed_progress'])) {
                // Format “complet” : on extrait la partie attendue.
                $templatecontext = deffinumreport_detailed\json_parser::parse_from_string(
                    json_encode($data['detailed_progress'])
                );
                // On fusionne d’éventuelles infos CMI.
                if (isset($data['cmi']) && is_array($data['cmi'])) {
                    $templatecontext['cmi'] =
                        array_merge($templatecontext['cmi'] ?? [], $data['cmi']);
                }
                // Normalise cmi.score {raw,max} -> cmi.score_raw / cmi.score_max -------.
                if (isset($templatecontext['cmi']['score']) && is_array($templatecontext['cmi']['score'])) {
                    $score = $templatecontext['cmi']['score'];
                    if (isset($score['raw'])) {
                        $templatecontext['cmi']['score_raw'] = $score['raw'];
                    }
                    if (isset($score['max'])) {
                        $templatecontext['cmi']['score_max'] = $score['max'];
                    }
                    unset($templatecontext['cmi']['score']); // On retire le bloc original.
                }
            } else {
                // Format “DB” : le fichier EST déjà detailed_progress.
                $templatecontext = deffinumreport_detailed\json_parser::parse_from_string($raw);
            }

            if (!empty($templatecontext['cmi']['completion_status'])) {
                $rawstatus = str_replace(' ', '',
                    $templatecontext['cmi']['completion_status']);
                $templatecontext['cmi']['completion_status'] =
                    get_string($rawstatus, 'deffinumreport_detailed');
                $templatecontext['completion_'.$rawstatus] = true;
            }
            if (isset($templatecontext['cmi']['progress_measure'])) {
                $templatecontext['cmi']['progress_measure'] =
                    round(floatval($templatecontext['cmi']['progress_measure']) * 100, 2);
            }

            foreach ([
                         'canviewcorrectorder', 'canviewlevel', 'canviewnbtry',
                         'canviewscore', 'canviewscoremax', 'canviewtimespent',
                     ] as $cap) {
                $templatecontext[$cap] = true;
            }

            $templatecontext['title'] = get_string('renderingfile', 'deffinumreport_detailed', $relpath);
            echo $OUTPUT->render_from_template('deffinumreport_detailed/report', $templatecontext);

            // Aide développeur : contenus à insérer en BDD.
            echo html_writer::empty_tag('hr');
            echo html_writer::tag('h4',
                get_string('dev_rawdbheader', 'deffinumreport_detailed'));

            $dbvalues = [
                // CMI / Score.
                'cmi.completion_status'   => $data['cmi']['completion_status'] ?? null,
                'cmi.progress_measure'    => $data['cmi']['progress_measure'] ?? null,
                'cmi.session_time'        => $data['cmi']['session_time'] ?? null,
                'cmi.score.raw'           => $data['cmi']['score']['raw'] ?? null,
                'cmi.score.max'           => $data['cmi']['score']['max'] ?? null,

                // Arborescence détaillée : chaîne JSON.
                'detailed_progress'       => json_encode(
                    $data['detailed_progress'],
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                ),
            ];

            $rows = [];
            foreach ($dbvalues as $element => $value) {
                if ($value === null) {
                    continue;
                }
                $label = get_string('db_'.$element, 'deffinumreport_detailed');
                if ($element === 'detailed_progress') {
                    // Montre le JSON dans un bloc <pre>.
                    $pretty = json_encode(json_decode($value, true),
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $rows[] = html_writer::tag('tr',
                        html_writer::tag('th', $label).
                        html_writer::tag('td', html_writer::tag('pre', s($pretty)))
                    );
                } else {
                    // Valeur scalaire.
                    $rows[] = html_writer::tag('tr',
                        html_writer::tag('th', $label).
                        html_writer::tag('td', s((string)$value))
                    );
                }
            }

            echo html_writer::tag('table', implode("\n", $rows),
                ['class' => 'table table-striped w-auto']);

        } catch (\Throwable $e) {
            echo $OUTPUT->notification($e->getMessage(), 'notifyproblem');
            echo $OUTPUT->notification(nl2br($e->getTraceAsString()), 'notifyproblem');
        }
    }
}

echo $OUTPUT->footer();
