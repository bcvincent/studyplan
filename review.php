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
require_once($CFG->dirroot . '/lib/grouplib.php');

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');


$studentid = optional_param('student', 0, PARAM_INT); // the student's ID
$groupid = optional_param('group', -1, PARAM_INT); // the group's ID
$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // studyplan instance ID - it should be named as the first character of the module
$showtabulation  = optional_param('showtabulation', 0, PARAM_INT);  // show how we calculate the math in the layout

if ($id) {
    $cm         = get_coursemodule_from_id('studyplan', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $studyplan  = $DB->get_record('studyplan', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $studyplan  = $DB->get_record('studyplan', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $studyplan->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('studyplan', $studyplan->id, $course->id, false, MUST_EXIST);
} else {
    error( get_string('specifyiderror', 'studyplan') );
}
if ($studentid) {
	$STUDENT  = $DB->get_record('user', array('id' => $studentid), '*', MUST_EXIST);
}
if (!groups_get_course_groupmode($course)) {
	$groupid=0;
}
if ($groupid==-1) { //get the first group id for this user cause they didn't tell me
	$user_groups=sp_utility_nestedArrayCollapse(groups_get_user_groups($course->id));
	if (!empty($user_groups)) {
		$new_groupid=intval($user_groups[0]);
		if ($new_groupid!=$groupid) {
			$groupid=$new_groupid;
			$_GET["group"]=$new_groupid;
			$_REQUEST["group"]=$new_groupid;
		}
	}
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/studyplan:review', $context);
add_to_log($course->id, 'studyplan', 'review', "review.php?id={$cm->id}&student={$studentid}&student={$groupid}", $studyplan->name, $cm->id);

// Print the page header
$PAGE->set_url('/mod/studyplan/review.php', array('id' => $cm->id,'student' => $studentid,'group' => $groupid));
$justid_url="review.php?id={$cm->id}";
$PAGE->set_title(format_string($studyplan->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_cacheable(false);
$PAGE->requires->css("/mod/studyplan/view.css");
$PAGE->add_body_class('studyplan-review');
echo $OUTPUT->header();

// Output starts here
echo "\n".
'<script language="JavaScript" type="text/javascript">
	function studyplanStudentSelectorChanged(elem) {
		if (elem.selectedIndex>=0) {
			var id=elem.options[elem.selectedIndex].value
			var url=document.location.href
			url=url.substring(0,url.indexOf("?"))
			url=url+"?id='.$cm->id.'&group='.$groupid.'&student="+id
			document.location.href=url
		}
	}
</script>'."\n";

echo "\n".
'<script language="JavaScript" type="text/javascript">
	function studyplanGroupSelectorChanged(elem) {
		if (elem.selectedIndex>=0) {
			var id=elem.options[elem.selectedIndex].value
			var url=document.location.href
			url=url.substring(0,url.indexOf("?"))
			url=url+"?id='.$cm->id.'&group="+id
			document.location.href=url
		}
	}
</script>'."\n";

echo "\n".
'<script language="JavaScript" type="text/javascript">
	function studyplanAssign(elem) {
		if (Y) {
			var button=Y.one(elem)
			var div=button.ancestor("div")
			var blockid=button.getAttribute("data-blockid")
			var assigned=button.getAttribute("data-assigned")
			button.setAttribute("disabled","disabled")
			var url="assign.php?sesskey='.sesskey().'&id='.$cm->id.'&group='.$groupid.'&student='.$studentid.'&block="+blockid
			Y.io(url, {
		    on:   {success: (function(transactionid, response, arguments) {
					if (assigned=="1") {
						button.removeAttribute("disabled")
						button.setAttribute("data-assigned","0")
						button.setAttribute("value","Assign")
						div.removeClass("studyplan-block-teacher-assigned")
					} else {
						button.removeAttribute("disabled")
						button.setAttribute("data-assigned","1")
						button.setAttribute("value","Remove")
						div.addClass("studyplan-block-teacher-assigned")
					}
					try {
						eval(response.responseText);
					} catch(err) { }
			    })}
			})
		}
	}
</script>'."\n";

echo "<table width='100%'><tr>";
echo "<td id='studyplan-review-left-column' valign='top'>";

if (groups_get_course_groupmode($course)) {
	echo get_string('groups', 'studyplan') . "<br>\n";
	echo "<select name=\"group\" onchange=\"studyplanGroupSelectorChanged(this)\">";
	if ( has_capability('mod/studyplan:showallgroups', context_module::instance($PAGE->cm->id)) ) {
		print "<option value=\"0\">".get_string('allparticipants', 'studyplan')."</option>\n";
		$groups=groups_get_all_groups($course->id);
	} else {
		$groups=sp_utility_nestedArrayCollapse(groups_get_user_groups($course->id));
	}
	foreach ($groups as $g) {
		if (gettype($g)=="object") {
			$g_id=$g->id;
			$g_name=$g->name;
		} else {
			$g_id=$g;
			$g_name=groups_get_group_name($g);
		}
		$s="";
		if ($g_id==$groupid) { $s=' selected="selected" ';}
		print "<option value=\"$g_id\"  $s>".htmlentities($g_name)."</option>\n";
	}
	echo "</select><br> ";
}

if ($groupid<1) { 
	echo get_string('allparticipants', 'studyplan'); 
} else {
	echo get_string('participantplural', 'studyplan');
}
echo "<br>\n";
echo "<select size=\"35\" id=\"studyplan-review-student-selector\" onchange=\"studyplanStudentSelectorChanged(this)\">";
if (empty($groups)) {
	$participants=array();
} elseif ($groupid<1) {
	$participants=get_enrolled_users($context);
} else {
	$participants=groups_get_members($groupid);
}
foreach ($participants as $u) {
	$s="";
	if ($u->id==$studentid) { $s=' selected="selected" ';}
	print "<option value=\"$u->id\"  $s>".htmlentities($u->firstname)." ".htmlentities($u->lastname)."</option>\n";
}
echo "</select>";
echo "</td>";

echo "<td id='studyplan-review-right-column' valign='top'>";

$heading= get_string('nostudentselected', 'studyplan');

if (isset($STUDENT)) { 
	$heading="$studyplan->name for $STUDENT->firstname $STUDENT->lastname";
}
echo $OUTPUT->heading($heading);

if (isset($STUDENT)) {
	if ($studyplan->intro) { // Conditions to show the intro can change to look for own settings or whatever
	    echo $OUTPUT->box(format_module_intro('studyplan', $studyplan, $cm->id), 'studyplan-intro', 'studyplanintro');
	}
	
	if ($studyplan->standardblock) {
		echo '<div class="studyplan-standard">';
		include(dirname(__FILE__).'/intro.php');
		echo '</div>';
	}
	
	$attempts = quiz_get_user_attempts($studyplan->quiz, $STUDENT->id, 'finished', true);
	if (empty($attempts)) { 
		$url = new moodle_url('/mod/quiz/view.php', array('q' => $studyplan->quiz));
		$quiz_name = htmlentities(sp_get_quiz_name($studyplan->quiz));
		print "<h2 class=\"studyplan-header studyplan-no-quiz\">".
				get_string('youhavenotfinished', 'studyplan')." <a href=\"$url\">$quiz_name</a>.".
				"</h2>"; 
	} else {
		$lastfinishedattempt = end($attempts);
		$attemptobj = quiz_attempt::create($lastfinishedattempt->id);
		$questionids = sp_get_questionids_from_attempt($attemptobj);
		$presummary=sp_presummarize($attemptobj,$questionids,$showtabulation);
		echo sp_render_block($studyplan->id,$presummary,has_capability('mod/studyplan:assign', $context),false,$showtabulation);
	}
}
echo "</td>";
echo "</tr></table>";

// Finish the page
echo $OUTPUT->footer();
