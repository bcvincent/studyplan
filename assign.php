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

header ("Content-type: text/javascript");

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');

$studentid = optional_param('student', 0, PARAM_INT); // the student's ID
$blockid = optional_param('block', 0, PARAM_INT); // the block's ID
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
if ($studentid) {
	$STUDENT  = $DB->get_record('user', array('id' => $studentid), '*', MUST_EXIST);
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/studyplan:assign', $context);

add_to_log($course->id, 'studyplan', 'assign', "assign.php?id={$cm->id}&student={$studentid}&block={$blockid}", $studyplan->name, $cm->id);

// Print the page header
$PAGE->set_cacheable(false);

sp_toggle_block_assigned($blockid,$studyplan->id,$studentid);

?>
/* SUCCESS */
