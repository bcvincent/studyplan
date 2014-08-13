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
 * Define all the backup steps that will be used by the backup_studyplan_activity_task
 */

/**
 * Define the complete studyplan structure for backup, with file and id annotations
 */     
class backup_studyplan_activity_structure_step extends backup_activity_structure_step {
 
    protected function define_structure() {
 
        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');
 
        // Define each element separated
        $studyplan = new backup_nested_element('studyplan', array('id'), array(
            "quiz",
			"name",
			"intro",
			"introformat",
			"standardblock",
			"timecreated",
			"timemodified"));
 
        $blocks = new backup_nested_element('blocks');
 
        $block = new backup_nested_element('block', array('id'), array(
            "studyplan",
            "sequence",
			"type",
			"lookuptype",
			"keyname",
			"operator",
			"value",
			"label",
			"content",
			"contentformat",
			"activity",
			"completionactivity",
			"timecreated",
			"timemodified"));
 
        $overrides = new backup_nested_element('overrides');
 
        $override = new backup_nested_element('override', array('id'), array(
            "studyplan",
            "block",
            "user",
			"timecreated",
			"timemodified"));
 
        $progresses = new backup_nested_element('progresses');
 
        $progress = new backup_nested_element('progress', array('id'), array(
            "studyplan",
            "user",
            "percent",
			"timecreated",
			"timemodified"));
 
        // Build the tree
        $studyplan->add_child($blocks);
        $blocks->add_child($block);
 
        $studyplan->add_child($overrides);
        $overrides->add_child($override);
        
        $studyplan->add_child($progresses);
        $progresses->add_child($progress);
 
        // Define sources
        $studyplan->set_source_table('studyplan', array('id' => backup::VAR_ACTIVITYID));
 
        $block->set_source_sql('
            SELECT *
              FROM {studyplan_blocks}
             WHERE studyplan = ?',
            array(backup::VAR_PARENTID));
 
        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            $override->set_source_table('studyplan_overrides', array('studyplan' => '../../id'));
            $override->annotate_ids('studyplan', 'studyplan');
            $override->annotate_ids('studyplan_block', 'block');
            $override->annotate_ids('user', 'user');
            
            $progress->set_source_table('studyplan_progress', array('studyplan' => '../../id'));
            $progress->annotate_ids('studyplan', 'studyplan');
            $progress->annotate_ids('user', 'user');
        }
 
        // Define id annotations
        $studyplan->annotate_ids('quiz', 'quiz');
        $block->annotate_ids('studyplan', 'studyplan');
        $block->annotate_ids('course_module','activity');
        $block->annotate_ids('course_module','completionactivity');
 
        // Define file annotations
 
        // Return the root element (studyplan), wrapped into standard activity structure
        return $this->prepare_activity_structure($studyplan);
 
    }
}