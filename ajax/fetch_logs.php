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
 * AJAX endpoint to retrieve user attempt data for a Deffinum SCO.
 *
 * @package    mod_deffinum
 * @copyright  2025 Edunao SAS (contact@edunao.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_login();

// Retrieve required parameters from the request.
$id     = required_param('cm', PARAM_INT);       // Course Module ID, or.
$scoid  = required_param('scoid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

// Validate course module.
if (! $cm = get_coursemodule_from_id('deffinum', $id, 0, true)) {
    throw new \moodle_exception('invalidcoursemodule');
}

// Retrieve course.
if (! $course = $DB->get_record("course", ['id' => $cm->course])) {
    throw new \moodle_exception('coursemisconf');
}

// Retrieve module instance.
if (! $deffinum = $DB->get_record("deffinum", ['id' => $cm->instance])) {
    throw new \moodle_exception('invalidcoursemodule');
}

// Check capability to view reports.
if (!has_capability('mod/deffinum:viewreport', context_module::instance($id))) {
    header('HTTP/1.1 403 Forbidden');
    die();
}

// Retrieve attempt data.
// Fetch the attempt number (a.attempt), the element value (ev.value),
// and the element name (e.element) for the given user, module, and SCO.
$sql = "SELECT a.attempt, ev.value, e.element
        FROM {deffinum_attempt} a
        JOIN {deffinum_scoes_value} ev ON ev.attemptid = a.id
        JOIN {deffinum_element} e ON e.id = ev.elementid
        WHERE a.userid = ? AND a.deffinumid = ? AND ev.scoid = ?
        ORDER BY ev.id ASC";
$params = [$userid, $deffinum->id, $scoid];
$records = $DB->get_records_sql($sql, $params);

// Initialize output array.
$data = [];
$oldattempt = 0;
foreach ($records as $obj) {
    // Store the attempt number when a change in 'attempt' is detected.
    if ($obj->attempt != $oldattempt) {
        $data['attempt'] = $obj->attempt;
        $oldattempt = $obj->attempt;
    }
    // Add each element with its corresponding value.
    $data[$obj->element] = $obj->value;
}

// Convert the result to JSON and output it.
header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);
