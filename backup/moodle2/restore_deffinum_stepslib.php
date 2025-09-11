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
 * @package    mod_deffinum
 * @subpackage backup-moodle2
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_deffinum_activity_task
 */

/**
 * Structure step to restore one deffinum activity
 */
class restore_deffinum_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('deffinum', '/activity/deffinum');
        $paths[] = new restore_path_element('deffinum_sco', '/activity/deffinum/scoes/sco');
        $paths[] = new restore_path_element('deffinum_sco_data', '/activity/deffinum/scoes/sco/sco_datas/sco_data');
        $paths[] = new restore_path_element('deffinum_seq_objective', '/activity/deffinum/scoes/sco/seq_objectives/seq_objective');
        $paths[] = new restore_path_element('deffinum_seq_rolluprule', '/activity/deffinum/scoes/sco/seq_rolluprules/seq_rolluprule');
        $paths[] = new restore_path_element('deffinum_seq_rolluprulecond', '/activity/deffinum/scoes/sco/seq_rollupruleconds/seq_rolluprulecond');
        $paths[] = new restore_path_element('deffinum_seq_rulecond', '/activity/deffinum/scoes/sco/seq_ruleconds/seq_rulecond');
        $paths[] = new restore_path_element('deffinum_seq_rulecond_data', '/activity/deffinum/scoes/sco/seq_rulecond_datas/seq_rulecond_data');

        $paths[] = new restore_path_element('deffinum_seq_mapinfo', '/activity/deffinum/scoes/sco/seq_objectives/seq_objective/seq_mapinfos/seq_mapinfo');
        if ($userinfo) {
            $paths[] = new restore_path_element('deffinum_sco_track', '/activity/deffinum/scoes/sco/sco_tracks/sco_track');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_deffinum($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->course = $this->get_courseid();

        // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
        // See MDL-9367.
        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);

        if (!isset($data->completionstatusallscos)) {
            $data->completionstatusallscos = false;
        }
        // insert the deffinum record
        $newitemid = $DB->insert_record('deffinum', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_deffinum_sco($data) {
        global $DB;

        $data = (object)$data;

        $oldid = $data->id;
        $data->deffinum = $this->get_new_parentid('deffinum');

        $newitemid = $DB->insert_record('deffinum_scoes', $data);
        $this->set_mapping('deffinum_sco', $oldid, $newitemid);
    }

    protected function process_deffinum_sco_data($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->scoid = $this->get_new_parentid('deffinum_sco');

        $newitemid = $DB->insert_record('deffinum_scoes_data', $data);
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }

    protected function process_deffinum_seq_objective($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->scoid = $this->get_new_parentid('deffinum_sco');

        $newitemid = $DB->insert_record('deffinum_seq_objective', $data);
        $this->set_mapping('deffinum_seq_objective', $oldid, $newitemid);
    }

    protected function process_deffinum_seq_rolluprule($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->scoid = $this->get_new_parentid('deffinum_sco');

        $newitemid = $DB->insert_record('deffinum_seq_rolluprule', $data);
        $this->set_mapping('deffinum_seq_rolluprule', $oldid, $newitemid);
    }

    protected function process_deffinum_seq_rolluprulecond($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->scoid = $this->get_new_parentid('deffinum_sco');
        $data->ruleconditions = $this->get_new_parentid('deffinum_seq_rolluprule');

        $newitemid = $DB->insert_record('deffinum_seq_rolluprulecond', $data);
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }

    protected function process_deffinum_seq_rulecond($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->scoid = $this->get_new_parentid('deffinum_sco');

        $newitemid = $DB->insert_record('deffinum_seq_ruleconds', $data);
        $this->set_mapping('deffinum_seq_ruleconds', $oldid, $newitemid);
    }

    protected function process_deffinum_seq_rulecond_data($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->scoid = $this->get_new_parentid('deffinum_sco');
        $data->ruleconditions = $this->get_new_parentid('deffinum_seq_ruleconds');

        $newitemid = $DB->insert_record('deffinum_seq_rulecond', $data);
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }



    protected function process_deffinum_seq_mapinfo($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->scoid = $this->get_new_parentid('deffinum_sco');
        $data->objectiveid = $this->get_new_parentid('deffinum_seq_objective');
        $newitemid = $DB->insert_record('deffinum_scoes_data', $data);
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }

    protected function process_deffinum_sco_track($data) {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/deffinum/locallib.php');
        $data = (object)$data;
        $attemptobject = deffinum_get_attempt($this->get_mappingid('user', $data->userid),
                                           $this->get_new_parentid('deffinum'),
                                           $data->attempt);
        $data->scoid = $this->get_new_parentid('deffinum_sco');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->attemptid = $attemptobject->id;
        $data->elementid = deffinum_get_elementid($data->element);

        $DB->insert_record('deffinum_scoes_value', $data);
        // No need to save this mapping as far as nothing depend on it.
    }

    protected function after_execute() {
        global $DB;

        // Add deffinum related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_deffinum', 'intro', null);
        $this->add_related_files('mod_deffinum', 'content', null);
        $this->add_related_files('mod_deffinum', 'package', null);
        // BEGIN DEFFINUM CUSTOMIZATION.
        $this->add_related_files('mod_deffinum', 'resource', null);
        // END DEFFINUM CUSTOMIZATION.

        // Fix launch param in deffinum table to use new sco id.
        $deffinumid = $this->get_new_parentid('deffinum');
        $deffinum = $DB->get_record('deffinum', array('id' => $deffinumid));
        $deffinum->launch = $this->get_mappingid('deffinum_sco', $deffinum->launch, '');

        if (!empty($deffinum->launch)) {
            // Check that this sco has a valid launch value.
            $scolaunch = $DB->get_field('deffinum_scoes', 'launch', array('id' => $deffinum->launch));
            if (empty($scolaunch)) {
                // This is not a valid sco - set to empty so we can find a valid launch sco.
                $deffinum->launch = '';
            }
        }

        if (empty($deffinum->launch)) {
            // This deffinum has an invalid launch param - we need to calculate it and get the first launchable sco.
            $sqlselect = 'deffinum = ? AND '.$DB->sql_isnotempty('deffinum_scoes', 'launch', false, true);
            // We use get_records here as we need to pass a limit in the query that works cross db.
            $scoes = $DB->get_records_select('deffinum_scoes', $sqlselect, array($deffinumid), 'sortorder', 'id', 0, 1);
            if (!empty($scoes)) {
                $sco = reset($scoes); // We only care about the first record - the above query only returns one.
                $deffinum->launch = $sco->id;
            }
        }
        if (!empty($deffinum->launch)) {
            $DB->update_record('deffinum', $deffinum);
        }
    }
}
