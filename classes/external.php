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
 * DEFFINUM module external API
 *
 * @package    mod_deffinum
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

use core_course\external\helper_for_get_mods_by_courses;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;
use core_external\util;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/deffinum/lib.php');
require_once($CFG->dirroot . '/mod/deffinum/locallib.php');

/**
 * DEFFINUM module external functions
 *
 * @package    mod_deffinum
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_deffinum_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function view_deffinum_parameters() {
        return new external_function_parameters(
            array(
                'deffinumid' => new external_value(PARAM_INT, 'deffinum instance id')
            )
        );
    }

    /**
     * Trigger the course module viewed event.
     *
     * @param int $deffinumid the deffinum instance id
     * @return array of warnings and status result
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function view_deffinum($deffinumid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/mod/deffinum/lib.php');

        $params = self::validate_parameters(self::view_deffinum_parameters(),
                                            array(
                                                'deffinumid' => $deffinumid
                                            ));
        $warnings = array();

        // Request and permission validation.
        $deffinum = $DB->get_record('deffinum', array('id' => $params['deffinumid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($deffinum, 'deffinum');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        // Call the deffinum/lib API.
        deffinum_view($deffinum, $course, $cm, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return \core_external\external_description
     * @since Moodle 3.0
     */
    public static function view_deffinum_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Describes the parameters for get_deffinum_attempt_count.
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_deffinum_attempt_count_parameters() {
        return new external_function_parameters(
            array(
                'deffinumid' => new external_value(PARAM_INT, 'DEFFINUM instance id'),
                'userid' => new external_value(PARAM_INT, 'User id'),
                'ignoremissingcompletion' => new external_value(PARAM_BOOL,
                                                'Ignores attempts that haven\'t reported a grade/completion',
                                                VALUE_DEFAULT, false),
            )
        );
    }

    /**
     * Return the number of attempts done by a user in the given DEFFINUM.
     *
     * @param int $deffinumid the deffinum id
     * @param int $userid the user id
     * @param bool $ignoremissingcompletion ignores attempts that haven't reported a grade/completion
     * @return array of warnings and the attempts count
     * @since Moodle 3.0
     */
    public static function get_deffinum_attempt_count($deffinumid, $userid, $ignoremissingcompletion = false) {
        global $USER, $DB;

        $params = self::validate_parameters(self::get_deffinum_attempt_count_parameters(),
                                            array('deffinumid' => $deffinumid, 'userid' => $userid,
                                                'ignoremissingcompletion' => $ignoremissingcompletion));

        $attempts = array();
        $warnings = array();

        $deffinum = $DB->get_record('deffinum', array('id' => $params['deffinumid']), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('deffinum', $deffinum->id);

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        $user = core_user::get_user($params['userid'], '*', MUST_EXIST);
        core_user::require_active_user($user);

        // Extra checks so only users with permissions can view other users attempts.
        if ($USER->id != $user->id) {
            require_capability('mod/deffinum:viewreport', $context);
        }

        // If the DEFFINUM is not open this function will throw exceptions.
        deffinum_require_available($deffinum);

        $attemptscount = deffinum_get_attempt_count($user->id, $deffinum, false, $params['ignoremissingcompletion']);

        $result = array();
        $result['attemptscount'] = $attemptscount;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Describes the get_deffinum_attempt_count return value.
     *
     * @return external_single_structure
     * @since Moodle 3.0
     */
    public static function get_deffinum_attempt_count_returns() {

        return new external_single_structure(
            array(
                'attemptscount' => new external_value(PARAM_INT, 'Attempts count'),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for get_deffinum_scoes.
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_deffinum_scoes_parameters() {
        return new external_function_parameters(
            array(
                'deffinumid' => new external_value(PARAM_INT, 'deffinum instance id'),
                'organization' => new external_value(PARAM_RAW, 'organization id', VALUE_DEFAULT, '')
            )
        );
    }

    /**
     * Returns a list containing all the scoes data related to the given deffinum id
     *
     * @param int $deffinumid the deffinum id
     * @param string $organization the organization id
     * @return array warnings and the scoes data
     * @since Moodle 3.0
     */
    public static function get_deffinum_scoes($deffinumid, $organization = '') {
        global $DB;

        $params = self::validate_parameters(self::get_deffinum_scoes_parameters(),
                                            array('deffinumid' => $deffinumid, 'organization' => $organization));

        $scoes = array();
        $warnings = array();

        $deffinum = $DB->get_record('deffinum', array('id' => $params['deffinumid']), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('deffinum', $deffinum->id);

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        // Check settings / permissions to view the DEFFINUM.
        deffinum_require_available($deffinum, true, $context);

        if (!$scoes = deffinum_get_scoes($deffinum->id, $params['organization'])) {
            // Function deffinum_get_scoes return false, not an empty array.
            $scoes = array();
        } else {
            $scoreturnstructure = self::get_deffinum_scoes_returns();
            foreach ($scoes as $sco) {
                $extradata = array();
                foreach ($sco as $element => $value) {
                    // Check if the element is extra data (not a basic SCO element).
                    if (!isset($scoreturnstructure->keys['scoes']->content->keys[$element])) {
                        $extradata[] = array(
                            'element' => $element,
                            'value' => $value
                        );
                    }
                }
                $sco->extradata = $extradata;
            }
        }

        $result = array();
        $result['scoes'] = $scoes;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Describes the get_deffinum_scoes return value.
     *
     * @return external_single_structure
     * @since Moodle 3.0
     */
    public static function get_deffinum_scoes_returns() {

        return new external_single_structure(
            array(
                'scoes' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'sco id'),
                            'deffinum' => new external_value(PARAM_INT, 'deffinum id'),
                            'manifest' => new external_value(PARAM_NOTAGS, 'manifest id'),
                            'organization' => new external_value(PARAM_NOTAGS, 'organization id'),
                            'parent' => new external_value(PARAM_NOTAGS, 'parent'),
                            'identifier' => new external_value(PARAM_NOTAGS, 'identifier'),
                            'launch' => new external_value(PARAM_NOTAGS, 'launch file'),
                            'deffinumtype' => new external_value(PARAM_ALPHA, 'deffinum type (asset, sco)'),
                            'title' => new external_value(PARAM_NOTAGS, 'sco title'),
                            'sortorder' => new external_value(PARAM_INT, 'sort order'),
                            'extradata' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'element' => new external_value(PARAM_RAW, 'element name'),
                                        'value' => new external_value(PARAM_RAW, 'element value')
                                    )
                                ), 'Additional SCO data', VALUE_OPTIONAL
                            )
                        ), 'DEFFINUM SCO data'
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for get_deffinum_user_data.
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_deffinum_user_data_parameters() {
        return new external_function_parameters(
            array(
                'deffinumid' => new external_value(PARAM_INT, 'deffinum instance id'),
                'attempt' => new external_value(PARAM_INT, 'attempt number')
            )
        );
    }

    /**
     * Retrieves user tracking and SCO data and default DEFFINUM values
     *
     * @param int $deffinumid the deffinum id
     * @param int $attempt the attempt number
     * @return array warnings and the scoes data
     * @throws  moodle_exception
     * @since Moodle 3.0
     */
    public static function get_deffinum_user_data($deffinumid, $attempt) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_deffinum_user_data_parameters(),
                                            array('deffinumid' => $deffinumid, 'attempt' => $attempt));

        $data = array();
        $warnings = array();

        $deffinum = $DB->get_record('deffinum', array('id' => $params['deffinumid']), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('deffinum', $deffinum->id);

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        deffinum_require_available($deffinum, true, $context);

        $deffinum->version = strtolower(clean_param($deffinum->version, PARAM_SAFEDIR));
        if (!file_exists($CFG->dirroot.'/mod/deffinum/datamodels/'.$deffinum->version.'lib.php')) {
            $deffinum->version = 'deffinum_12';
        }
        require_once($CFG->dirroot.'/mod/deffinum/datamodels/'.$deffinum->version.'lib.php');

        if ($scoes = deffinum_get_scoes($deffinum->id)) {
            $def = new stdClass();
            $user = new stdClass();

            foreach ($scoes as $sco) {
                $def->{$sco->id} = new stdClass();
                $user->{$sco->id} = new stdClass();
                // We force mode normal, this can be override by the client at any time.
                $def->{$sco->id} = get_deffinum_default($user->{$sco->id}, $deffinum, $sco->id, $params['attempt'], 'normal');

                $userdata = array();
                $defaultdata = array();

                foreach ((array) $user->{$sco->id} as $key => $val) {
                    $userdata[] = array(
                        'element' => $key,
                        'value' => $val
                    );
                }
                foreach ($def->{$sco->id} as $key => $val) {
                    $defaultdata[] = array(
                        'element' => $key,
                        'value' => $val
                    );
                }

                $data[] = array(
                    'scoid' => $sco->id,
                    'userdata' => $userdata,
                    'defaultdata' => $defaultdata,
                );
            }
        }

        $result = array();
        $result['data'] = $data;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Describes the get_deffinum_user_data return value.
     *
     * @return external_single_structure
     * @since Moodle 3.0
     */
    public static function get_deffinum_user_data_returns() {

        return new external_single_structure(
            array(
                'data' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'scoid' => new external_value(PARAM_INT, 'sco id'),
                            'userdata' => new external_multiple_structure(
                                            new external_single_structure(
                                                array(
                                                    'element' => new external_value(PARAM_RAW, 'element name'),
                                                    'value' => new external_value(PARAM_RAW, 'element value')
                                                )
                                            )
                                          ),
                            'defaultdata' => new external_multiple_structure(
                                                new external_single_structure(
                                                    array(
                                                        'element' => new external_value(PARAM_RAW, 'element name'),
                                                        'value' => new external_value(PARAM_RAW, 'element value')
                                                    )
                                                )
                                             ),
                        ), 'SCO data'
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for insert_deffinum_tracks.
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function insert_deffinum_tracks_parameters() {
        return new external_function_parameters(
            array(
                'scoid' => new external_value(PARAM_INT, 'SCO id'),
                'attempt' => new external_value(PARAM_INT, 'attempt number'),
                'tracks' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'element' => new external_value(PARAM_RAW, 'element name'),
                            'value' => new external_value(PARAM_RAW, 'element value')
                        )
                    )
                ),
            )
        );
    }

    /**
     * Saves a DEFFINUM tracking record.
     * It will overwrite any existing tracking data for this attempt.
     * Validation should be performed before running the function to ensure the user will not lose any existing attempt data.
     *
     * @param int $scoid the SCO id
     * @param string $attempt the attempt number
     * @param array $tracks the track records to be stored
     * @return array warnings and the scoes data
     * @throws moodle_exception
     * @since Moodle 3.0
     */
    public static function insert_deffinum_tracks($scoid, $attempt, $tracks) {
        global $USER, $DB;

        $params = self::validate_parameters(self::insert_deffinum_tracks_parameters(),
                                            array('scoid' => $scoid, 'attempt' => $attempt, 'tracks' => $tracks));

        $trackids = array();
        $warnings = array();

        $sco = deffinum_get_sco($params['scoid'], SCO_ONLY);
        if (!$sco) {
            throw new moodle_exception('cannotfindsco', 'deffinum');
        }

        $deffinum = $DB->get_record('deffinum', array('id' => $sco->deffinum), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('deffinum', $deffinum->id);

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        // Check settings / permissions to view the DEFFINUM.
        require_capability('mod/deffinum:savetrack', $context);

        // Check settings / permissions to view the DEFFINUM.
        deffinum_require_available($deffinum);
        $attemptobject = deffinum_get_attempt($USER->id, $deffinum->id, $params['attempt']);

        foreach ($params['tracks'] as $track) {
            $element = $track['element'];
            $value = $track['value'];
            $trackid = deffinum_insert_track($USER->id, $deffinum->id, $sco->id, $attemptobject, $element, $value,
                                            $deffinum->forcecompleted);

            if ($trackid) {
                $trackids[] = $trackid;
            } else {
                $warnings[] = array(
                    'item' => 'deffinum',
                    'itemid' => $deffinum->id,
                    'warningcode' => 1,
                    'message' => 'Element: ' . $element . ' was not saved'
                );
            }
        }

        $result = array();
        $result['trackids'] = $trackids;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Describes the insert_deffinum_tracks return value.
     *
     * @return external_single_structure
     * @since Moodle 3.0
     */
    public static function insert_deffinum_tracks_returns() {

        return new external_single_structure(
            array(
                'trackids' => new external_multiple_structure(new external_value(PARAM_INT, 'track id')),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for get_deffinum_sco_tracks.
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_deffinum_sco_tracks_parameters() {
        return new external_function_parameters(
            array(
                'scoid' => new external_value(PARAM_INT, 'sco id'),
                'userid' => new external_value(PARAM_INT, 'user id'),
                'attempt' => new external_value(PARAM_INT, 'attempt number (0 for last attempt)', VALUE_DEFAULT, 0)
            )
        );
    }

    /**
     * Retrieves SCO tracking data for the given user id and attempt number
     *
     * @param int $scoid the sco id
     * @param int $userid the user id
     * @param int $attempt the attempt number
     * @return array warnings and the scoes data
     * @since Moodle 3.0
     */
    public static function get_deffinum_sco_tracks($scoid, $userid, $attempt = 0) {
        global $USER, $DB;

        $params = self::validate_parameters(self::get_deffinum_sco_tracks_parameters(),
                                            array('scoid' => $scoid, 'userid' => $userid, 'attempt' => $attempt));

        $tracks = array();
        $warnings = array();

        $sco = deffinum_get_sco($params['scoid'], SCO_ONLY);
        if (!$sco) {
            throw new moodle_exception('cannotfindsco', 'deffinum');
        }

        $deffinum = $DB->get_record('deffinum', array('id' => $sco->deffinum), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('deffinum', $deffinum->id);

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        $user = core_user::get_user($params['userid'], '*', MUST_EXIST);
        core_user::require_active_user($user);

        // Extra checks so only users with permissions can view other users attempts.
        if ($USER->id != $user->id) {
            require_capability('mod/deffinum:viewreport', $context);
        }

        deffinum_require_available($deffinum, true, $context);

        if (empty($params['attempt'])) {
            $params['attempt'] = deffinum_get_last_attempt($deffinum->id, $user->id);
        }

        $attempted = false;
        if ($deffinumtracks = deffinum_get_tracks($sco->id, $params['userid'], $params['attempt'])) {
            // Check if attempted.
            if ($deffinumtracks->status != '') {
                $attempted = true;
                foreach ($deffinumtracks as $element => $value) {
                    $tracks[] = array(
                        'element' => $element,
                        'value' => $value,
                    );
                }
            }
        }

        if (!$attempted) {
            $warnings[] = array(
                'item' => 'attempt',
                'itemid' => $params['attempt'],
                'warningcode' => 'notattempted',
                'message' => get_string('notattempted', 'deffinum')
            );
        }

        $result = array();
        $result['data']['attempt'] = $params['attempt'];
        $result['data']['tracks'] = $tracks;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Describes the get_deffinum_sco_tracks return value.
     *
     * @return external_single_structure
     * @since Moodle 3.0
     */
    public static function get_deffinum_sco_tracks_returns() {

        return new external_single_structure(
            array(
                'data' => new external_single_structure(
                    array(
                        'attempt' => new external_value(PARAM_INT, 'Attempt number'),
                        'tracks' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'element' => new external_value(PARAM_RAW, 'Element name'),
                                    'value' => new external_value(PARAM_RAW, 'Element value')
                                ), 'Tracks data'
                            )
                        ),
                    ), 'SCO data'
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for get_deffinums_by_courses.
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_deffinums_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    /**
     * Returns a list of deffinums in a provided list of courses,
     * if no list is provided all deffinums that the user can view will be returned.
     *
     * @param array $courseids the course ids
     * @return array the deffinum details
     * @since Moodle 3.0
     */
    public static function get_deffinums_by_courses($courseids = array()) {
        global $CFG;

        $returneddeffinums = array();
        $warnings = array();

        $params = self::validate_parameters(self::get_deffinums_by_courses_parameters(), array('courseids' => $courseids));

        $courses = array();
        if (empty($params['courseids'])) {
            $courses = enrol_get_my_courses();
            $params['courseids'] = array_keys($courses);
        }

        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {

            list($courses, $warnings) = util::validate_courses($params['courseids'], $courses);

            // Get the deffinums in this course, this function checks users visibility permissions.
            // We can avoid then additional validate_context calls.
            $deffinums = get_all_instances_in_courses("deffinum", $courses);

            $fs = get_file_storage();
            foreach ($deffinums as $deffinum) {

                $context = context_module::instance($deffinum->coursemodule);

                // Entry to return.
                $module = helper_for_get_mods_by_courses::standard_coursemodule_element_values($deffinum, 'mod_deffinum');

                // Check if the DEFFINUM open and return warnings if so.
                list($open, $openwarnings) = deffinum_get_availability_status($deffinum, true, $context);

                if (!$open) {
                    foreach ($openwarnings as $warningkey => $warningdata) {
                        $warnings[] = array(
                            'item' => 'deffinum',
                            'itemid' => $deffinum->id,
                            'warningcode' => $warningkey,
                            'message' => get_string($warningkey, 'deffinum', $warningdata)
                        );
                    }
                } else {
                    $module['packagesize'] = 0;
                    // DEFFINUM size.
                    if ($deffinum->deffinumtype === DEFFINUM_TYPE_LOCAL or $deffinum->deffinumtype === DEFFINUM_TYPE_LOCALSYNC) {
                        if ($packagefile = $fs->get_file($context->id, 'mod_deffinum', 'package', 0, '/', $deffinum->reference)) {
                            $module['packagesize'] = $packagefile->get_filesize();
                            // Download URL.
                            $module['packageurl'] = moodle_url::make_webservice_pluginfile_url(
                                                    $context->id, 'mod_deffinum', 'package', 0, '/', $deffinum->reference)->out(false);
                        }
                    }

                    $module['protectpackagedownloads'] = get_config('deffinum', 'protectpackagedownloads');

                    $viewablefields = array('version', 'maxgrade', 'grademethod', 'whatgrade', 'maxattempt', 'forcecompleted',
                                            'forcenewattempt', 'lastattemptlock', 'displayattemptstatus', 'displaycoursestructure',
                                            'sha1hash', 'md5hash', 'revision', 'launch', 'skipview', 'hidebrowse', 'hidetoc', 'nav',
                                            'navpositionleft', 'navpositiontop', 'auto', 'popup', 'width', 'height', 'timeopen',
                                            'timeclose', 'deffinumtype', 'reference');

                    // Check additional permissions for returning optional private settings.
                    if (has_capability('moodle/course:manageactivities', $context)) {
                        $additionalfields = array('updatefreq', 'options', 'completionstatusrequired', 'completionscorerequired',
                                                  'completionstatusallscos', 'autocommit', 'timemodified');
                        $viewablefields = array_merge($viewablefields, $additionalfields);
                    }

                    foreach ($viewablefields as $field) {
                        $module[$field] = $deffinum->{$field};
                    }
                }

                $returneddeffinums[] = $module;
            }
        }

        $settings = [
            [
                'name' => 'deffinumstandard',
                'value' => get_config('deffinum', 'deffinumstandard'),
            ]
        ];
        $result = [
            'deffinums'   => $returneddeffinums,
            'options'  => $settings,
            'warnings' => $warnings
        ];
        return $result;
    }

    /**
     * Describes the get_deffinums_by_courses return value.
     *
     * @return external_single_structure
     * @since Moodle 3.0
     */
    public static function get_deffinums_by_courses_returns() {

        return new external_single_structure(
            array(
                'deffinums' => new external_multiple_structure(
                    new external_single_structure(array_merge(
                        helper_for_get_mods_by_courses::standard_coursemodule_elements_returns(),
                        [
                            'packagesize' => new external_value(PARAM_INT, 'DEFFINUM zip package size', VALUE_OPTIONAL),
                            'packageurl' => new external_value(PARAM_URL, 'DEFFINUM zip package URL', VALUE_OPTIONAL),
                            'version' => new external_value(PARAM_NOTAGS, 'DEFFINUM version (DEFFINUM_12, DEFFINUM_13, DEFFINUM_AICC)',
                                                            VALUE_OPTIONAL),
                            'maxgrade' => new external_value(PARAM_INT, 'Max grade', VALUE_OPTIONAL),
                            'grademethod' => new external_value(PARAM_INT, 'Grade method', VALUE_OPTIONAL),
                            'whatgrade' => new external_value(PARAM_INT, 'What grade', VALUE_OPTIONAL),
                            'maxattempt' => new external_value(PARAM_INT, 'Maximum number of attemtps', VALUE_OPTIONAL),
                            'forcecompleted' => new external_value(PARAM_BOOL, 'Status current attempt is forced to "completed"',
                                                                    VALUE_OPTIONAL),
                            'forcenewattempt' => new external_value(PARAM_INT, 'Controls re-entry behaviour',
                                                                    VALUE_OPTIONAL),
                            'lastattemptlock' => new external_value(PARAM_BOOL, 'Prevents to launch new attempts once finished',
                                                                    VALUE_OPTIONAL),
                            'displayattemptstatus' => new external_value(PARAM_INT, 'How to display attempt status',
                                                                            VALUE_OPTIONAL),
                            'displaycoursestructure' => new external_value(PARAM_BOOL, 'Display contents structure',
                                                                            VALUE_OPTIONAL),
                            'sha1hash' => new external_value(PARAM_NOTAGS, 'Package content or ext path hash', VALUE_OPTIONAL),
                            'md5hash' => new external_value(PARAM_NOTAGS, 'MD5 Hash of package file', VALUE_OPTIONAL),
                            'revision' => new external_value(PARAM_INT, 'Revison number', VALUE_OPTIONAL),
                            'launch' => new external_value(PARAM_INT, 'First content to launch', VALUE_OPTIONAL),
                            'skipview' => new external_value(PARAM_INT, 'How to skip the content structure page', VALUE_OPTIONAL),
                            'hidebrowse' => new external_value(PARAM_BOOL, 'Disable preview mode?', VALUE_OPTIONAL),
                            'hidetoc' => new external_value(PARAM_INT, 'How to display the DEFFINUM structure in player',
                                                            VALUE_OPTIONAL),
                            'nav' => new external_value(PARAM_INT, 'Show navigation buttons', VALUE_OPTIONAL),
                            'navpositionleft' => new external_value(PARAM_INT, 'Navigation position left', VALUE_OPTIONAL),
                            'navpositiontop' => new external_value(PARAM_INT, 'Navigation position top', VALUE_OPTIONAL),
                            'auto' => new external_value(PARAM_BOOL, 'Auto continue?', VALUE_OPTIONAL),
                            'popup' => new external_value(PARAM_INT, 'Display in current or new window', VALUE_OPTIONAL),
                            'width' => new external_value(PARAM_INT, 'Frame width', VALUE_OPTIONAL),
                            'height' => new external_value(PARAM_INT, 'Frame height', VALUE_OPTIONAL),
                            'timeopen' => new external_value(PARAM_INT, 'Available from', VALUE_OPTIONAL),
                            'timeclose' => new external_value(PARAM_INT, 'Available to', VALUE_OPTIONAL),
                            'deffinumtype' => new external_value(PARAM_ALPHA, 'DEFFINUM type', VALUE_OPTIONAL),
                            'reference' => new external_value(PARAM_NOTAGS, 'Reference to the package', VALUE_OPTIONAL),
                            'protectpackagedownloads' => new external_value(PARAM_BOOL, 'Protect package downloads?',
                                                                            VALUE_OPTIONAL),
                            'updatefreq' => new external_value(PARAM_INT, 'Auto-update frequency for remote packages',
                                                                VALUE_OPTIONAL),
                            'options' => new external_value(PARAM_RAW, 'Additional options', VALUE_OPTIONAL),
                            'completionstatusrequired' => new external_value(PARAM_INT, 'Status passed/completed required?',
                                                                                VALUE_OPTIONAL),
                            'completionscorerequired' => new external_value(PARAM_INT, 'Minimum score required', VALUE_OPTIONAL),
                            'completionstatusallscos' => new external_value(PARAM_INT, 'Require all scos to return completion status', VALUE_OPTIONAL),
                            'autocommit' => new external_value(PARAM_BOOL, 'Save track data automatically?', VALUE_OPTIONAL),
                            'timemodified' => new external_value(PARAM_INT, 'Time of last modification', VALUE_OPTIONAL),
                        ]
                    ), 'DEFFINUM')
                ),
                'options' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'name' => new external_value(PARAM_RAW, 'Options name'),
                            'value' => new external_value(PARAM_RAW, 'Option value')
                        ]
                    ), 'Global DEFFINUM options', VALUE_OPTIONAL
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.1
     */
    public static function launch_sco_parameters() {
        return new external_function_parameters(
            array(
                'deffinumid' => new external_value(PARAM_INT, 'DEFFINUM instance id'),
                'scoid' => new external_value(PARAM_INT, 'SCO id (empty for launching the first SCO)', VALUE_DEFAULT, 0)
            )
        );
    }

    /**
     * Trigger the course module viewed event.
     *
     * @param int $deffinumid the DEFFINUM instance id
     * @param int $scoid the SCO id
     * @return array of warnings and status result
     * @since Moodle 3.1
     * @throws moodle_exception
     */
    public static function launch_sco($deffinumid, $scoid = 0) {
        global $DB, $CFG;

        require_once($CFG->libdir . '/completionlib.php');

        $params = self::validate_parameters(self::launch_sco_parameters(),
                                            array(
                                                'deffinumid' => $deffinumid,
                                                'scoid' => $scoid
                                            ));
        $warnings = array();

        // Request and permission validation.
        $deffinum = $DB->get_record('deffinum', array('id' => $params['deffinumid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($deffinum, 'deffinum');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        // If the DEFFINUM is not open this function will throw exceptions.
        deffinum_require_available($deffinum);

        if (!empty($params['scoid']) and !($sco = deffinum_get_sco($params['scoid'], SCO_ONLY))) {
            throw new moodle_exception('cannotfindsco', 'deffinum');
        }

        // Mark module viewed.
        $completion = new completion_info($course);
        $completion->set_module_viewed($cm);

        list($sco, $scolaunchurl) = deffinum_get_sco_and_launch_url($deffinum, $params['scoid'], $context);
        // Trigger the SCO launched event.
        deffinum_launch_sco($deffinum, $sco, $cm, $context, $scolaunchurl);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return \core_external\external_description
     * @since Moodle 3.1
     */
    public static function launch_sco_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Describes the parameters for get_deffinum_access_information.
     *
     * @return external_external_function_parameters
     * @since Moodle 3.7
     */
    public static function get_deffinum_access_information_parameters() {
        return new external_function_parameters (
            array(
                'deffinumid' => new external_value(PARAM_INT, 'deffinum instance id.')
            )
        );
    }

    /**
     * Return access information for a given deffinum.
     *
     * @param int $deffinumid deffinum instance id
     * @return array of warnings and the access information
     * @since Moodle 3.7
     * @throws  moodle_exception
     */
    public static function get_deffinum_access_information($deffinumid) {
        global $DB;

        $params = self::validate_parameters(self::get_deffinum_access_information_parameters(), array('deffinumid' => $deffinumid));

        // Request and permission validation.
        $deffinum = $DB->get_record('deffinum', array('id' => $params['deffinumid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($deffinum, 'deffinum');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        $result = array();
        // Return all the available capabilities.
        $capabilities = load_capability_def('mod_deffinum');
        foreach ($capabilities as $capname => $capdata) {
            // Get fields like cansubmit so it is consistent with the access_information function implemented in other modules.
            $field = 'can' . str_replace('mod/deffinum:', '', $capname);
            $result[$field] = has_capability($capname, $context);
        }

        $result['warnings'] = array();
        return $result;
    }

    /**
     * Describes the get_deffinum_access_information return value.
     *
     * @return external_single_structure
     * @since Moodle 3.7
     */
    public static function get_deffinum_access_information_returns() {

        $structure = array(
            'warnings' => new external_warnings()
        );

        $capabilities = load_capability_def('mod_deffinum');
        foreach ($capabilities as $capname => $capdata) {
            // Get fields like cansubmit so it is consistent with the access_information function implemented in other modules.
            $field = 'can' . str_replace('mod/deffinum:', '', $capname);
            $structure[$field] = new external_value(PARAM_BOOL, 'Whether the user has the capability ' . $capname . ' allowed.',
                VALUE_OPTIONAL);
        }

        return new external_single_structure($structure);
    }
}
