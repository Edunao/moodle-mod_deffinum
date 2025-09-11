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
 * Returns an array of reports to which are currently readable.
 * @package    mod_deffinum
 * @author     Ankit Kumar Agarwal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* Generates and returns list of available Deffinum report sub-plugins
 *
 * @param context context level to check caps against
 * @return array list of valid reports present
 */
function deffinum_report_list($context) {
    global $CFG;
    static $reportlist;
    if (!empty($reportlist)) {
        return $reportlist;
    }
    $installed = core_component::get_plugin_list('deffinumreport');
    foreach ($installed as $reportname => $notused) {

        // Moodle 2.8+ style of autoloaded classes.
        $classname = "deffinumreport_$reportname\\report";
        if (class_exists($classname)) {
            $report = new $classname();

            if ($report->canview($context)) {
                $reportlist[] = $reportname;
            }
            continue;
        }

        // Legacy style of naming classes.
        $pluginfile = $CFG->dirroot.'/mod/deffinum/report/'.$reportname.'/report.php';
        if (is_readable($pluginfile)) {
            debugging("Please use autoloaded classnames for your plugin. Refer MDL-46469 for details", DEBUG_DEVELOPER);
            include_once($pluginfile);
            $reportclassname = "deffinum_{$reportname}_report";
            if (class_exists($reportclassname)) {
                $report = new $reportclassname();

                if ($report->canview($context)) {
                    $reportlist[] = $reportname;
                }
            }
        }
    }
    return $reportlist;
}
/**
 * Returns The maximum numbers of Questions associated with an Deffinum Pack
 *
 * @param int Deffinum ID
 * @return int an integer representing the question count
 */
function get_deffinum_question_count($deffinumid) {
    global $DB;
    $count = 0;
    $params = array();
    $sql = "SELECT DISTINCT e.id, e.element
              FROM {deffinum_element} e
              JOIN {deffinum_scoes_value} v ON e.id = v.elementid
              JOIN {deffinum_attempt} a ON a.id = v.attemptid
             WHERE a.deffinumid = ? AND ". $DB->sql_like("element", "?", false) .
        " ORDER BY e.element";

    $params[] = $deffinumid;
    $params[] = "cmi.interactions_%.id";
    $rs = $DB->get_recordset_sql($sql, $params);
    $keywords = array("cmi.interactions_", ".id");
    if ($rs->valid()) {
        foreach ($rs as $record) {
            $num = trim(str_ireplace($keywords, '', $record->element));
            if (is_numeric($num) && $num > $count) {
                $count = $num;
            }
        }
        // Done as interactions start at 0 (do only if we have something to report).
        $count++;
    }
    $rs->close(); // Closing recordset.
    return $count;
}
