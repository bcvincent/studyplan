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
 * Prints a particular instance of studyplan
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_studyplan
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace studyplan with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');


$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // studyplan instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('studyplan', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $studyplan  = $DB->get_record('studyplan', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $studyplan  = $DB->get_record('studyplan', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $studyplan->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('studyplan', $studyplan->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'studyplan', 'view', "view.php?id={$cm->id}", $studyplan->name, $cm->id);

// Print the page header
$PAGE->set_url('/mod/studyplan/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($studyplan->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_cacheable(false);
$PAGE->requires->css("/mod/studyplan/view.css");
$PAGE->add_body_class('studyplan-view');
echo $OUTPUT->header();

// Output starts here
echo $OUTPUT->heading($studyplan->name);

if ($studyplan->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('studyplan', $studyplan, $cm->id), 'studyplan-intro', 'studyplanintro');
}

if ($studyplan->standardblock) {
	echo '<div class="studyplan-standard">';
	include(dirname(__FILE__).'/lang/en/intro.php');
	echo '</div>';
}

$attempts = quiz_get_user_attempts($studyplan->quiz, $USER->id, 'finished', true);
if (empty($attempts)) { 
	$url = new moodle_url('/mod/quiz/view.php', array('q' => $studyplan->quiz));
	$quiz_name = htmlentities(sp_get_quiz_name($studyplan->quiz));
	print "<h2 class=\"studyplan-header studyplan-no-quiz\">".
			"You have not finished the <a href=\"$url\">$quiz_name</a>.".
			"</h2>"; 
} else {
	$lastfinishedattempt = end($attempts);
	$attemptobj = quiz_attempt::create($lastfinishedattempt->id);
	$presummary=sp_presummarize($attemptobj);
	echo sp_render_legend();
	echo sp_render_block($studyplan->id,$presummary);
}

// Finish the page
echo $OUTPUT->footer();
