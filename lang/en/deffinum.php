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
 * Strings for component 'deffinum', language 'en'
 *
 * @package   mod_deffinum
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['toc'] = 'TOC';
$string['navigation'] = 'Navigation';
$string['aicchacptimeout'] = 'AICC HACP timeout';
$string['aicchacptimeout_desc'] = 'Length of time in minutes that an external AICC HACP session can remain open';
$string['aicchacpkeepsessiondata'] = 'AICC HACP session data';
$string['aicchacpkeepsessiondata_desc'] = 'Length of time in days to keep the external AICC HACP session data (a high setting will fill up the table with old data but may be useful when debugging)';
$string['aiccuserid'] = 'AICC pass numeric user id';
$string['aiccuserid_desc'] = 'The AICC standard for usernames is very restrictive compared with Moodle, and allows for alphanumeric characters, dash and underscore only. Periods, spaces and the @ symbol are not permitted. If enabled, user ID numbers are passed to the AICC package instead of usernames.';
$string['activation'] = 'Activation';
$string['activityloading'] = 'You will be automatically redirected to the activity in';
$string['activityoverview'] = 'You have DEFFINUM packages that need attention';
$string['activitypleasewait'] = 'Activity loading, please wait ...';
$string['adminsettings'] = 'Admin settings';
$string['advanced'] = 'Parameters';
$string['aliasonly'] = 'When selecting an imsmanifest.xml file from a repository you must use an alias/shortcut for this file.';
$string['allowapidebug'] = 'Activate API debug and tracing (set the capture mask with apidebugmask)';
$string['allowtypeexternal'] = 'Enable external package type';
$string['allowtypeexternalaicc'] = 'Enable direct AICC URL';
$string['allowtypeexternalaicc_desc'] = 'If enabled this allows a direct url to a simple AICC package';
$string['allowtypelocalsync'] = 'Enable downloaded package type';
$string['allowtypeaicchacp'] = 'Enable external AICC HACP';
$string['allowtypeaicchacp_desc'] = 'If enabled this allows AICC HACP external communication without requiring user login for post requests from the external AICC package';
$string['apidebugmask'] = 'API debug capture mask  - use a simple regex on &lt;username&gt;:&lt;activityname&gt; e.g. admin:.* will debug for admin user only';
$string['areacontent'] = 'Content files';
$string['areapackage'] = 'Package file';
$string['asset'] = 'Asset';
$string['assetlaunched'] = 'Asset - Viewed';
$string['attempt'] = 'Attempt';
$string['attempts'] = 'Attempts';
$string['attemptstatusall'] = 'Dashboard and entry page';
$string['attemptstatusmy'] = 'Dashboard only';
$string['attemptstatusentry'] = 'Entry page only';
$string['attemptsx'] = '{$a} attempts';
$string['attemptsmanagement'] = 'Attempts management';
$string['attempt1'] = '1 attempt';
$string['attr_error'] = 'Bad value for attribute ({$a->attr}) in tag {$a->tag}.';
$string['autocommit'] = 'Auto-commit';
$string['autocommit_help'] = 'If enabled, DEFFINUM data is automaticaly saved to the database. Useful for DEFFINUM objects which do not save their data regularly.';
$string['autocommitdesc'] = 'Automatically save DEFFINUM data if the DEFFINUM package does not save it.';
$string['autocontinue'] = 'Auto-continue';
$string['autocontinue_help'] = 'If enabled, subsequent learning objects are launched automatically, otherwise the Continue button must be used.';
$string['autocontinuedesc'] = 'If enabled, subsequent learning objects are launched automatically, otherwise the Continue button must be used.';
$string['averageattempt'] = 'Average attempts';
$string['badmanifest'] = 'Some manifest errors: see errors log';
$string['badimsmanifestlocation'] = 'An imsmanifest.xml file was found but it was not in the root of your zip file, please re-package your DEFFINUM';
$string['badarchive'] = 'You must provide a valid zip file';
$string['browse'] = 'Preview';
$string['browsed'] = 'Browsed';
$string['browsemode'] = 'Preview mode';
$string['browserepository'] = 'Browse repository';
$string['cachedef_elements'] = 'Element cache';
$string['calculatedweight'] = 'Calculated weight';
$string['calendarend'] = '{$a} closes';
$string['calendarstart'] = '{$a} opens';
$string['cannotaccess'] = 'You cannot call this script in that way';
$string['cannotfindsco'] = 'Could not find SCO';
$string['closebeforeopen'] = 'You have specified a close date before the open date.';
$string['collapsetocwinsize'] = 'Collapse TOC when window size below';
$string['collapsetocwinsizedesc'] = 'This setting lets you specify the window size below which the TOC should automatically collapse.';
$string['compatibilitysettings'] = 'Compatibility settings';
$string['completed'] = 'Completed';
$string['completiondetail:completionstatuspassed'] = 'Pass the activity';
$string['completiondetail:completionstatuscompleted'] = 'Complete the activity';
$string['completiondetail:completionstatuscompletedorpassed'] = 'Complete or pass the activity';
$string['completiondetail:completionscore'] = 'Receive a score of {$a} or more';
$string['completiondetail:allscos'] = 'Do all parts of this activity';
$string['completionscorerequired'] = 'Require minimum score';
$string['completionscorerequireddesc'] = 'Minimum score of {$a} is required for completion';
$string['completionstatus_passed'] = 'Passed';
$string['completionstatus_completed'] = 'Completed';
$string['completionstatusallscos'] = 'All SCOs must return completion status';
$string['completionstatusallscos_help'] = 'Some DEFFINUM packages contain multiple components or "scos" - when this is enabled all scos within the package must return the relevant lesson_status for this activity to be flagged complete.';
$string['completionstatusrequired'] = 'Require status';
$string['completionstatusrequireddesc'] = 'Student must achieve at least one of the following statuses: {$a}';
$string['completionstatusrequired_help'] = 'Checking one or more statuses will require a user to achieve at least one of the checked statuses in order to be marked complete in this DEFFINUM activity, as well as any other Activity Completion requirements.';
$string['confirmloosetracks'] = 'WARNING: The package seems to be changed or modified. If the package structure is changed, some users tracks may be lost during update process.';
$string['contents'] = 'Contents';
$string['coursepacket'] = 'Course package';
$string['coursestruct'] = 'Content structure';
$string['crontask'] = 'Background processing for DEFFINUM';
$string['currentwindow'] = 'Current window';
$string['datadir'] = 'Filesystem error: Can\'t create course data directory';
$string['defaultdisplaysettings'] = 'Default display settings';
$string['defaultgradesettings'] = 'Default grade settings';
$string['defaultothersettings'] = 'Other default settings';
$string['deleteattemptcheck'] = 'Are you absolutely sure you want to completely delete these attempts?';
$string['deleteallattempts'] = 'All DEFFINUM attempts';
$string['deleteselected'] = 'Delete selected attempts';
$string['deleteuserattemptcheck'] = 'Are you absolutely sure you want to completely delete all your attempts?';
$string['details'] = 'Track details';
$string['directories'] = 'Show the directory links';
$string['disabled'] = 'Disabled';
$string['display'] = 'Display package';
$string['displayattemptstatus'] = 'Display attempt status';
$string['displayattemptstatus_help'] = 'This preference allows a summary of the users attempts to show in the course overview block in Dashboard and/or the DEFFINUM entry page.';
$string['displayattemptstatusdesc'] = 'Whether a summary of the user\'s attempts is shown in the course overview block in Dashboard and/or the DEFFINUM entry page.';
$string['displaycoursestructure'] = 'Display content structure on entry page';
$string['displaycoursestructure_help'] = 'If enabled, the table of contents is displayed on the DEFFINUM outline page.';
$string['displaycoursestructuredesc'] = 'If enabled, the table of contents is displayed on the DEFFINUM outline page.';
$string['displaydesc'] = 'Whether to display the DEFFINUM package in a new window.';
$string['displaysettings'] = 'Display settings';
$string['dnduploaddeffinum'] = 'Add a DEFFINUM package';
$string['domxml'] = 'DOMXML external library';
$string['element'] = 'Element';
$string['enter'] = 'Enter';
$string['entercourse'] = 'Enter course';
$string['errorlogs'] = 'Errors log';
$string['eventattemptdeleted'] = 'Attempt deleted';
$string['eventinteractionsviewed'] = 'Interactions viewed';
$string['eventreportviewed'] = 'Report viewed';
$string['eventscolaunched'] = 'Sco launched';
$string['eventscorerawsubmitted'] = 'Submitted DEFFINUM raw score';
$string['eventstatussubmitted'] = 'Submitted DEFFINUM status';
$string['eventtracksviewed'] = 'Tracks viewed';
$string['eventuserreportviewed'] = 'User report viewed';
$string['everyday'] = 'Every day';
$string['everytime'] = 'Every time it\'s used';
$string['exceededmaxattempts'] = 'You have reached the maximum number of attempts.';
$string['exit'] = 'Exit course';
$string['exitactivity'] = 'Exit activity';
$string['expired'] = 'Sorry, this activity closed on {$a} and is no longer available';
$string['external'] = 'Update external packages timing';
$string['failed'] = 'Failed';
$string['finishdeffinum'] = 'If you have finished viewing this resource, {$a}';
$string['finishdeffinumlinkname'] = 'click here to return to the course page';
$string['firstaccess'] = 'First access';
$string['firstattempt'] = 'First attempt';
$string['floating'] = 'Floating';
$string['forcecompleted'] = 'Force completed';
$string['forcecompleted_help'] = 'If enabled, the status of the current attempt is forced to "completed". (Only applicable to DEFFINUM 1.2 packages.)';
$string['forcecompleteddesc'] = 'This preference sets the default value for the force completed setting';
$string['forcenewattempts'] = 'Force new attempt';
$string['forcenewattempts_help'] = 'There are 3 options:

