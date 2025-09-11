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
 * Provides support for the conversion of moodle1 backup to the moodle2 format
 *
 * @package    mod_deffinum
 * @copyright  2011 Aparup Banerjee <nebgor@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Deffinum conversion handler
 */
class moodle1_mod_deffinum_handler extends moodle1_mod_handler {

    /** @var moodle1_file_manager */
    protected $fileman = null;

    /** @var int cmid */
    protected $moduleid = null;

    /**
     * Declare the paths in moodle.xml we are able to convert
     *
     * The method returns list of {@link convert_path} instances.
     * For each path returned, the corresponding conversion method must be
     * defined.
     *
     * Note that the path /MOODLE_BACKUP/COURSE/MODULES/MOD/DEFFINUM does not
     * actually exist in the file. The last element with the module name was
     * appended by the moodle1_converter class.
     *
     * @return array of {@link convert_path} instances
     */
    public function get_paths() {
        return array(
            new convert_path('deffinum', '/MOODLE_BACKUP/COURSE/MODULES/MOD/DEFFINUM',
                array(
                    'newfields' => array(
                        'whatgrade' => 0,
                        'deffinumtype' => 'local',
                        'sha1hash' => null,
                        'revision' => '0',
                        'forcecompleted' => 0,
                        'forcenewattempt' => 0,
                        'lastattemptlock' => 0,
                        'masteryoverride' => 1,
                        'displayattemptstatus' => 1,
                        'displaycoursestructure' => 0,
                        'timeopen' => '0',
                        'timeclose' => '0',
                        'introformat' => '0',
                        'completionstatusallscos' => 0,
                    ),
                    'renamefields' => array(
                        'summary' => 'intro'
                    )
                )
            ),
            new convert_path('deffinum_sco', '/MOODLE_BACKUP/COURSE/MODULES/MOD/DEFFINUM/SCOES/SCO')
        );
    }

    /**
     * This is executed every time we have one /MOODLE_BACKUP/COURSE/MODULES/MOD/DEFFINUM
     * data available
     */
    public function process_deffinum($data) {
        global $CFG;

        // get the course module id and context id
        $instanceid     = $data['id'];
        $currentcminfo  = $this->get_cminfo($instanceid);
        $this->moduleid = $currentcminfo['id'];
        $contextid      = $this->converter->get_contextid(CONTEXT_MODULE, $this->moduleid);

        // conditionally migrate to html format in intro
        if ($CFG->texteditors !== 'textarea') {
            $data['intro']       = text_to_html($data['intro'], false, false, true);
            $data['introformat'] = FORMAT_HTML;
        }

        // get a fresh new file manager for this instance
        $this->fileman = $this->converter->get_file_manager($contextid, 'mod_deffinum');

        // convert course files embedded into the intro
        $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $data['intro'] = moodle1_converter::migrate_referenced_files($data['intro'], $this->fileman);

        // check 1.9 version where backup was created
        $backupinfo = $this->converter->get_stash('backup_info');
        if ($backupinfo['moodle_version'] < 2007110503) {
            // as we have no module version data, assume $currmodule->version <= $module->version
            // - fix data as the source 1.9 build hadn't yet at time of backing up.
            $data['grademethod'] = $data['grademethod']%10;
        }

        // update deffinumtype (logic is consistent as done in deffinum/db/upgrade.php)
        $ismanifest = preg_match('/imsmanifest\.xml$/', $data['reference']);
        $iszippif = preg_match('/.(zip|pif)$/', $data['reference']);
        $isurl = preg_match('/^((http|https):\/\/|www\.)/', $data['reference']);
        if ($isurl) {
            if ($ismanifest) {
                $data['deffinumtype'] = 'external';
            } else if ($iszippif) {
                $data['deffinumtype'] = 'localtype';
            }
        }

        // migrate deffinum package file
        $this->fileman->filearea = 'package';
        $this->fileman->itemid   = 0;
        $this->fileman->migrate_file('course_files/'.$data['reference']);

        // start writing deffinum.xml
        $this->open_xml_writer("activities/deffinum_{$this->moduleid}/deffinum.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $this->moduleid,
            'modulename' => 'deffinum', 'contextid' => $contextid));
        $this->xmlwriter->begin_tag('deffinum', array('id' => $instanceid));

        foreach ($data as $field => $value) {
            if ($field <> 'id') {
                $this->xmlwriter->full_tag($field, $value);
            }
        }

        $this->xmlwriter->begin_tag('scoes');

        return $data;
    }

    /**
     * This is executed every time we have one /MOODLE_BACKUP/COURSE/MODULES/MOD/DEFFINUM/SCOES/SCO
     * data available
     */
    public function process_deffinum_sco($data) {
        $this->write_xml('sco', $data, array('/sco/id'));
    }

    /**
     * This is executed when we reach the closing </MOD> tag of our 'deffinum' path
     */
    public function on_deffinum_end() {
        // close deffinum.xml
        $this->xmlwriter->end_tag('scoes');
        $this->xmlwriter->end_tag('deffinum');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

        // write inforef.xml
        $this->open_xml_writer("activities/deffinum_{$this->moduleid}/inforef.xml");
        $this->xmlwriter->begin_tag('inforef');
        $this->xmlwriter->begin_tag('fileref');
        foreach ($this->fileman->get_fileids() as $fileid) {
            $this->write_xml('file', array('id' => $fileid));
        }
        $this->xmlwriter->end_tag('fileref');
        $this->xmlwriter->end_tag('inforef');
        $this->close_xml_writer();
    }
}
