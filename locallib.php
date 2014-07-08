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
 * Internal library of functions for module studyplan
 *
 * All the studyplan specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_studyplan
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Does something really useful with the passed things
 *
 * @param array $things
 * @return object
 */
//function studyplan_do_something_useful(array $things) {
//    return new stdClass();
//}

function sp_hidden_field($form,$name,$data) {
	$out='<input type="hidden" name="'.htmlentities("".$name).'" value="'.htmlentities("".$data).'">';
	$form->addElement('html',$out);
}

function sp_select_menu_without_framing($form,$name,$id,$data,$options,$style) {
	$out='<div class="felement fselect" style="display:inline-block;margin-right:5px;">';
	$out.="<select name=\"$name\" id=\"$id\" style=\"$style\" >";
	foreach ($options as $k=>$v) {
		$sel="";
		if (($data!==false) && ($data!==null) && ($k==$data)) { $sel=' selected'; }
		$out.='<option value="'.htmlentities($k).'" '.$sel.'>'.htmlentities($v).'</option>';
	}
	$out.='</select></div>';
	$form->addElement('html',$out);
}

function sp_text_input_without_framing($form,$name,$id,$data,$style) {
	$out='<div class="felement ftext" style="display:inline-block;margin-right:5px;">';
	$out.="<input type=\"text\" name=\"$name\" id=\"$id\" style=\"$style\" value=\"".htmlentities($data)."\">";
	$out.='</div>';
	$form->addElement('html',$out);
}

function sp_textarea_without_framing($form,$name,$id,$data) {
	$out.='<div class="fitem_feditor">';
	$out.='<div class="felement feditor" style="display:inline-block;margin-right:5px;">';
	$out.='<textarea rows="10" cols="80" spellcheck="true" class="collapsed collapsible" '."name=\"$name\" id=\"$id\">".htmlentities($data)."</textarea>";
	$out.='</div></div>';
	#<textarea id="id_introeditor" name="introeditor[text]" rows="10" cols="80" spellcheck="true" class="collapsed collapsible">&lt;p&gt;Shello!2&lt;/p&gt;</textarea>
	$form->addElement('html',$out);
}

function sp_get_full_operators_hash() {
	return array(
		'lt'	=>"<",
		'lte'	=>"<=",
		'gt'	=>">",
		'gte'	=>">=",
		'ltp'	=>"< %",
		'ltep'	=>"<= %",
		'gtp'	=>"> %",
		'gtep'	=>">= %"
	);
}

function sp_get_operators_hash() {
	return array(
		'ltp'	=>"< %",
		'ltep'	=>"<= %",
		'gtp'	=>"> %",
		'gtep'	=>">= %"
	);
}


function sp_get_types_hash() {
	return array(
		'1'	=> get_string('heading', 'studyplan'),
		'0'	=> get_string('evaluate', 'studyplan')
	);
}


function sp_get_full_lookuptypes_hash() {
	return array(
		'text'		              => get_string('text', 'studyplan'), // "Text",
		'summary'	              => get_string('summary', 'studyplan'), // "Summary",
		'mark'		              => get_string('mark', 'studyplan'), // "Mark",
		'category'	              => get_string('category', 'studyplan'), // "Category",
		'tag'		              => get_string('tag', 'studyplan'), // "Tag",
		'question'	              => get_string('question', 'studyplan'), // "Question",
		'questions_all'			  => get_string('allquestions', 'studyplan'), // "All Questions",
		'questions_correct'		  => get_string('correctquestions', 'studyplan'), // "Correct Questions",
		'questions_incorrect'	  => get_string('incorrectquestions', 'studyplan'), // "Incorrect Questions",
		'response'	              => get_string('response', 'studyplan'), //"Response"
	);
}


function sp_get_lookuptypes_hash() {
	return array(
		'tag'		              => get_string('tag', 'studyplan'), // "Tag"
	);
}

function sp_presummarize($attemptobj) {
	$presummary=array();
	$slots = $attemptobj->get_slots();
	foreach ($slots as $slot) {
		if ($attemptobj->is_real_question($slot)) {
			$tags=sp_get_tags_for_question($slot);
			$mark=$attemptobj->get_question_mark($slot);
			foreach ($tags as $tag) {
				$presummary["mark"]["tag"][$tag]+=$mark;
				$presummary["count"]["tagtotal"][$tag]++;
				if ($mark>0) {
					$presummary["count"]["tag"][$tag]++;
				}
			}
			#$s=$attemptobj->get_question_status($slot,true);
			#$category=
		}
	}
	#might also be useful: get_question_status, others in quiz/attemptlib.php l# 980+
	return $presummary;
}