* No - If a previous attempt is completed, passed or failed, the student will be provided with the option to enter in review mode or start a new attempt.
* When previous attempt completed, passed or failed - This relies on the DEFFINUM package setting the status of \'completed\', \'passed\' or \'failed\'.
* Always - Each re-entry to the DEFFINUM activity will generate a new attempt and the student will not be returned to the same point they reached in their previous attempt.';
$string['forceattemptalways'] = 'Always';
$string['forceattemptoncomplete'] = 'When previous attempt completed, passed or failed';
$string['forcejavascript'] = 'Force users to enable JavaScript';
$string['forcejavascript_desc'] = 'If enabled (recommended) this prevents access to DEFFINUM objects when JavaScript is not supported/enabled in a users browser. If disabled the user may view the DEFFINUM but API communication will fail and no grade information will be saved.';
$string['forcejavascriptmessage'] = 'JavaScript is required to view this object, please enable JavaScript in your browser and try again.';
$string['found'] = 'Manifest found';
$string['frameheight'] = 'The height of the stage frame or window.';
$string['framewidth'] = 'The width of the stage frame or window.';
$string['fromleft'] = 'From left';
$string['fromtop'] = 'From top';
$string['fullscreen'] = 'Fill the whole screen';
$string['general'] = 'General data';
$string['gradeaverage'] = 'Average grade';
$string['gradeforattempt'] = 'Grade for attempt';
$string['gradehighest'] = 'Highest grade';
$string['grademethod'] = 'Grading method';
$string['grademethod_help'] = 'The grading method defines how the grade for a single attempt of the activity is determined.

