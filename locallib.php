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

function sp_student_progress_as_decimal() {
	return (sp_student_progress_as_percentage()/100);
}

function sp_student_progress_as_percentage_text($precision=0) {
	return number_format(sp_student_progress_as_percentage(),$precision).'%';
}

function sp_student_progress_as_percentage() {
	global $STUDENT_PROGRESS;
	if ($STUDENT_PROGRESS===null) { return 0.0; }
	if (count($STUDENT_PROGRESS)==0) { return 0.0; }
	return ( array_sum($STUDENT_PROGRESS) / count($STUDENT_PROGRESS) ) * 100;
}

function sp_presummarize($attemptobj,$questionids,$showtabulation=0) {
	$presummary=array();
	if ($showtabulation) {
		$presummary['tabulation']=array();
		$presummary['tabulation']['questions']=array();
		$presummary['tabulation']['tags']=array();
	}
	$slots = $attemptobj->get_slots();
	foreach ($slots as $slot) {
		if ($attemptobj->is_real_question($slot)) {
			$qid=sp_get_questionid_for_slot($questionids,$slot);
			$tags=sp_get_tags_for_question($qid);
			$mark=$attemptobj->get_question_mark($slot);
			foreach ($tags as $tag) {
				@$presummary["mark"]["tag"][$tag]+=$mark;
				@$presummary["count"]["tagtotal"][$tag]++;
				@$presummary["quest"]["tag"][$tag][]=$qid; //b
				@$presummary["tag"][$tag]["mark"][]=$mark; 
				@$presummary["tag"][$tag]["quest"][]=$qid; 

				if ($mark>0) {
					@$presummary["count"]["tag"][$tag]++;
				}
				if ($showtabulation) {
					if (!$presummary['tabulation']['tags'][$tag]) {
						@$presummary['tabulation']['tags'][$tag]=array();
					}
					@$presummary['tabulation']['tags'][$tag]['name']=$tag;
					@$presummary['tabulation']['tags'][$tag]['count']=$presummary["count"]["tagtotal"][$tag];
					@$presummary['tabulation']['tags'][$tag]['questionids'].=" $qid";
				}
				
			}
			if ($showtabulation) {
				$question_data=array();
				$question_data['id']=$qid;
				$question_data['name']=sp_get_name_for_question($qid);
				$question_data['mark']=floatval($mark);
				$question_data['tags']=implode(',',$tags);
				@$presummary['tabulation']['questions'][]=$question_data;
			}
		}
	}
	#might also be useful: get_question_status, others in mod/quiz/attemptlib.php l# 980+
	return $presummary;
}

function sp_render_presummary_tabulation($presummary){
	$out="";
	$out.='<h2 class="tabulation-header">Quiz Results</h2>';
	$out.='<table class="tabulation-table">';
		$out.='<tr>
			<th>id</th>
			<th>name</th>
			<th>mark</th>
			<th>tags</th>
			</tr>'."\n";
	foreach ($presummary['tabulation']['questions'] as $row) {
		$out.='<tr>';
		$out.='<td>'.htmlentities($row['id']).'</td>';
		$out.='<td>'.htmlentities($row['name']).'</td>';
		$out.='<td>'.htmlentities($row['mark']).'</td>';
		$out.='<td>'.htmlentities($row['tags']).'</td>';
		$out.='</tr>'."\n";
	}
	$out.='</table>'."\n";
	
	$out.='<h2 class="tabulation-header">Tag Counts</h2>';
	$out.='<table class="tabulation-table">';
		$out.='<tr>
			<th>name</th>
			<th>count</th>
			<th>questionids</th>
			</tr>'."\n";
	foreach ($presummary['tabulation']['tags'] as $tag=>$row) {
		$out.='<tr>';
		$out.='<td>'.htmlentities($row['name']).'</td>';
		$out.='<td>'.htmlentities($row['count']).'</td>';
		$out.='<td>'.htmlentities($row['questionids']).'</td>';
		$out.='</tr>'."\n";
	}
	$out.='</table>'."\n";
	return $out;
}

