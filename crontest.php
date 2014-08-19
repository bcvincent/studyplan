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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_studyplan
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// Replace studyplan with the name of your module and remove this line

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$PAGE->set_url('/mod/studyplan/crontest.php');
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();

echo "<h1>Cron Test</h1>";

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
	if ($results !== false) {
		foreach ($results as $r) { 
			#userid, courseid, studyplanid
			#studyplan, user, percent, timecreated, timemodified
			$percent=26;
			
			$progress=new stdClass();
			$progress->studyplan=$r->studyplanid;
			$progress->user=$r->userid;
			$progress->percent=$percent;
			$progress->timemodified=$now;
			if ($r->progressid) {
				$progress->id=$r->progressid;
				$DB->update_record('studyplan_progress', $progress);
			} else {
				$progress->timecreated=$now;
				$DB->insert_record('studyplan_progress', $progress);
			}
			print_r($r);
			print "<hr>\n";
		}
	}
}

//id & course

print_r($sql);

echo $OUTPUT->footer();