There are 4 grading methods:

* Learning objects - The number of completed/passed learning objects
* Highest grade - The highest score obtained in all passed learning objects
* Average grade - The mean of all the scores
* Sum grade - The sum of all the scores';
$string['grademethoddesc'] = 'The grading method defines how the grade for a single attempt of the activity is determined.';
$string['gradereported'] = 'Grade reported';
$string['gradesettings'] = 'Grade settings';
$string['gradescoes'] = 'Learning objects';
$string['gradesum'] = 'Sum grade';
$string['height'] = 'Height';
$string['hidden'] = 'Hidden';
$string['hidebrowse'] = 'Disable preview mode';
$string['hidebrowse_help'] = 'Preview mode allows a student to browse an activity before attempting it. If preview mode is disabled, the preview button is hidden.';
$string['hidebrowsedesc'] = 'Preview mode allows a student to browse an activity before attempting it.';
$string['hideexit'] = 'Hide exit link';
$string['hidereview'] = 'Hide review button';
$string['hidetoc'] = 'Display content structure in player';
$string['hidetoc_help'] = 'How the table of contents is displayed in the DEFFINUM player';
$string['hidetocdesc'] = 'This setting specifies how the table of contents is displayed in the DEFFINUM player.';
$string['highestattempt'] = 'Highest attempt';
$string['chooseapacket'] = 'Choose or update a package';
$string['identifier'] = 'Question identifier';
$string['incomplete'] = 'Incomplete';
$string['indicator:cognitivedepth'] = 'DEFFINUM cognitive';
$string['indicator:cognitivedepth_help'] = 'This indicator is based on the cognitive depth reached by the student in a DEFFINUM activity.';
$string['indicator:cognitivedepthdef'] = 'DEFFINUM cognitive';
$string['indicator:cognitivedepthdef_help'] = 'The participant has reached this percentage of the cognitive engagement offered by the DEFFINUM activities during this analysis interval (Levels = No view, View, Submit, View feedback)';
$string['indicator:cognitivedepthdef_link'] = 'Learning_analytics_indicators#Cognitive_depth';
$string['indicator:socialbreadth'] = 'DEFFINUM social';
$string['indicator:socialbreadth_help'] = 'This indicator is based on the social breadth reached by the student in a DEFFINUM activity.';
$string['indicator:socialbreadthdef'] = 'DEFFINUM social';
$string['indicator:socialbreadthdef_help'] = 'The participant has reached this percentage of the social engagement offered by the DEFFINUM activities during this analysis interval (Levels = No participation, Participant alone)';
$string['indicator:socialbreadthdef_link'] = 'Learning_analytics_indicators#Social_breadth';

