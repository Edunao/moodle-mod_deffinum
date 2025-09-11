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
 * DEFFINUM module library functions tests
 *
 * @package    mod_deffinum
 * @category   test
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
namespace mod_deffinum;

use mod_deffinum_get_completion_active_rule_descriptions;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/deffinum/lib.php');

/**
 * DEFFINUM module library functions tests
 *
 * @package    mod_deffinum
 * @category   test
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
final class lib_test extends \advanced_testcase {

    /** @var \stdClass course record. */
    protected \stdClass $course;

    /** @var \stdClass activity record. */
    protected \stdClass $deffinum;

    /** @var \core\context\module context instance. */
    protected \core\context\module $context;

    /** @var \stdClass */
    protected \stdClass $cm;

    /** @var \stdClass user record. */
    protected \stdClass $student;

    /** @var \stdClass user record. */
    protected \stdClass $teacher;

    /** @var \stdClass a fieldset object, false or exception if error not found. */
    protected \stdClass $studentrole;

    /** @var \stdClass a fieldset object, false or exception if error not found. */
    protected \stdClass $teacherrole;

    /**
     * Set up for every test
     */
    public function setUp(): void {
        global $DB;
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();

        // Setup test data.
        $this->course = $this->getDataGenerator()->create_course();
        $this->deffinum = $this->getDataGenerator()->create_module('deffinum', array('course' => $this->course->id));
        $this->context = \context_module::instance($this->deffinum->cmid);
        $this->cm = get_coursemodule_from_instance('deffinum', $this->deffinum->id);

        // Create users.
        $this->student = self::getDataGenerator()->create_user();
        $this->teacher = self::getDataGenerator()->create_user();

        // Users enrolments.
        $this->studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->getDataGenerator()->enrol_user($this->student->id, $this->course->id, $this->studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course->id, $this->teacherrole->id, 'manual');
    }

    /** Test deffinum_check_mode
     *
     * @return void
     */
    public function test_deffinum_check_mode(): void {
        global $CFG;

        $newattempt = 'on';
        $attempt = 1;
        $mode = 'normal';
        deffinum_check_mode($this->deffinum, $newattempt, $attempt, $this->student->id, $mode);
        $this->assertEquals('off', $newattempt);

        $scoes = deffinum_get_scoes($this->deffinum->id);
        $sco = array_pop($scoes);
        deffinum_insert_track($this->student->id, $this->deffinum->id, $sco->id, 1, 'cmi.core.lesson_status', 'completed');
        $newattempt = 'on';
        deffinum_check_mode($this->deffinum, $newattempt, $attempt, $this->student->id, $mode);
        $this->assertEquals('on', $newattempt);

        // Now do the same with a DEFFINUM 2004 package.
        $record = new \stdClass();
        $record->course = $this->course->id;
        $record->packagefilepath = $CFG->dirroot.'/mod/deffinum/tests/packages/RuntimeBasicCalls_DEFFINUM20043rdEdition.zip';
        $deffinum13 = $this->getDataGenerator()->create_module('deffinum', $record);
        $newattempt = 'on';
        $attempt = 1;
        $mode = 'normal';
        deffinum_check_mode($deffinum13, $newattempt, $attempt, $this->student->id, $mode);
        $this->assertEquals('off', $newattempt);

        $scoes = deffinum_get_scoes($deffinum13->id);
        $sco = array_pop($scoes);
        deffinum_insert_track($this->student->id, $deffinum13->id, $sco->id, 1, 'cmi.completion_status', 'completed');

        $newattempt = 'on';
        $attempt = 1;
        $mode = 'normal';
        deffinum_check_mode($deffinum13, $newattempt, $attempt, $this->student->id, $mode);
        $this->assertEquals('on', $newattempt);
    }

    /**
     * Test deffinum_view
     * @return void
     */
    public function test_deffinum_view(): void {
        global $CFG;

        // Trigger and capture the event.
        $sink = $this->redirectEvents();

        deffinum_view($this->deffinum, $this->course, $this->cm, $this->context);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_deffinum\event\course_module_viewed', $event);
        $this->assertEquals($this->context, $event->get_context());
        $url = new \moodle_url('/mod/deffinum/view.php', array('id' => $this->cm->id));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());
    }

    /**
     * Test deffinum_get_availability_status and deffinum_require_available
     * @return void
     */
    public function test_deffinum_check_and_require_available(): void {
        global $DB;

        $this->setAdminUser();

        // User override case.
        $this->deffinum->timeopen = time() + DAYSECS;
        $this->deffinum->timeclose = time() - DAYSECS;
        list($status, $warnings) = deffinum_get_availability_status($this->deffinum, true, $this->context);
        $this->assertEquals(true, $status);
        $this->assertCount(0, $warnings);

        // Now check with a student.
        list($status, $warnings) = deffinum_get_availability_status($this->deffinum, true, $this->context, $this->student->id);
        $this->assertEquals(false, $status);
        $this->assertCount(2, $warnings);
        $this->assertArrayHasKey('notopenyet', $warnings);
        $this->assertArrayHasKey('expired', $warnings);
        $this->assertEquals(userdate($this->deffinum->timeopen), $warnings['notopenyet']);
        $this->assertEquals(userdate($this->deffinum->timeclose), $warnings['expired']);

        // Reset the deffinum's times.
        $this->deffinum->timeopen = $this->deffinum->timeclose = 0;

        // Set to the student user.
        self::setUser($this->student);

        // Usual case.
        list($status, $warnings) = deffinum_get_availability_status($this->deffinum, false);
        $this->assertEquals(true, $status);
        $this->assertCount(0, $warnings);

        // DEFFINUM not open.
        $this->deffinum->timeopen = time() + DAYSECS;
        list($status, $warnings) = deffinum_get_availability_status($this->deffinum, false);
        $this->assertEquals(false, $status);
        $this->assertCount(1, $warnings);

        // DEFFINUM closed.
        $this->deffinum->timeopen = 0;
        $this->deffinum->timeclose = time() - DAYSECS;
        list($status, $warnings) = deffinum_get_availability_status($this->deffinum, false);
        $this->assertEquals(false, $status);
        $this->assertCount(1, $warnings);

        // DEFFINUM not open and closed.
        $this->deffinum->timeopen = time() + DAYSECS;
        list($status, $warnings) = deffinum_get_availability_status($this->deffinum, false);
        $this->assertEquals(false, $status);
        $this->assertCount(2, $warnings);

        // Now additional checkings with different parameters values.
        list($status, $warnings) = deffinum_get_availability_status($this->deffinum, true, $this->context);
        $this->assertEquals(false, $status);
        $this->assertCount(2, $warnings);

        // DEFFINUM not open.
        $this->deffinum->timeopen = time() + DAYSECS;
        $this->deffinum->timeclose = 0;
        list($status, $warnings) = deffinum_get_availability_status($this->deffinum, true, $this->context);
        $this->assertEquals(false, $status);
        $this->assertCount(1, $warnings);

        // DEFFINUM closed.
        $this->deffinum->timeopen = 0;
        $this->deffinum->timeclose = time() - DAYSECS;
        list($status, $warnings) = deffinum_get_availability_status($this->deffinum, true, $this->context);
        $this->assertEquals(false, $status);
        $this->assertCount(1, $warnings);

        // DEFFINUM not open and closed.
        $this->deffinum->timeopen = time() + DAYSECS;
        list($status, $warnings) = deffinum_get_availability_status($this->deffinum, true, $this->context);
        $this->assertEquals(false, $status);
        $this->assertCount(2, $warnings);

        // As teacher now.
        self::setUser($this->teacher);

        // DEFFINUM not open and closed.
        $this->deffinum->timeopen = time() + DAYSECS;
        list($status, $warnings) = deffinum_get_availability_status($this->deffinum, false);
        $this->assertEquals(false, $status);
        $this->assertCount(2, $warnings);

        // Now, we use the special capability.
        // DEFFINUM not open and closed.
        $this->deffinum->timeopen = time() + DAYSECS;
        list($status, $warnings) = deffinum_get_availability_status($this->deffinum, true, $this->context);
        $this->assertEquals(true, $status);
        $this->assertCount(0, $warnings);

        // Check exceptions does not broke anything.
        deffinum_require_available($this->deffinum, true, $this->context);
        // Now, expect exceptions.
        $this->expectException('moodle_exception');
        $this->expectExceptionMessage(get_string("notopenyet", "deffinum", userdate($this->deffinum->timeopen)));

        // Now as student other condition.
        self::setUser($this->student);
        $this->deffinum->timeopen = 0;
        $this->deffinum->timeclose = time() - DAYSECS;

        $this->expectException('moodle_exception');
        $this->expectExceptionMessage(get_string("expired", "deffinum", userdate($this->deffinum->timeclose)));
        deffinum_require_available($this->deffinum, false);
    }

    /**
     * Test deffinum_get_last_completed_attempt
     *
     * @return void
     */
    public function test_deffinum_get_last_completed_attempt(): void {
        $this->assertEquals(1, deffinum_get_last_completed_attempt($this->deffinum->id, $this->student->id));
    }

    public function test_deffinum_core_calendar_provide_event_action_open(): void {
        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a deffinum activity.
        $deffinum = $this->getDataGenerator()->create_module('deffinum', array('course' => $course->id,
            'timeopen' => time() - DAYSECS, 'timeclose' => time() + DAYSECS));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $deffinum->id, DEFFINUM_EVENT_TYPE_OPEN);

        // Only students see deffinum events.
        $this->setUser($this->student);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event.
        $actionevent = mod_deffinum_core_calendar_provide_event_action($event, $factory);

        // Confirm the event was decorated.
        $this->assertInstanceOf('\core_calendar\local\event\value_objects\action', $actionevent);
        $this->assertEquals(get_string('enter', 'deffinum'), $actionevent->get_name());
        $this->assertInstanceOf('moodle_url', $actionevent->get_url());
        $this->assertEquals(1, $actionevent->get_item_count());
        $this->assertTrue($actionevent->is_actionable());
    }

    public function test_deffinum_core_calendar_provide_event_action_closed(): void {
        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a deffinum activity.
        $deffinum = $this->getDataGenerator()->create_module('deffinum', array('course' => $course->id,
            'timeclose' => time() - DAYSECS));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $deffinum->id, DEFFINUM_EVENT_TYPE_OPEN);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event.
        $actionevent = mod_deffinum_core_calendar_provide_event_action($event, $factory);

        // No event on the dashboard if module is closed.
        $this->assertNull($actionevent);
    }

    public function test_deffinum_core_calendar_provide_event_action_open_in_future(): void {
        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a deffinum activity.
        $deffinum = $this->getDataGenerator()->create_module('deffinum', array('course' => $course->id,
            'timeopen' => time() + DAYSECS));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $deffinum->id, DEFFINUM_EVENT_TYPE_OPEN);

        // Only students see deffinum events.
        $this->setUser($this->student);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event.
        $actionevent = mod_deffinum_core_calendar_provide_event_action($event, $factory);

        // Confirm the event was decorated.
        $this->assertInstanceOf('\core_calendar\local\event\value_objects\action', $actionevent);
        $this->assertEquals(get_string('enter', 'deffinum'), $actionevent->get_name());
        $this->assertInstanceOf('moodle_url', $actionevent->get_url());
        $this->assertEquals(1, $actionevent->get_item_count());
        $this->assertFalse($actionevent->is_actionable());
    }

    public function test_deffinum_core_calendar_provide_event_action_with_different_user_as_admin(): void {
        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a deffinum activity.
        $deffinum = $this->getDataGenerator()->create_module('deffinum', array('course' => $course->id,
            'timeopen' => time() + DAYSECS));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $deffinum->id, DEFFINUM_EVENT_TYPE_OPEN);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event override with a passed in user.
        $actionevent = mod_deffinum_core_calendar_provide_event_action($event, $factory, $this->student->id);
        $actionevent2 = mod_deffinum_core_calendar_provide_event_action($event, $factory);

        // Only students see deffinum events.
        $this->assertNull($actionevent2);

        // Confirm the event was decorated.
        $this->assertInstanceOf('\core_calendar\local\event\value_objects\action', $actionevent);
        $this->assertEquals(get_string('enter', 'deffinum'), $actionevent->get_name());
        $this->assertInstanceOf('moodle_url', $actionevent->get_url());
        $this->assertEquals(1, $actionevent->get_item_count());
        $this->assertFalse($actionevent->is_actionable());
    }

    public function test_deffinum_core_calendar_provide_event_action_no_time_specified(): void {
        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a deffinum activity.
        $deffinum = $this->getDataGenerator()->create_module('deffinum', array('course' => $course->id));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $deffinum->id, DEFFINUM_EVENT_TYPE_OPEN);

        // Only students see deffinum events.
        $this->setUser($this->student);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event.
        $actionevent = mod_deffinum_core_calendar_provide_event_action($event, $factory);

        // Confirm the event was decorated.
        $this->assertInstanceOf('\core_calendar\local\event\value_objects\action', $actionevent);
        $this->assertEquals(get_string('enter', 'deffinum'), $actionevent->get_name());
        $this->assertInstanceOf('moodle_url', $actionevent->get_url());
        $this->assertEquals(1, $actionevent->get_item_count());
        $this->assertTrue($actionevent->is_actionable());
    }

    public function test_deffinum_core_calendar_provide_event_action_already_completed(): void {
        $this->resetAfterTest();
        set_config('enablecompletion', 1);
        $this->setAdminUser();

        // Create the activity.
        $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $deffinum = $this->getDataGenerator()->create_module('deffinum', array('course' => $course->id),
            array('completion' => 2, 'completionview' => 1, 'completionexpected' => time() + DAYSECS));

        // Get some additional data.
        $cm = get_coursemodule_from_instance('deffinum', $deffinum->id);

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $deffinum->id,
            \core_completion\api::COMPLETION_EVENT_TYPE_DATE_COMPLETION_EXPECTED);

        // Mark the activity as completed.
        $completion = new \completion_info($course);
        $completion->set_module_viewed($cm);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event.
        $actionevent = mod_deffinum_core_calendar_provide_event_action($event, $factory);

        // Ensure result was null.
        $this->assertNull($actionevent);
    }

    public function test_deffinum_core_calendar_provide_event_action_already_completed_for_user(): void {
        $this->resetAfterTest();
        set_config('enablecompletion', 1);
        $this->setAdminUser();

        // Create the activity.
        $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $deffinum = $this->getDataGenerator()->create_module('deffinum', array('course' => $course->id),
            array('completion' => 2, 'completionview' => 1, 'completionexpected' => time() + DAYSECS));

        // Enrol a student in the course.
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        // Get some additional data.
        $cm = get_coursemodule_from_instance('deffinum', $deffinum->id);

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $deffinum->id,
            \core_completion\api::COMPLETION_EVENT_TYPE_DATE_COMPLETION_EXPECTED);

        // Mark the activity as completed for the student.
        $completion = new \completion_info($course);
        $completion->set_module_viewed($cm, $student->id);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event for the student.
        $actionevent = mod_deffinum_core_calendar_provide_event_action($event, $factory, $student->id);

        // Ensure result was null.
        $this->assertNull($actionevent);
    }

    /**
     * Creates an action event.
     *
     * @param int $courseid
     * @param int $instanceid The data id.
     * @param string $eventtype The event type. eg. DATA_EVENT_TYPE_OPEN.
     * @param int|null $timestart The start timestamp for the event
     * @return bool|calendar_event
     */
    private function create_action_event($courseid, $instanceid, $eventtype, $timestart = null) {
        $event = new \stdClass();
        $event->name = 'Calendar event';
        $event->modulename = 'deffinum';
        $event->courseid = $courseid;
        $event->instance = $instanceid;
        $event->type = CALENDAR_EVENT_TYPE_ACTION;
        $event->eventtype = $eventtype;
        $event->eventtype = $eventtype;

        if ($timestart) {
            $event->timestart = $timestart;
        } else {
            $event->timestart = time();
        }

        return \calendar_event::create($event);
    }

    /**
     * Test the callback responsible for returning the completion rule descriptions.
     * This function should work given either an instance of the module (cm_info), such as when checking the active rules,
     * or if passed a stdClass of similar structure, such as when checking the the default completion settings for a mod type.
     */
    public function test_mod_deffinum_completion_get_active_rule_descriptions(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Two activities, both with automatic completion. One has the 'completionsubmit' rule, one doesn't.
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 2]);
        $deffinum1 = $this->getDataGenerator()->create_module('deffinum', [
            'course' => $course->id,
            'completion' => 2,
            'completionstatusrequired' => 6,
            'completionscorerequired' => 5,
            'completionstatusallscos' => 1
        ]);
        $deffinum2 = $this->getDataGenerator()->create_module('deffinum', [
            'course' => $course->id,
            'completion' => 2,
            'completionstatusrequired' => null,
            'completionscorerequired' => null,
            'completionstatusallscos' => null
        ]);
        $cm1 = \cm_info::create(get_coursemodule_from_instance('deffinum', $deffinum1->id));
        $cm2 = \cm_info::create(get_coursemodule_from_instance('deffinum', $deffinum2->id));

        // Data for the stdClass input type.
        // This type of input would occur when checking the default completion rules for an activity type, where we don't have
        // any access to cm_info, rather the input is a stdClass containing completion and customdata attributes, just like cm_info.
        $moddefaults = new \stdClass();
        $moddefaults->customdata = ['customcompletionrules' => [
            'completionstatusrequired' => 6,
            'completionscorerequired' => 5,
            'completionstatusallscos' => 1
        ]];
        $moddefaults->completion = 2;

        // Determine the selected statuses using a bitwise operation.
        $cvalues = array();
        foreach (deffinum_status_options(true) as $key => $value) {
            if (($deffinum1->completionstatusrequired & $key) == $key) {
                $cvalues[] = $value;
            }
        }
        $statusstring = implode(', ', $cvalues);

        $activeruledescriptions = [
            get_string('completionstatusrequireddesc', 'deffinum', $statusstring),
            get_string('completionscorerequireddesc', 'deffinum', $deffinum1->completionscorerequired),
            get_string('completionstatusallscos', 'deffinum'),
        ];
        $this->assertEquals(mod_deffinum_get_completion_active_rule_descriptions($cm1), $activeruledescriptions);
        $this->assertEquals(mod_deffinum_get_completion_active_rule_descriptions($cm2), []);
        $this->assertEquals(mod_deffinum_get_completion_active_rule_descriptions($moddefaults), $activeruledescriptions);
        $this->assertEquals(mod_deffinum_get_completion_active_rule_descriptions(new \stdClass()), []);
    }

    /**
     * An unkown event type should not change the deffinum instance.
     */
    public function test_mod_deffinum_core_calendar_event_timestart_updated_unknown_event(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $deffinumgenerator = $generator->get_plugin_generator('mod_deffinum');
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $deffinum = $deffinumgenerator->create_instance(['course' => $course->id]);
        $deffinum->timeopen = $timeopen;
        $deffinum->timeclose = $timeclose;
        $DB->update_record('deffinum', $deffinum);

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'deffinum',
            'instance' => $deffinum->id,
            'eventtype' => DEFFINUM_EVENT_TYPE_OPEN . "SOMETHING ELSE",
            'timestart' => 1,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        mod_deffinum_core_calendar_event_timestart_updated($event, $deffinum);

        $deffinum = $DB->get_record('deffinum', ['id' => $deffinum->id]);
        $this->assertEquals($timeopen, $deffinum->timeopen);
        $this->assertEquals($timeclose, $deffinum->timeclose);
    }

    /**
     * A DEFFINUM_EVENT_TYPE_OPEN event should update the timeopen property of
     * the deffinum activity.
     */
    public function test_mod_deffinum_core_calendar_event_timestart_updated_open_event(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $deffinumgenerator = $generator->get_plugin_generator('mod_deffinum');
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $timemodified = 1;
        $newtimeopen = $timeopen - DAYSECS;
        $deffinum = $deffinumgenerator->create_instance(['course' => $course->id]);
        $deffinum->timeopen = $timeopen;
        $deffinum->timeclose = $timeclose;
        $deffinum->timemodified = $timemodified;
        $DB->update_record('deffinum', $deffinum);

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'deffinum',
            'instance' => $deffinum->id,
            'eventtype' => DEFFINUM_EVENT_TYPE_OPEN,
            'timestart' => $newtimeopen,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        // Trigger and capture the event when adding a contact.
        $sink = $this->redirectEvents();

        mod_deffinum_core_calendar_event_timestart_updated($event, $deffinum);

        $triggeredevents = $sink->get_events();
        $moduleupdatedevents = array_filter($triggeredevents, function($e) {
            return is_a($e, 'core\event\course_module_updated');
        });

        $deffinum = $DB->get_record('deffinum', ['id' => $deffinum->id]);
        // Ensure the timeopen property matches the event timestart.
        $this->assertEquals($newtimeopen, $deffinum->timeopen);
        // Ensure the timeclose isn't changed.
        $this->assertEquals($timeclose, $deffinum->timeclose);
        // Ensure the timemodified property has been changed.
        $this->assertNotEquals($timemodified, $deffinum->timemodified);
        // Confirm that a module updated event is fired when the module
        // is changed.
        $this->assertNotEmpty($moduleupdatedevents);
    }

    /**
     * A DEFFINUM_EVENT_TYPE_CLOSE event should update the timeclose property of
     * the deffinum activity.
     */
    public function test_mod_deffinum_core_calendar_event_timestart_updated_close_event(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $deffinumgenerator = $generator->get_plugin_generator('mod_deffinum');
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $timemodified = 1;
        $newtimeclose = $timeclose + DAYSECS;
        $deffinum = $deffinumgenerator->create_instance(['course' => $course->id]);
        $deffinum->timeopen = $timeopen;
        $deffinum->timeclose = $timeclose;
        $deffinum->timemodified = $timemodified;
        $DB->update_record('deffinum', $deffinum);

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'deffinum',
            'instance' => $deffinum->id,
            'eventtype' => DEFFINUM_EVENT_TYPE_CLOSE,
            'timestart' => $newtimeclose,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        // Trigger and capture the event when adding a contact.
        $sink = $this->redirectEvents();

        mod_deffinum_core_calendar_event_timestart_updated($event, $deffinum);

        $triggeredevents = $sink->get_events();
        $moduleupdatedevents = array_filter($triggeredevents, function($e) {
            return is_a($e, 'core\event\course_module_updated');
        });

        $deffinum = $DB->get_record('deffinum', ['id' => $deffinum->id]);
        // Ensure the timeclose property matches the event timestart.
        $this->assertEquals($newtimeclose, $deffinum->timeclose);
        // Ensure the timeopen isn't changed.
        $this->assertEquals($timeopen, $deffinum->timeopen);
        // Ensure the timemodified property has been changed.
        $this->assertNotEquals($timemodified, $deffinum->timemodified);
        // Confirm that a module updated event is fired when the module
        // is changed.
        $this->assertNotEmpty($moduleupdatedevents);
    }

    /**
     * An unkown event type should not have any limits
     */
    public function test_mod_deffinum_core_calendar_get_valid_event_timestart_range_unknown_event(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $deffinum = new \stdClass();
        $deffinum->timeopen = $timeopen;
        $deffinum->timeclose = $timeclose;

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'deffinum',
            'instance' => 1,
            'eventtype' => DEFFINUM_EVENT_TYPE_OPEN . "SOMETHING ELSE",
            'timestart' => 1,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        list ($min, $max) = mod_deffinum_core_calendar_get_valid_event_timestart_range($event, $deffinum);
        $this->assertNull($min);
        $this->assertNull($max);
    }

    /**
     * The open event should be limited by the deffinum's timeclose property, if it's set.
     */
    public function test_mod_deffinum_core_calendar_get_valid_event_timestart_range_open_event(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $deffinum = new \stdClass();
        $deffinum->timeopen = $timeopen;
        $deffinum->timeclose = $timeclose;

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'deffinum',
            'instance' => 1,
            'eventtype' => DEFFINUM_EVENT_TYPE_OPEN,
            'timestart' => 1,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        // The max limit should be bounded by the timeclose value.
        list ($min, $max) = mod_deffinum_core_calendar_get_valid_event_timestart_range($event, $deffinum);

        $this->assertNull($min);
        $this->assertEquals($timeclose, $max[0]);

        // No timeclose value should result in no upper limit.
        $deffinum->timeclose = 0;
        list ($min, $max) = mod_deffinum_core_calendar_get_valid_event_timestart_range($event, $deffinum);

        $this->assertNull($min);
        $this->assertNull($max);
    }

    /**
     * The close event should be limited by the deffinum's timeopen property, if it's set.
     */
    public function test_mod_deffinum_core_calendar_get_valid_event_timestart_range_close_event(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $deffinum = new \stdClass();
        $deffinum->timeopen = $timeopen;
        $deffinum->timeclose = $timeclose;

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'deffinum',
            'instance' => 1,
            'eventtype' => DEFFINUM_EVENT_TYPE_CLOSE,
            'timestart' => 1,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        // The max limit should be bounded by the timeclose value.
        list ($min, $max) = mod_deffinum_core_calendar_get_valid_event_timestart_range($event, $deffinum);

        $this->assertEquals($timeopen, $min[0]);
        $this->assertNull($max);

        // No timeclose value should result in no upper limit.
        $deffinum->timeopen = 0;
        list ($min, $max) = mod_deffinum_core_calendar_get_valid_event_timestart_range($event, $deffinum);

        $this->assertNull($min);
        $this->assertNull($max);
    }

    /**
     * A user who does not have capabilities to add events to the calendar should be able to create a DEFFINUM.
     */
    public function test_creation_with_no_calendar_capabilities(): void {
        $this->resetAfterTest();
        $course = self::getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $user = self::getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $roleid = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($roleid, $user->id, $context->id);
        assign_capability('moodle/calendar:manageentries', CAP_PROHIBIT, $roleid, $context, true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_deffinum');
        // Create an instance as a user without the calendar capabilities.
        $this->setUser($user);
        $time = time();
        $params = array(
            'course' => $course->id,
            'timeopen' => $time + 200,
            'timeclose' => $time + 2000,
        );
        $generator->create_instance($params);
    }
}