function sp_render_block_tabulation($block) {
	$out="";
	$operators=sp_get_full_operators_hash();
	$out.='<table class="tabulation-table">';
	$out.='<tr>
		<th>assigned</th>
		<th>type</th>
		<th>value</th>
		<th>operator</th>
		<th>compareto</th>
		<th>comp</th>
		<th>results</th>
		<th>tags (keyname)</th>
		</tr>'."\n";
		
	$assigned="NO";
	$assigned_style="tabulation-show-not-assigned";
	if (stristr($block->style, 'studyplan-block-assigned')) {
		$assigned="YES";
		$assigned_style="tabulation-show-assigned";
	}
	$out.='<tr>';
	$out.='<td><span class="'.$assigned_style.'">'.htmlentities($assigned).'</span></td>';
	$out.='<td>'.htmlentities($block->lookuptype).'</td>';
	$out.='<td>'.htmlentities($block->value).'</td>';
	$out.='<td>'.htmlentities($operators[$block->operator]).'</td>';
	$out.='<td>'.htmlentities($block->compareto).'</td>';
	$out.='<td>'.htmlentities($block->comparisonvalue).'</td>';
	$out.='<td style="white-space: nowrap;">'.implode("<br>\n",explode(" ",$block->style)).'</td>';
	$out.='<td style="white-space: nowrap;">'.implode(",<br>\n",explode(",",$block->keyname)).'</td>';
	$out.='</tr>'."\n";
	$out.='</table>'."\n";
	return $out;
}

function sp_render_evaluations_tabulation($evaluations,$block) {
	$out="";
	$out.='<table class="tabulation-table">';
	$out.='<tr>
		<th>tag evaluations</th>
		<th>mark</th>
		<th>total</th>
		<th>perc</th>
		</tr>'."\n";
	foreach ($evaluations['tags'] as $tag=>$row) {
		$out.='<tr>';
		$out.='<td>'.htmlentities($row['tag']).'</td>';
		$out.='<td>'.htmlentities($row['mark']).'</td>';
		$out.='<td>'.htmlentities($row['total']).'</td>';
		$out.='<td>'.htmlentities($row['perc']).'</td>';
		$out.='</tr>'."\n";
	}
	$out.='</table>'."\n";
	return $out;
}

