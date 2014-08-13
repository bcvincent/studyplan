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
 * @package mod_studyplan
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_studyplan_activity_task
 */

/**
 * Structure step to restore one studyplan activity
 */
class restore_studyplan_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('studyplan', '/activity/studyplan');
        $paths[] = new restore_path_element('studyplan_block', '/activity/studyplan/blocks/block');
        if ($userinfo) {
            $paths[] = new restore_path_element('studyplan_override', '/activity/studyplan/overrides/override');
            $paths[] = new restore_path_element('studyplan_progress', '/activity/studyplan/progresses/progress');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_studyplan($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->quiz = $this->get_mappingid('quiz', $data->quiz);

        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // insert the studyplan record
        $newitemid = $DB->insert_record('studyplan', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_studyplan_block($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;

        $data->studyplan = $this->get_new_parentid('studyplan');
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('studyplan_blocks', $data);
        $this->set_mapping('studyplan_block', $oldid, $newitemid);
    }

    protected function process_studyplan_override($data) {
        global $DB;

        $data = (object)$data;

        $data->studyplan = $this->get_new_parentid('studyplan');
        $data->block = $this->get_mappingid('studyplan_block', $data->block);
        $data->user = $this->get_mappingid('user', $data->user);
        
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('studyplan_overrides', $data);
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }

    protected function process_studyplan_progress($data) {
        global $DB;

        $data = (object)$data;

        $data->studyplan = $this->get_new_parentid('studyplan');
        $data->user = $this->get_mappingid('user', $data->user);
        
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('studyplan_progress', $data);
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }

    protected function after_execute() {
        // Add studyplan related files, no need to match by itemname (just internally handled context)
        # $this->add_related_files('mod_studyplan', 'intro', null);
    }
}