function sp_render_block($studyplanid,$presummary,$assignbuttons=false){
	global $COURSE;
	$cms=get_fast_modinfo($COURSE)->get_cms();
	
	$out="";
	foreach (sp_get_blocks($studyplanid) as $block) {
		if ($block->type==STUDYPLAN_BLOCKS_TYPES_HEADER) {
			$out.='<h2 class="studyplan-header">'.htmlentities($block->label)."</h2>";
		} elseif ($block->type==STUDYPLAN_BLOCKS_TYPES_EVALUATE) {
			$mark = 0.0;
			$value = 0.0;
			$total = 0.0;
			$count = 0.0;
			$perc = 0.0;
			
			if ($block->lookuptype=="tag") {
				$value = floatval($block->value);
				$mark = 0.0;
				$total = 0.0;
				$count = 0.0;
				$full_keyname=strtolower($block->keyname);
				foreach (explode(',',$full_keyname) as $key_untrimmed) {
					$key=trim($key_untrimmed);
					$m = floatval($presummary["mark"]["tag"][$key]);
					$t = floatval($presummary["count"]["tagtotal"][$key]);
					$c = floatval($presummary["count"]["tag"][$key]);
					if ($m>$mark) { $mark=$m; }
					if ($t>$total) { $total=$t; }
					if ($c>$count) { $count=$c; }
				}
				
				if (($count==0) || ($total==0)) { 
					$perc = 0.0; 
				} else {
					$perc = 100.0*$count/$total;
				}
			}
				
			$style="";
			//assigned
			if (($block->operator=="ltp") && ($perc<$value)) {
				$style.="studyplan-block-assigned ";
			} else if (($block->operator=="ltep") && ($perc<=$value)) {
				$style.="studyplan-block-assigned ";
			} else if (($block->operator=="gtp") && ($perc>$value)) {
				$style.="studyplan-block-assigned ";
			} else if (($block->operator=="gtep") && ($perc>=$value)) {
				$style.="studyplan-block-assigned ";
			} else if (($block->operator=="lt") && ($mark<$value)) {
				$style.="studyplan-block-assigned ";
			} else if (($block->operator=="lte") && ($mark<=$value)) {
				$style.="studyplan-block-assigned ";
			} else if (($block->operator=="gt") && ($mark>$value)) {
				$style.="studyplan-block-assigned ";
			} else if (($block->operator=="gte") && ($mark>=$value)) {
				$style.="studyplan-block-assigned ";
			}
			//teacher-assigned
			$assign_label="Assign";
			$assigned_data="0";
			if (sp_get_block_assigned($block->id,$studyplanid)) {
				$style.="studyplan-block-teacher-assigned ";
				$assign_label="Remove";
				$assigned_data="1";
			}
			//completed
			if (sp_get_activity_completed($block->completionactivity)) {
				$style.="studyplan-block-completed ";
			}
			$url = $cms[$block->activity]->get_url()->out();
			$out.='<div class="studyplan-block '.$style.'">';
			if ($assignbuttons) {
				$out.='<input type="button" value="'.$assign_label.'" class="studyplan-assign-button" 
					data-blockid="'.$block->id.'" data-assigned="'.$assigned_data.'" 
					onclick="studyplanAssign(this)" >';
				$out.="\n";
			}
			$out.='<a href="'.htmlentities($url).'">'.htmlentities($block->label).'</a>';
			$out.='</div>';
		}
	}
	
	return $out;
}

function sp_render_legend() {
	return '
		<table class="studyplan-legend">
			<tr>
				<td><div class="studyplan-block studyplan-example">
				    ' . get_string('completed', 'studyplan') . '
				</div></td>
				<td><div class="studyplan-block studyplan-block-assigned studyplan-example">
				    ' . get_string('assignedbytest', 'studyplan') . '
				</div></td>
				<td><div class="studyplan-block studyplan-block-teacher-assigned studyplan-example">
				    ' . get_string('assignedbyteacher', 'studyplan') . '
				</div></td>
			</tr>
		</table>
	';
}


function sp_get_tags_for_question($qid) {
	global $DB;
	$out=array();
	$conditions=array();
	$conditions['itemid']=$qid;
	$conditions['itemtype']='question';
	$results=$DB->get_records('tag_instance',$conditions,null,'tagid');
	if ($results!==false) {
		foreach ($results as $r) { array_push($out,$r->tagid); }
	}
	if (count($out)>0) {
		$conditions_joined=join(',',$out);
		$out=array();
		$sql="SELECT name FROM {tag} WHERE id in ( $conditions_joined )";
		$results = $DB->get_records_sql($sql);
		if ($results!==false) {
			foreach ($results as $r) { array_push($out,$r->name); }
		}
	}
	return $out;
}


