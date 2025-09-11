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
 * Resource delivery entry point for the Deffinum module.
 *
 * @package    mod_deffinum
 * @copyright  2025 Edunao SAS (contact@edunao.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/deffinum/lib.php');

global $DB;

// Retrieve the course module ID from the request.
$id = required_param('id', PARAM_INT);       // Course Module ID

// Validate and load the course module instance for the Deffinum activity.
if (! $cm = get_coursemodule_from_id('deffinum', $id, 0, true)) {
    throw new \moodle_exception('invalidcoursemodule');
}

// Retrieve the associated course record.
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

// When scanning the QR code, the user is connected via the mobile app.
// After redirection to this page, authentication cannot be enforced outside the app.
//require_course_login($course, true, $cm);

// Build the page URL for the resource.
$pageurl = new moodle_url('/mod/deffinum/resource.php', array('id' => $cm->id));
$PAGE->set_url($pageurl);

// Get the context of the module, which is necessary for permission checks and file retrieval.
$context = context_module::instance($cm->id);

// Retrieve all files associated with this module's 'resource' area, excluding directories.
// Files are ordered by 'sortorder' descending and 'id' ascending.
$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'mod_deffinum', 'resource', 0, 'sortorder DESC, id ASC', false);

// Check if there are any files. If not, display a notification for file not found and terminate execution.
if (count($files) < 1) {
    echo $OUTPUT->notification(get_string('filenotfound', 'deffinum'));
    die;
} else {
    // Reset the array to get the first file element.
    $file = reset($files);

    // Clear the files array to free up memory.
    unset($files);

    // Send the file to the user. Force download prevents the file from being displayed in the browser.
    // Zero values for caching parameters to ensure the latest file version is always served.
    send_stored_file($file, 0, 0, true);
}