function sp_render_block($studyplanid,$presummary,$assignbuttons=false,$skipoutput=false,$showtabulation=0,$userr){
	global $COURSE;
	global $STUDENT_PROGRESS;
	if ($STUDENT_PROGRESS===null) { $STUDENT_PROGRESS = array(); }
	

		
	
	if ($skipoutput) {
		$cms=null;
	} else {
		$cms=get_fast_modinfo($COURSE)->get_cms();
	}
	
	$out="";
	
	if (($showtabulation) && ($presummary['tabulation'])) {
		$out.=sp_render_presummary_tabulation($presummary);
	}
	
	foreach (sp_get_blocks($studyplanid) as $block) {
		if ($block->type==STUDYPLAN_BLOCKS_TYPES_HEADER) {
			$out.='<h2 class="studyplan-header">'.htmlentities($block->label)."</h2>";
		} elseif ($block->type==STUDYPLAN_BLOCKS_TYPES_EVALUATE) {
			$STUDENT_PROGRESS[] = 0;
			$mark = 0.0;
			$value = 0.0;
			$total = 0.0;
			$count = 0.0;
			$perc = 0.0;
			$evaluations=array();
			$evaluations['tags']=array();
					
			//logic by lookuptype
			if ($block->lookuptype=="tag") {
				$value = floatval($block->value);
				$mark = 0.0;
				$total = 0.0;
				$count = 0.0;
				$usedqs = array(); //bcv
				$full_keyname=strtolower($block->keyname);
				foreach (array_filter(explode(',',$full_keyname)) as $key_untrimmed) {
					$key=trim($key_untrimmed);
					if ($key!="") {
						$m = 0;
						$t = 0;
							
							for ($i = 0; $i < count(@$presummary["tag"][$key]["quest"]); $i++) {
								if (!in_array($presummary["tag"][$key]["quest"][$i], $usedqs)) {
									$usedqs[] = $presummary["tag"][$key]["quest"][$i];
									$m += floatval($presummary["tag"][$key]["mark"][$i]);
									$t += 1;								}
								else{
								}

							}
					
						$c = floatval(@$presummary["count"]["tag"][$key]);
						$mark+=$m;
						$total+=$t;
						$count+=$c;
											
						$evaluations['tags'][$key]["tag"]=$key;
						$evaluations['tags'][$key]["mark"]=$mark;
						$evaluations['tags'][$key]["total"]=$total;
						$evaluations['tags'][$key]["count"]=$count;
						if (($mark==0) || ($total==0)) { 
							$evaluations['tags'][$key]["perc"] = 0.0; 
						} else {
							$evaluations['tags'][$key]["perc"] = 100.0*$mark/$total;
						}
					}
				}
				
				if (($mark==0) || ($total==0)) { 
					$perc = 0.0; 
				} else {
					$perc = 100.0*$mark/$total;
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
			$block->compareto="mark";
			$block->comparisonvalue=$mark;
			if (substr($block->operator,-1)=="p") { 
				$block->compareto="percent"; 
				$block->comparisonvalue=$perc;
			}
			
			if($userr=="student"){
				
				//teacher-assigned
				$assign_label="Assign";
				$assigned_data="0";
				if (sp_get_block_assigned($block->id,$studyplanid)) {
					$style.="studyplan-block-teacher-assigned ";
					$assign_label="Remove";
					$assigned_data="1";
				}
				
				//completed	
				else if (sp_get_activity_completed($block->completionactivity)) {
					$style.="studyplan-block-completed ";
				}
				
				$STUDENT_PROGRESS[ count($STUDENT_PROGRESS)-1 ] = 1;
				
				if ( ( $style != '' ) && ( !stristr($style, 'studyplan-block-completed')) ) {
					// this counts as not completed because it isn't flagged as studyplan-block-completed
					$STUDENT_PROGRESS[ count($STUDENT_PROGRESS)-1 ]=0;
				}
				if ( ( $style != '' ) && ( stristr($style, 'studyplan-block-teacher-assigned')) ) {
					// this counts as not completed because the teacher is forcing the assignment
					$STUDENT_PROGRESS[ count($STUDENT_PROGRESS)-1 ]=0;
				}	
				
				
				if ( ( !stristr($style, 'assigned')) ) { 
					$style.="studyplan-block-checked ";
				}
			}
			else if ($userr=="teacher"){
				
				//completed	
				if (sp_get_activity_completed($block->completionactivity)) {
					//$style.="studyplan-block-completed ";
					$style.="studyplan-block-checked ";

				}
				
				//teacher-assigned
				$assign_label="Assign";
				$assigned_data="0";
				if (sp_get_block_assigned($block->id,$studyplanid)) {
					$style.="studyplan-block-teacher-assigned ";				
					$assign_label="Remove";
					$assigned_data="1";
				}
				
				
				if ( ( $style != '' ) && (( stristr($style, 'studyplan-block-checked')) &&  ( !stristr($style, 'studyplan-block-teacher-assigned')))) {
					$STUDENT_PROGRESS[ count($STUDENT_PROGRESS)-1 ]=1;
				}
			}
			else{
			}
						
			$block->style=$style;
			$evaluations['style']=$style;
			
			$url="";
			if ($cms) { 
				$url = $cms[$block->activity]->url->out(); 
			
			}
			$out.='<div class="studyplan-block '.$style.'">';
			if ($assignbuttons) {
				$out.='<input type="button" value="'.$assign_label.'" class="studyplan-assign-button" 
					data-blockid="'.$block->id.'" data-assigned="'.$assigned_data.'" 
					onclick="studyplanAssign(this)" >';
				$out.="\n";
			}
			$out.='<a href="'.htmlentities($url).'">'.htmlentities($block->label).'</a>';
	
			if ((!$skipoutput) && ($showtabulation)) {
				$out.="Percentage of questions in this section correct: ".$perc." ";
				$out.=sp_render_block_tabulation($block);
				$out.=sp_render_evaluations_tabulation($evaluations,$block);
			}
			
			$out.='</div>';
		}
	}
	
	if ($skipoutput) { return; }
	sp_store_student_progress($studyplanid);
	//return sp_render_progress_header().sp_render_legend().$out;
	return sp_render_legend($userr).$out;
}

function sp_calculate_student_progress($studyplanid=null,$userid=null,$store=true,$gettext=true) {
	global $USER, $STUDENT, $DB;
	global $STUDENT_PROGRESS;
	if ($studyplanid===null) { return; }
	if ($userid===null) { return; }
	if ($userid!=$STUDENT->id) {
		$STUDENT  = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
	}
	$STUDENT_PROGRESS = array();
	
	$studyplan  = $DB->get_record('studyplan', array('id' => $studyplanid), '*', MUST_EXIST);
	$attempts = quiz_get_user_attempts($studyplan->quiz, $userid, 'finished', true);
	if (!empty($attempts)) { 
		$lastfinishedattempt = end($attempts);
		$attemptobj = quiz_attempt::create($lastfinishedattempt->id);
		$questionids = sp_get_questionids_from_attempt($attemptobj);
		$presummary=sp_presummarize($attemptobj,$questionids);
		#calculate percent by rendering the block
		sp_render_block($studyplan->id,$presummary,false,true);
	}
	if ($store) { 
		sp_store_student_progress($studyplanid,$userid);
	}
	if (!$gettext) { return; }
	return sp_student_progress_as_percentage_text();
}

function sp_store_student_progress($studyplanid=null,$userid=null,$percent=null) {
	global $USER, $STUDENT, $DB;
	if ($studyplanid===null) { return; }
	if ($userid===null) {
		if (isset($USER)) { $userid=$USER->id; }
		if (isset($STUDENT)) { $userid=$STUDENT->id; }
		if ($userid===null) { return; }
	}
	if ($percent===null) {
		$percent=sp_student_progress_as_percentage();
		if ($percent===null) { return; }
	}
	
	$now=time();
	$progress=$DB->get_record('studyplan_progress',array('studyplan'=>$studyplanid,'user'=>$userid));
	if ($progress) {
		#only store if the percentage has actually changed
		if (floatval($progress->percent)!==floatval($percent)) {
			$progress->percent=$percent;
			$progress->timemodified=$now;
			$DB->update_record('studyplan_progress', $progress);
		}
	} else {
		$progress=new stdClass();
		$progress->studyplan=$studyplanid;
		$progress->user=$userid;
		$progress->percent=$percent;
		$progress->timecreated=$now;
		$progress->timemodified=$now;
		$DB->insert_record('studyplan_progress', $progress);
	}
}

function sp_render_progress_header() {
	return '
		<h2 class="studyplan-progress-header">'.
			get_string('progressheaderprefix', 'studyplan').'
			<span class="studyplan-progress-percent">' . sp_student_progress_as_percentage_text() . '</span> '.
			get_string('progressheadersuffix', 'studyplan').'
		</h2>
		';
}

function sp_render_legend($userr) {
	if($userr=="teacher"){
		return '
			<table class="studyplan-legend">
				<tr>
					<td><div class="studyplan-block studyplan-block-completed studyplan-example">
						' . get_string('completedS', 'studyplan') . '
					</div></td>
					<td><div class="studyplan-block studyplan-block-unassigned studyplan-example">
						' . get_string('unassigned', 'studyplan') . '
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
	
	else{
		return '
		<table class="studyplan-legend">
			<tr>
				<td><div class="studyplan-block studyplan-block-completed studyplan-example">
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
	
}

function sp_get_questionids_from_attempt($attemptobj) {
	/*# based on mod/quiz/attemptlib.php - line 91
	$questionids=quiz_questions_in_quiz($attemptobj->get_quiz()->questions);
	if ($questionids) { 
		$questionids = explode(',', $questionids);
	} else {
		$questionids = array(); 
	}
	return $questionids;*/
	
	global $DB;
	
	$questionids = array();
	$temp_slots = $attemptobj->get_quiz()->id;
	$sql="SELECT questionid FROM {quiz_slots} WHERE quizid = $temp_slots";
	$results = $DB->get_records_sql($sql);

	if ($results!==false) {
		foreach ($results as $r) { array_push($questionids,$r->questionid); }
	}
	else{}
	
	return $questionids;
}

function sp_get_questionid_for_slot($questions,$slot) {
	//based on mod/quiz/attemptlib.php - line 91
	$offset_slot=intval($slot)-1;
	if ($offset_slot<0) { return 0; }
	if ($offset_slot>=count($questions)) { return 0; }
	return $questions[$offset_slot];
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

function sp_get_name_for_question($qid) {
	global $DB;
	$sql="SELECT name FROM {question} WHERE id = $qid";
	$result = $DB->get_record_sql($sql);
	if ($result!==false) {
		return $result->name;
	}
	return "";
}

function sp_get_names_for_questions($qids) {
	global $DB;
	$out=array();
	$conditions_joined=join(',',$qids);
	$sql="SELECT name FROM {question} WHERE id in ( $conditions_joined )";
	$results = $DB->get_records_sql($sql);
	if ($results!==false) {
		foreach ($results as $r) { array_push($out,$r->name); }
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

    // echo "<br />COMPLETION: " . print_r($mod->modname, true);

	if ($mod->modname=="quiz") {
		$attempts = quiz_get_user_attempts($studyplan->quiz, $user_id, 'finished', true);
		if (empty($attempts)) { return false; }
		return true;
	} else {
		#http://docs.moodle.org/dev/Course_completion & http://docs.moodle.org/dev/Activity_completion_API
		#lib/completionlib.php - line # 907 - get_data
		$comp_data=$completion->get_data($mod, false, $user_id);	
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