$string['interactions'] = 'Interactions';
$string['largetrackupgrade'] = 'This next upgrade step may take some time to complete, your site has {$a} DEFFINUM track records that need to be migrated to the new table structure, please be patient as a progress bar is not able to be displayed.';
$string['masteryoverride'] = 'Mastery score overrides status';
$string['masteryoverride_help'] = 'If enabled and a mastery score is provided, when LMSFinish is called and a raw score has been set, status will be recalculated using the raw score and mastery score and any status provided by the DEFFINUM (including "incomplete") will be overridden.';
$string['masteryoverridedesc'] = 'This preference sets the default for the mastery score override setting';
$string['myattempts'] = 'My attempts';
$string['myaiccsessions'] = 'My AICC sessions';
$string['repositorynotsupported'] = 'This repository does not support linking directly to an imsmanifest.xml file.';
$string['trackid'] = 'ID';
$string['trackid_help'] = 'This is the identifier set by your DEFFINUM package for this question, the DEFFINUM specification doesn\'t allow the full question text to be provided.';
$string['trackcorrectcount'] = 'Correct count';
$string['trackcorrectcount_help'] = 'Number of correct results for the question';
$string['trackpattern'] = 'Pattern';
$string['trackpattern_help'] = 'This is what a correct response to this question would be, it does not show the learners response.';
$string['tracklatency'] = 'Latency';
$string['tracklatency_help'] = 'The time elapsed between the time the question was made available to the learner for a response and the time of the first response.';
$string['trackresponse'] = 'Response';
$string['trackresponse_help'] = 'This is the response made by the learner for this question';
$string['trackresult'] = 'Result';
$string['trackresult_help'] = 'Shows if the learner entered a correct response.';
$string['trackscoremin'] = 'Minimum score';
$string['trackscoremin_help'] = 'Minimum value that can be assigned for the raw score';
$string['trackscoremax'] = 'Maximum score';
$string['trackscoremax_help'] = 'Maximum value that can be assigned for the raw score';
$string['trackscoreraw'] = 'Raw score';
$string['trackscoreraw_help'] = 'Number that reflects the performance of the learner relative to the range bounded by the values of min and max';
$string['tracksuspenddata'] = 'Suspend data';
$string['tracksuspenddata_help'] = 'Provides space to store and retrieve data between learner sessions';
$string['tracktime'] = 'Time';
$string['tracktime_help'] = 'Time at which the attempt was started';
$string['tracktype'] = 'Type';
$string['tracktype_help'] = 'Type of the question, for example "choice" or "shortanswer".';
$string['trackweight'] = 'Weight';
$string['trackweight_help'] = 'Weight assigned to the question when calculating score.';
$string['invalidactivity'] = 'DEFFINUM activity is incorrect';
$string['invalidmanifestname'] = 'Only imsmanifest.xml or .zip files may be selected';
$string['invalidstatus'] = 'Invalid status';
$string['invalidurl'] = 'Invalid URL specified';
$string['invalidurlhttpcheck'] = 'Invalid URL specified. Debug message:<pre>{$a->cmsg}</pre>';
$string['invalidhacpsession'] = 'Invalid HACP session';
$string['invalidmanifestresource'] = 'WARNING: The following resources were referenced in your manifest but couldn\'t be found:';
$string['last'] = 'Last accessed on';
$string['lastaccess'] = 'Last access';
$string['lastattempt'] = 'Last completed attempt';
$string['lastattemptlock'] = 'Lock after final attempt';
$string['lastattemptlock_help'] = 'If enabled, a student is prevented from launching the DEFFINUM player after using up all their allocated attempts.';
$string['lastattemptlockdesc'] = 'If enabled, a student is prevented from launching the DEFFINUM player after using up all their allocated attempts.';
$string['location'] = 'Show the location bar';
$string['max'] = 'Max score';
$string['maximumattempts'] = 'Number of attempts';
$string['maximumattempts_help'] = 'This setting enables the number of attempts to be restricted. It is only applicable for DEFFINUM 1.2 and AICC packages.';
$string['maximumattemptsdesc'] = 'This preference sets the default maximum attempts for an activity';
$string['maximumgradedesc'] = 'This preference sets the default maximum grade for an activity';
$string['menubar'] = 'Show the menu bar';
$string['min'] = 'Minimum score';
$string['minimumscoregreater'] = 'Minimum score must be greater than 0.';
$string['missing_attribute'] = 'Missing attribute {$a->attr} in tag {$a->tag}';
$string['missingparam'] = 'A required parameter is missing or wrong';
$string['missing_tag'] = 'Missing tag {$a->tag}';
$string['mode'] = 'Mode';
$string['modulename'] = 'DEFFINUM package';
$string['modulename_help'] = 'A DEFFINUM package is a collection of files which are packaged according to an agreed standard for learning objects. The DEFFINUM activity module enables DEFFINUM or AICC packages to be uploaded as a zip file and added to a course.