function sp_get_blocks($studyplan) {
	global $DB;
	$out=array();
	$conditions['studyplan']=$studyplan;
	$results=$DB->get_records('studyplan_blocks',$conditions,'sequence,id');
	if ($results!==false) {
		foreach ($results as $r) { $out[]=$r; }
	}
	return $out;
}

function sp_get_block_assigned($blockid,$studyplanid) {
	global $DB, $USER, $STUDENT;
	$user_id=$USER->id;
	if (isset($STUDENT)) { $user_id=$STUDENT->id; }
	$out=array();
	$conditions['block']=$blockid;
	$conditions['studyplan']=$studyplanid;
	$conditions['user']=$user_id;
	$results=$DB->get_record('studyplan_overrides',$conditions);
	if ($results!==false) {
		return true;
	}
	return false;
}

function sp_toggle_block_assigned($blockid,$studyplanid,$user_id) {
	global $DB;
	$out=array();
	$conditions['block']=$blockid;
	$conditions['studyplan']=$studyplanid;
	$conditions['user']=$user_id;
	$results=$DB->get_record('studyplan_overrides',$conditions);
	if ($results!==false) {
		#delete it
		$DB->delete_records('studyplan_overrides', $conditions);
	} else {
		#create it
		$conditions['timecreated'] = time();
		$DB->insert_record('studyplan_overrides', sp_utility_arrayToStdClass($conditions));
	}
}

function sp_get_completionactivity_ids($studyplanid) {
	$out=array();
	$already=array();
	foreach (sp_get_blocks($studyplanid) as $block) {
		if ($already['x'.$block->completionactivity]!=1) {
			array_push($out,$block->completionactivity);
		}
		$already['x'.$block->completionactivity]=1;
	}
	return $out;
}

function sp_get_completionactivity_modnames($studyplanid) {
	global $COURSE;
	$cms=get_fast_modinfo($COURSE)->get_cms();
	$out=array();
	$already=array();
	foreach (sp_get_completionactivity_ids($studyplanid) as $caid) {
		if ($already[$cms[$caid]->modname]!=1) {
			array_push($out,$cms[$caid]->modname);
		}
		$already[$cms[$caid]->modname]=1;
	}
	return $out;
}

function sp_get_activity_completed($activityid=0) {
	global $DB, $COURSE, $USER, $STUDENT;
	$user_id=$USER->id;
	if (isset($STUDENT)) { $user_id=$STUDENT->id; }
	if ($activityid<=0) { return false; }
	$cms=get_fast_modinfo($COURSE)->get_cms();
	$mod=$cms[$activityid];
	$completion = new completion_info($COURSE);
	if ($mod->modname=="quiz") {
		$attempts = quiz_get_user_attempts($studyplan->quiz, $user_id, 'finished', true);
		if (empty($attempts)) { return false; }
		return true;
	} else {
		#http://docs.moodle.org/dev/Course_completion & http://docs.moodle.org/dev/Activity_completion_API
		#lib/completionlib.php
		$comp_data=$completion->get_data($mod, $user_id);
		if (empty($comp_data)) { return false; }
		if ($comp_data->completionstate>=COMPLETION_COMPLETE) { return true; }
	}
	return false;
}

function sp_get_quiz_name($quizid=0,$missing_name="quiz") {
	global $COURSE, $DB;
	if (($quizid==null) || ($quizid==0)) { return $missing_name; }
	$out=array();
	$conditions['id']=$quizid;
	$result=$DB->get_record('quiz',$conditions);
	if ($result!==false) {
		return $result->name;
	}
	return $missing_name;
}

function sp_get_quiz_hash_for_current_course() {
	global $COURSE, $DB;
	$out=array();
	$conditions['course']=$COURSE->id;
	$results=$DB->get_records('quiz',$conditions,null,'id,name');
	if ($results!==false) {
		foreach ($results as $r) { $out[$r->id]=$r->name; }
	}
	return $out;
}

function sp_get_modules_hash_for_current_course() {
	global $COURSE;
	$out=array();
	$cms=get_fast_modinfo($COURSE)->get_cms();
	foreach ($cms as $m) { $out[$m->id]=$m->name; }
	return $out;
}

function sp_utility_arrayToStdClass($d) {
	if (is_array($d)) {
	/*
	* Return array converted to object
	* Using __FUNCTION__ (Magic constant)
	* for recursive call
	*/
	return (object) array_map(__FUNCTION__, $d);
	}
	else {
	// Return object
	return $d;
	}
}

function sp_utility_nestedArrayCollapse($the_array) {
	$out=array();
	foreach ($the_array as $a) {
		if (is_array($a)) {
			$out=array_merge($out,sp_utility_nestedArrayCollapse($a));
		} else {
			$out[]=$a;
		}
	}
	return $out;
}
