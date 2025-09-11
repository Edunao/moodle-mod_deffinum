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

require_once($CFG->dirroot.'/mod/deffinum/locallib.php');

// Set some vars to use as default values.
$userdata = new stdClass();
$def = new stdClass();
$cmiobj = new stdClass();
$cmiint = new stdClass();

if (!isset($currentorg)) {
    $currentorg = '';
}

if ($scoes = $DB->get_records('deffinum_scoes', array('deffinum' => $deffinum->id), 'sortorder, id')) {
    // Drop keys so that it is a simple array.
    $scoes = array_values($scoes);
    foreach ($scoes as $sco) {
        $def->{($sco->id)} = new stdClass();
        $userdata->{($sco->id)} = new stdClass();
        $def->{($sco->id)} = get_deffinum_default($userdata->{($sco->id)}, $deffinum, $sco->id, $attempt, $mode);

        // Reconstitute objectives.
        $cmiobj->{($sco->id)} = deffinum_reconstitute_array_element($deffinum->version, $userdata->{($sco->id)},
                                                                    'cmi.objectives', array('score'));
        $cmiint->{($sco->id)} = deffinum_reconstitute_array_element($deffinum->version, $userdata->{($sco->id)},
                                                                    'cmi.interactions', array('objectives', 'correct_responses'));
    }
}

// If DEFFINUM 1.2 standard mode is disabled allow higher datamodel limits.
if (intval(get_config("deffinum", "deffinumstandard"))) {
    $cmistring256 = '^[\\u0000-\\uFFFF]{0,255}$';
    $cmistring4096 = '^[\\u0000-\\uFFFF]{0,4096}$';
} else {
    $cmistring256 = '^[\\u0000-\\uFFFF]{0,64000}$';
    $cmistring4096 = $cmistring256;
}

$deffinum->autocommit = ($deffinum->autocommit === "1") ? true : false;
$deffinum->masteryoverride = ($deffinum->masteryoverride === "1") ? true : false;
$PAGE->requires->js_init_call('M.deffinum_api.init', array($def, $cmiobj, $cmiint, $cmistring256, $cmistring4096,
                                                        deffinum_debugging($deffinum), $deffinum->auto, $deffinum->id, $CFG->wwwroot,
                                                        sesskey(), $scoid, $attempt, $mode, $id, $currentorg, $deffinum->autocommit,
                                                        $deffinum->masteryoverride, $deffinum->hidetoc));

// Pull in the debugging utilities.
if (deffinum_debugging($deffinum)) {
    require_once($CFG->dirroot.'/mod/deffinum/datamodels/debug.js.php');
    echo html_writer::script('AppendToLog("Moodle DEFFINUM 1.2 API Loaded, Activity: '.
                                $deffinum->name.', SCO: '.$sco->identifier.'", 0);');
}