Content is usually displayed over several pages, with navigation between the pages. There are various options for displaying content in a pop-up window, with a table of contents, with navigation buttons etc. DEFFINUM activities generally include questions, with grades being recorded in the gradebook.

DEFFINUM activities may be used

* For presenting multimedia content and animations
* As an assessment tool';
$string['modulename_link'] = 'mod/deffinum/view';
$string['modulenameplural'] = 'DEFFINUM packages';
$string['nav'] = 'Show Navigation';
$string['nav_help'] = 'This setting specifies whether to show or hide the navigation buttons and their position.

There are 3 options:

* No - Navigation buttons are not shown
* Under content - Navigation buttons are shown below the DEFFINUM package content
* Floating - Navigation buttons are shown floating, with the position from the top and from the left determined by the package.';
$string['navdesc'] = 'This setting specifies whether to show or hide navigation buttons and their position.';
$string['navpositionleft'] = 'Position of navigation buttons from left in pixels.';
$string['navpositiontop'] = 'Position of navigation buttons from top in pixels.';
$string['networkdropped'] = 'The DEFFINUM player has determined that your Internet connection is unreliable or has been interrupted. If you continue in this DEFFINUM activity, your progress may not be saved.<br />
You should exit the activity now, and return when you have a dependable Internet connection.';
$string['newattempt'] = 'Start a new attempt';
$string['next'] = 'Continue';
$string['noactivity'] = 'Nothing to report';
$string['noattemptsallowed'] = 'Number of attempts allowed';
$string['noattemptsmade'] = 'Number of attempts you have made';
$string['no_attributes'] = 'Tag {$a->tag} must have attributes';
$string['no_children'] = 'Tag {$a->tag} must have children';
$string['nolimit'] = 'Unlimited attempts';
$string['nomanifest'] = 'Incorrect file package - missing imsmanifest.xml or AICC structure';
$string['noprerequisites'] = 'Sorry but you don\'t have the required prerequisites to access this activity.';
$string['noreports'] = 'No report to display';
$string['normal'] = 'Normal';
$string['noscriptnodeffinum'] = 'Your browser does not support JavaScript or it has JavaScript support disabled. This DEFFINUM package may not play or save data correctly.';
$string['notattempted'] = 'Not attempted';
$string['not_corr_type'] = 'Type mismatch for tag {$a->tag}';
$string['notopenyet'] = 'Sorry, this activity is not available until {$a}';
$string['objectives'] = 'Objectives';
$string['openafterclose'] = 'You have specified an open date after the close date';
$string['optallstudents'] = 'all users';
$string['optattemptsonly'] = 'users with attempts only';
$string['optnoattemptsonly'] = 'users with no attempts only';
$string['options'] = 'Options (Prevented by some browsers)';
$string['optionsadv'] = 'Options (Advanced)';
$string['optionsadv_desc'] = 'If checked the width and height will be listed as advanced settings.';
$string['organization'] = 'Organisation';
$string['organizations'] = 'Organisations';
$string['othersettings'] = 'Additional settings';
$string['page-mod-deffinum-x'] = 'Any DEFFINUM module page';
$string['pagesize'] = 'Page size';
$string['package'] = 'Package file';
$string['package_help'] = 'The package file is a zip (or pif) file containing DEFFINUM/AICC course definition files.';
$string['packagedir'] = 'Filesystem error: Can\'t create package directory';
$string['packagefile'] = 'No package file specified';
$string['packagehdr'] = 'Package';
$string['packageurl'] = 'URL';
$string['packageurl_help'] = 'This setting enables a URL for the DEFFINUM package to be specified, rather than choosing a file via the file picker.';
$string['passed'] = 'Passed';
$string['php5'] = 'PHP 5 (DOMXML native library)';
$string['pluginadministration'] = 'DEFFINUM package administration';
$string['pluginname'] = 'DEFFINUM package';
$string['popup'] = 'New window';
$string['popuplaunched'] = 'This DEFFINUM package has been launched in a popup window, If you have finished viewing this resource, click here to return to the course page';
$string['popupmenu'] = 'In a drop-down menu';
$string['popupopen'] = 'Open package in a new window';
$string['popupsblocked'] = 'It appears that popup windows are blocked, stopping this DEFFINUM package from playing. Please check your browser settings before trying again.';
$string['position_error'] = 'The {$a->tag} tag can\'t be child of {$a->parent} tag';
$string['preferencesuser'] = 'Preferences for this report';
$string['preferencespage'] = 'Preferences just for this page';
$string['prev'] = 'Previous';
$string['privacy:metadata:aicc:data'] = 'Personal data passed through from the AICC/DEFFINUM subsystem.';
$string['privacy:metadata:aicc:externalpurpose'] = 'This plugin sends data externally using the AICC HACP.';
$string['privacy:metadata:aicc_session:lessonstatus'] = 'The lesson status to be tracked';
$string['privacy:metadata:aicc_session:deffinummode'] = 'The mode of the element to be tracked';
$string['privacy:metadata:aicc_session:deffinumstatus'] = 'The status of the element to be tracked';
$string['privacy:metadata:aicc_session:sessiontime'] = 'The session time to be tracked';
$string['privacy:metadata:aicc_session:timecreated'] = 'The time when the tracked element was created';
$string['privacy:metadata:attempt'] = 'The attempt number';
$string['privacy:metadata:scoes_track:element'] = 'The name of the element to be tracked';
$string['privacy:metadata:scoes_track:value'] = 'The value of the given element';
$string['privacy:metadata:deffinum_aicc_session'] = 'The session information of the AICC HACP';
$string['privacy:metadata:deffinum_attempt'] = 'The DEFFINUM attempts made by a user';
$string['privacy:metadata:timemodified'] = 'The time when the tracked element was last modified';
$string['privacy:metadata:userid'] = 'The ID of the user who accessed the DEFFINUM activity';
$string['protectpackagedownloads'] = 'Protect package downloads';
$string['protectpackagedownloads_desc'] = 'If enabled, DEFFINUM packages can be downloaded only if the user has the course:manageactivities capability. If disabled, DEFFINUM packages can always be downloaded (by mobile or other means).';
$string['raw'] = 'Raw score';
$string['regular'] = 'Regular manifest';
$string['report'] = 'Report';
$string['reports'] = 'Reports';
$string['reportcountallattempts'] = '{$a->nbattempts} attempts for {$a->nbusers} users, out of {$a->nbresults} results';
$string['reportcountattempts'] = '{$a->nbresults} results ({$a->nbusers} users)';
$string['response'] = 'Response';
$string['result'] = 'Result';
$string['results'] = 'Results';
$string['review'] = 'Review';
$string['reviewmode'] = 'Review mode';
$string['rightanswer'] = 'Right answer';
$string['deffinumstandard'] = 'DEFFINUM standards mode';
$string['deffinumstandarddesc'] = 'When disabled, Moodle allows DEFFINUM 1.2 packages to store more than the specification allows, and uses Moodle full name format settings when passing the users name to the DEFFINUM package.';
$string['scoes'] = 'Learning objects';
$string['score'] = 'Score';
$string['deffinum:addinstance'] = 'Add a new DEFFINUM package';
$string['deffinumclose'] = 'Available to';
$string['deffinumcourse'] = 'Learning course';
$string['deffinum:deleteresponses'] = 'Delete DEFFINUM attempts';
$string['deffinumloggingoff'] = 'API logging is off';
$string['deffinumloggingon'] = 'API logging is on';
$string['deffinumopen'] = 'Available from';
$string['deffinumresponsedeleted'] = 'Deleted user attempts';
$string['deffinum:deleteownresponses'] = 'Delete own attempts';
$string['deffinum:savetrack'] = 'Save tracks';
$string['deffinum:skipview'] = 'Skip overview';
$string['deffinumtype'] = 'Type';
$string['deffinumtype_help'] = 'This setting determines how the package is included in the course. There are up to 4 options:

* Uploaded package - Enables a DEFFINUM package to be chosen via the file picker
* External DEFFINUM manifest - Enables an imsmanifest.xml URL to be specified. Note: If the URL has a different domain name than your site, then "Downloaded package" is a better option, since otherwise grades are not saved.
* Downloaded package - Enables a package URL to be specified. The package will be unzipped and saved locally, and updated when the external DEFFINUM package is updated.
* External AICC URL - this URL is the launch URL for a single AICC Activity.  A psuedo package will be constructed around this.';
$string['deffinum:viewreport'] = 'View reports';
$string['deffinum:viewscores'] = 'View scores';
$string['scrollbars'] = 'Allow the window to be scrolled';
$string['search:activity'] = 'DEFFINUM package - activity information';
$string['selectall'] = 'Select all';
$string['selectnone'] = 'Deselect all';
$string['show'] = 'Show';
$string['sided'] = 'To the side';
$string['skipview'] = 'Student skip content structure page';
$string['skipview_help'] = 'This setting specifies whether the content structure page should ever be skipped (not displayed). If the package contains only one learning object, the content structure page can always be skipped.';
$string['skipviewdesc'] = 'This preference sets the default for when to skip content structure for a page';
$string['slashargs'] = 'WARNING: slash arguments is disabled on this site and objects may not function as expected!';
$string['stagesize'] = 'Stage size';
$string['stagesize_help'] = 'These two settings specify the frame/window width and height for the learning objects.';
$string['started'] = 'Started on';
$string['status'] = 'Status';
$string['statusbar'] = 'Show the status bar';
$string['student_response'] = 'Response';
$string['subplugintype_deffinumreport'] = 'Report';
$string['subplugintype_deffinumreport_plural'] = 'Reports';
$string['suspended'] = 'Suspended';
$string['syntax'] = 'Syntax error';
$string['tag_error'] = 'Unknown tag ({$a->tag}) with this content: {$a->value}';
$string['time'] = 'Time';
$string['title'] = 'Title';
$string['toolbar'] = 'Show the toolbar';
$string['too_many_attributes'] = 'Tag {$a->tag} has too many attributes';
$string['too_many_children'] = 'Tag {$a->tag} has too many children';
$string['totaltime'] = 'Time';
$string['trackingloose'] = 'WARNING: The tracking data of this package will be lost!';
$string['type'] = 'Type';
$string['typeaiccurl'] = 'External AICC URL';
$string['typeexternal'] = 'External DEFFINUM manifest';
$string['typelocal'] = 'Uploaded package';
$string['typelocalsync'] = 'Downloaded package';
$string['undercontent'] = 'Under content';
$string['unziperror'] = 'An error occurs during package unzip';
$string['updatefreq'] = 'Auto-update frequency';
$string['updatefreq_error'] = 'Auto-update frequency can only be set when the package file is hosted externally';
$string['updatefreq_help'] = 'This allows the external package to be automatically downloaded and updated';
$string['updatefreqdesc'] = 'This preference sets the default auto-update frequency of an activity';
$string['validateadeffinum'] = 'Validate a package';
$string['validation'] = 'Validation result';
$string['validationtype'] = 'This preference set the DOMXML library used for validating DEFFINUM Manifest. If you don\'t know leave the selected choice.';
$string['value'] = 'Value';
$string['versionwarning'] = 'The manifest version is older than 1.3, warning at {$a->tag} tag';
$string['viewallreports'] = 'View reports for {$a} attempts';
$string['viewalluserreports'] = 'View reports for {$a} users';
$string['whatgrade'] = 'Attempts grading';
$string['whatgrade_help'] = 'If multiple attempts are allowed, this setting specifies whether the highest, average (mean), first or last completed attempt is recorded in the gradebook. The last completed attempt option does not include attempts with a \'failed\' status.

