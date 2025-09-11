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
 * Monitor tracks in real time.
 *
 * @package    mod_deffinum
 * @copyright  2025 Edunao SAS (contact@edunao.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/deffinum/lib.php');
require_once($CFG->dirroot.'/mod/deffinum/locallib.php');
require_once($CFG->dirroot.'/course/lib.php');

$id = required_param('cm', PARAM_INT);       // Course Module ID, or.
$scoid = required_param('scoid', PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);

// Checks.
if (! $cm = get_coursemodule_from_id('deffinum', $id, 0, true)) {
    throw new \moodle_exception('invalidcoursemodule');
}
if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
    throw new \moodle_exception('coursemisconf');
}
if (! $deffinum = $DB->get_record("deffinum", array("id" => $cm->instance))) {
    throw new \moodle_exception('invalidcoursemodule');
}

require_login($course, false, $cm);

$context = context_course::instance($course->id);
$contextmodule = context_module::instance($cm->id);
require_capability('mod/deffinum:viewreport', $context);

$url = new moodle_url('/mod/deffinum/monitor_logs.php', array('cm' => $cm->id, 'scoid' => $scoid));
$PAGE->set_url($url);
$PAGE->set_context($contextmodule);

$useroptions = get_enrolled_users($contextmodule, '', 0, 'u.id, u.firstname, u.lastname', 'u.firstname');
$useroptions = [
        0 => (object)[
            'id' => 0,
            'firstname' => get_string('chooseuser'),
            'lastname' => '',
        ],
    ] + $useroptions;
$useroptions = array_map(function($user) use ($userid) {
    return (object)[
        'userid' => $user->id,
        'userfullname' => trim($user->firstname . ' ' . $user->lastname),
        'isselected' => (int) $user->id == (int) $userid,
    ];
}, $useroptions);


// Print the page header.
$PAGE->set_title('Monitor logs');
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

// Select user.
$formaction = $url->out();
$hiddeninputs = "<input type='hidden' name='cm' value='{$cm->id}'>";
$hiddeninputs .= "<input type='hidden' name='scoid' value='{$scoid}'>";
$options = '';
foreach ($useroptions as $useroption) {
    $selected = $useroption->isselected ? ' selected' : '';
    $userfullname = htmlspecialchars($useroption->userfullname);
    $options .= "<option value='{$useroption->userid}'{$selected}>{$userfullname}</option>";
}

// Submit form on change.
echo <<<HTML
<form action="{$formaction}" method="get">
    {$hiddeninputs}
    <select name="userid" onchange="this.form.submit()">
        {$options}
    </select>
</form>
<script type="text/javascript">
document.querySelector("select[name='userid']").addEventListener("change", function() {
    this.form.submit();
});
</script>
HTML;

// Ajax call to get user data for this scorm.
$js = '';
if ($userid) {
    $ajaxurl = new moodle_url('/mod/deffinum/ajax/fetch_logs.php', [
        'scoid' => $scoid,
        'cm' => $cm->id,
        'userid' => $userid,
    ]);
    $ajaxurl = $ajaxurl->out(false);
    $js = <<<JS
    <div id="user-data"></div>

    <script type="text/javascript">
        localStorage.removeItem('lastUserData');
        let lastData = {};

        function userDataChanged(newData) {
            return JSON.stringify(newData) !== JSON.stringify(lastData);
        }

        function fetchUserData() {
            fetch('{$ajaxurl}')
                .then(response => response.json())
                .then(data => {
                    if (userDataChanged(data)) {
                        let currentTime = new Date().toLocaleString();
                        let changes = findChanges(lastData, data);
                        if (Object.keys(changes).length > 0) {
                            let changeHtml = '<table class="table table-bordered"><caption>' + currentTime + '</caption><tbody>';
                            for (const [key, value] of Object.entries(changes)) {
                                changeHtml += '<tr><td>' + key + '</td><td class="word-break">' + value + '</td></tr>';
                            }
                            changeHtml += '</tbody></table>';
                            document.getElementById('user-data').innerHTML += changeHtml;
                        }
                        localStorage.setItem('lastUserData', JSON.stringify(data));
                        lastData = data;
                    }
                })
                .catch(error => console.error('Error fetching user data:', error));
        }

        function findChanges(oldData, newData) {
            let changes = {};
            for (const key in newData) {
                if (newData[key] !== oldData[key]) {
                    changes[key] = newData[key];
                }
            }
            return changes;
        }

        setInterval(fetchUserData, 1000);
    </script>
JS;
}
echo $js;

echo $OUTPUT->footer();
