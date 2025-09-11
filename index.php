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

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/deffinum/locallib.php');

$id = required_param('id', PARAM_INT);   // Course id.

$PAGE->set_url('/mod/deffinum/index.php', array('id' => $id));

if (!empty($id)) {
    if (!$course = $DB->get_record('course', array('id' => $id))) {
        throw new \moodle_exception('invalidcourseid');
    }
} else {
    throw new \moodle_exception('missingparameter');
}

require_course_login($course);
$PAGE->set_pagelayout('incourse');

// Trigger instances list viewed event.
$event = \mod_deffinum\event\course_module_instance_list_viewed::create(array('context' => context_course::instance($course->id)));
$event->add_record_snapshot('course', $course);
$event->trigger();

$strdeffinum = get_string("modulename", "deffinum");
$strdeffinums = get_string("modulenameplural", "deffinum");
$strname = get_string("name");
$strsummary = get_string("summary");
$strreport = get_string("report", 'deffinum');
$strlastmodified = get_string("lastmodified");

$PAGE->set_title($strdeffinums);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strdeffinums);
echo $OUTPUT->header();

$usesections = course_format_uses_sections($course->format);

if ($usesections) {
    $sortorder = "cw.section ASC";
} else {
    $sortorder = "m.timemodified DESC";
}

if (! $deffinums = get_all_instances_in_course("deffinum", $course)) {
    notice(get_string('thereareno', 'moodle', $strdeffinums), "../../course/view.php?id=$course->id");
    exit;
}

$table = new html_table();

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_'.$course->format);
    $table->head  = array ($strsectionname, $strname, $strsummary, $strreport);
    $table->align = array ("center", "left", "left", "left");
} else {
    $table->head  = array ($strlastmodified, $strname, $strsummary, $strreport);
    $table->align = array ("left", "left", "left", "left");
}

foreach ($deffinums as $deffinum) {
    $context = context_module::instance($deffinum->coursemodule);
    $tt = "";
    if ($usesections) {
        if ($deffinum->section) {
            $tt = get_section_name($course, $deffinum->section);
        }
    } else {
        $tt = userdate($deffinum->timemodified);
    }
    $report = '&nbsp;';
    $reportshow = '&nbsp;';
    if (has_capability('mod/deffinum:viewreport', $context)) {
        $trackedusers = deffinum_get_count_users($deffinum->id, $deffinum->groupingid);
        if ($trackedusers > 0) {
            $reportshow = html_writer::link('report.php?id='.$deffinum->coursemodule,
                                                get_string('viewallreports', 'deffinum', $trackedusers));
        } else {
            $reportshow = get_string('noreports', 'deffinum');
        }
    } else if (has_capability('mod/deffinum:viewscores', $context)) {
        require_once('locallib.php');
        $report = deffinum_grade_user($deffinum, $USER->id);
        $reportshow = get_string('score', 'deffinum').": ".$report;
    }
    $options = (object)array('noclean' => true);
    if (!$deffinum->visible) {
        // Show dimmed if the mod is hidden.
        $table->data[] = array ($tt, html_writer::link('view.php?id='.$deffinum->coursemodule,
                                                        format_string($deffinum->name),
                                                        array('class' => 'dimmed')),
                                format_module_intro('deffinum', $deffinum, $deffinum->coursemodule), $reportshow);
    } else {
        // Show normal if the mod is visible.
        $table->data[] = array ($tt, html_writer::link('view.php?id='.$deffinum->coursemodule, format_string($deffinum->name)),
                                format_module_intro('deffinum', $deffinum, $deffinum->coursemodule), $reportshow);
    }
}

echo html_writer::empty_tag('br');

echo html_writer::table($table);

echo $OUTPUT->footer();