Notes on handling of multiple attempts:

* The option to start a new attempt is provided by a checkbox above the Enter button on the content structure page, so be sure you\'re providing access to that page if you want to allow more than one attempt.
* Some DEFFINUM packages are intelligent about new attempts, many are not. What this means is that if the learner re-enters an existing attempt, if the DEFFINUM content does not have internal logic to avoid overwriting previous attempts they can be overwritten, even though the attempt was \'completed\' or \'passed\'.
* The settings "Force completed", "Force new attempt" and "Lock after final attempt" also provide further management of multiple attempts.';
$string['whatgradedesc'] = 'Whether the highest, average (mean), first or last completed attempt is recorded in the gradebook if multiple attempts are allowed.';
$string['width'] = 'Width';
$string['window'] = 'Window';
$string['youmustselectastatus'] = 'You must select a status to require';

// Deprecated since Moodle 4.3.
$string['completionscorerequired_help'] = 'Enabling this setting will require a user to have at least the minimum score entered to be marked complete in this DEFFINUM activity, as well as any other Activity Completion requirements.';

// Custom deffinum lang.
$string['resource'] = 'Resource file accessible without connection';
$string['resource_help'] = 'The resource file is a zip (or pif) file containing DEFFINUM/AICC course definition files.';
$string['resourcefile'] = 'No resource file specified';
$string['resourcehdr'] = 'Resource';
$string['resourceurl'] = 'URL';
$string['resourceurl_help'] = 'This setting enables a URL for the DEFFINUM package to be specified, rather than choosing a file via the file picker.';
$string['filenotfound'] = 'File not found, sorry.';
$string['deffinumsettings'] = 'Specific DEFFINUM settings.';
$string['alloweddomains'] = 'Allowed domains';
$string['complete']  = 'Completed';
$string['customtype'] = 'Type';
$string['customtype_360'] = '360';
$string['customtype_augmented_reality'] = 'Augmented reality';
$string['customtype_virtual_reality'] = 'Virtual reality';
$string['customtype_serious_game'] = 'Serious game';
$string['virtual_reality_instructions'] = 'Here is the download link for the Virtual Reality module to place in your headset {$a->downloadlink}.<br>Once the module is launched, you will be asked to log in with your Moodle credentials by entering the following code <strong style="font-size: 120%">{$a->scoid}</strong> so that your progress can be tracked.';
$string['visiturl'] = 'Visit url';
$string['domainnotallowed'] = 'Domain not allowed';
$string['privacy:metadata:deffinumuserjsonra'] = 'User Json info for RA';
$string['privacy:metadata:deffinumuserjsonrv'] = 'User Json info for RV';
$string['privacy:metadata:deffinumuserjsonsg'] = 'User Json info for SG';
$string['privacy:metadata:deffinumuserjson360'] = 'User Json info for 360';
$string['vrurl'] = 'Resource link';
