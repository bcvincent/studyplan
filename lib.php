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
 * Library of interface functions and constants for module studyplan
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the studyplan specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_studyplan
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** example constant */
//define('NEWMODULE_ULTIMATE_ANSWER', 42);
define('STUDYPLAN_BLOCKS_TYPES_EVALUATE', 0);
define('STUDYPLAN_BLOCKS_TYPES_HEADER', 1);
define('STUDYPLAN_BLOCKS_TYPES_TEXT', 2);

require_once($CFG->dirroot . '/mod/studyplan/locallib.php');

////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function studyplan_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:         return true;
        case FEATURE_SHOW_DESCRIPTION:  return true;
        case FEATURE_BACKUP_MOODLE2:    return true;

        default:                        return null;
    }
}

/**
 * Saves a new instance of the studyplan into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $studyplan An object from the form in mod_form.php
 * @param mod_studyplan_mod_form $mform
 * @return int The id of the newly inserted studyplan record
 */
function studyplan_add_instance(stdClass $studyplan, mod_studyplan_mod_form $mform = null) {
    global $DB;

    $studyplan->timecreated = time();

    # You may have to add extra stuff in here #

    return $DB->insert_record('studyplan', $studyplan);
}

/**
 * Updates an instance of the studyplan in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $studyplan An object from the form in mod_form.php
 * @param mod_studyplan_mod_form $mform
 * @return boolean Success/Fail
 */
function studyplan_update_instance(stdClass $studyplan, mod_studyplan_mod_form $mform = null) {
    global $DB;

    $studyplan->timemodified = time();
    $studyplan->id = $studyplan->instance;

    # You may have to add extra stuff in here #
    #print_object($mform);
    #exit();

    #return $DB->update_record('studyplan', $studyplan);

    if (! $studyplan = $DB->update_record('studyplan', $studyplan)) {
        return false;
    }
    $mform->sp_capture_blocks();
    return true;
}

/**
 * Removes an instance of the studyplan from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function studyplan_delete_instance($id) {
    global $DB;

    if (! $studyplan = $DB->get_record('studyplan', array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    $DB->delete_records('studyplan', array('id' => $studyplan->id));
    $DB->delete_records('studyplan_blocks', array('studyplan' => $studyplan->id));
    $DB->delete_records('studyplan_overrides', array('studyplan' => $studyplan->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function studyplan_user_outline($course, $user, $mod, $studyplan) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $studyplan the module instance record
 * @return void, is supposed to echp directly
 */
function studyplan_user_complete($course, $user, $mod, $studyplan) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in studyplan activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function studyplan_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link studyplan_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function studyplan_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see studyplan_get_recent_mod_activity()}

 * @return void
 */
function studyplan_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function studyplan_cron () {
	global $DB;
	mtrace("\n  Studyplan Cron for progress updates", '');
	
	//get just the courses that have studyplans in them
	$courses=get_courses();
	$studyplans=get_all_instances_in_courses('studyplan', $courses);
	$courses_subset=array();
	$course_studyplans=array();
	foreach ($studyplans as $studyplan) { 
		$courses_subset[]=$studyplan->course;
		if (!($course_studyplans[$studyplan->course])) {
			$course_studyplans[$studyplan->course]=array();
		}
		$course_studyplans[$studyplan->course][]=$studyplan->id;
	}
	
	//only do this is there are coursese with studyplan in them 
	if ($courses_subset) {
		//find all the users that logged in today and are in the course subset
		$courses_subset_sql="courseid in (".join(",",$courses_subset).")";
		
		$last_24_hrs=time()-(24*60*60);
		#note: using left join on the progress table so that we'll get blanks to create
		#so check if progressid is null
		$sql="SELECT {user_lastaccess}.*, {studyplan}.id as studyplanid, {studyplan_progress}.id as progressid, {studyplan_progress}.percent
			FROM {user_lastaccess}
			JOIN {studyplan}
			ON {user_lastaccess}.courseid = {studyplan}.course
			LEFT JOIN {studyplan_progress}
			ON ( {studyplan_progress}.id = {studyplan_progress}.studyplan
			AND {studyplan_progress}.user = {user_lastaccess}.userid )
			WHERE {user_lastaccess}.$courses_subset_sql
			AND {user_lastaccess}.timeaccess > $last_24_hrs
		";
		
		$now=time();
		$results=$DB->get_records_sql($sql);
		mtrace("\n".count($results).' students in last 24 hours.');
		if ($results !== false) {
			foreach ($results as $r) {
				sp_calculate_student_progress($r->studyplanid,$r->userid,true,false);
			}
		}
	}
	
	mtrace('done.');
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function studyplan_get_extra_capabilities() {
    return array('mod/studyplan:review','mod/studyplan:assign','mod/studyplan:showallgroups');
    #return array();
}



////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding studyplan nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the studyplan module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function studyplan_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the studyplan settings
 *
 * This function is called when the context for the page is a studyplan module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $studyplannode {@link navigation_node}
 */
function studyplan_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $studyplannode=null) {
    global $CFG, $PAGE;    
    
    if ($studyplannode==null) { return; }
    
    if (!has_capability('mod/studyplan:review', context_module::instance($PAGE->cm->id))) {
        return;
    }
    
    $str = "Review";
    if (has_capability('mod/studyplan:assign', context_module::instance($PAGE->cm->id)) ) {
        $str = "Review and Assign";
    }
    $url = new moodle_url('/mod/studyplan/review.php', array('id' => $PAGE->cm->id));
    $node = navigation_node::create(
        $str,
        $url,
        navigation_node::NODETYPE_LEAF,
        'studyplan',
        'studyplan'
    );
    if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
        $node->make_active();
    }
    $studyplannode->add_node($node);
}
