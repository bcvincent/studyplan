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
// started from: https://github.com/moodlehq/moodle-mod_newmodule

/**
 * The main studyplan configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_studyplan
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once(dirname(__FILE__).'/locallib.php');

/**
 * Module instance settings form
 */
class mod_studyplan_mod_form extends moodleform_mod {
	var $current_block_number=0;
	var $fixed_lookup_type='tag';

	public function sp_capture_blocks() {
		global $DB;
		$t = time();
        $studyplan_id = $this->current->id;
        $blocks=$_POST['studyplanblocks'];
        foreach ($blocks as $block) {
        	$b=sp_utility_arrayToStdClass($block);
        	$b->studyplan=$studyplan_id;
	        if ($b->markfordeletion=='1') { 
	        	#marked for deletion -- delete if there is an associated id
		        if ($b->id!='') {
			        $DB->delete_records('studyplan_blocks', array('id' => $b->id));
			        $DB->delete_records('studyplan_overrides', array('block' => $b->id));
		        }
	        } elseif ($b->id!='') {
	        	#update if there is an id
	        	$b->timemodified=$t;
		        $DB->update_record('studyplan_blocks', $b);
	        } else {
	        	#create if there is no id
	        	$b->timecreated=$t;
		        $DB->insert_record('studyplan_blocks', $b);
	        }
        }
	}
	
	public function sp_block_row($b) {
    	global $COURSE;
        $mform = $this->_form;
        $studyplan_id = $this->current->id;
        $n=$this->current_block_number;
        if ($b===false) {
	        $b=new stdClass;
	        $b->markfordeletion=1;
        } else {
	        $b->markfordeletion=0;
        }
        
        $mform->addElement('html', '<tr class="studyplan-block">');
	        $mform->addElement('html', '<td>');
        		sp_hidden_field($mform,'studyplanblocks['.$n.'][markfordeletion]', $b->markfordeletion);
        		sp_hidden_field($mform,'studyplanblocks['.$n.'][id]', $b->id);
        		sp_hidden_field($mform,'studyplanblocks['.$n.'][studyplan]', $studyplan_id);
        		sp_hidden_field($mform,'studyplanblocks['.$n.'][sequence]', $n);
    			sp_select_menu_without_framing($mform,'studyplanblocks['.$n.'][type]','',$b->type,sp_get_types_hash());
	        $mform->addElement('html', '</td><td>');
	        	sp_text_input_without_framing($mform,'studyplanblocks['.$n.'][label]','',$b->label);
	        $mform->addElement('html', '</td><td>');
        		sp_select_menu_without_framing($mform,'studyplanblocks['.$n.'][lookuptype]','',$this->fixed_lookup_type,sp_get_lookuptypes_hash());
        		sp_text_input_without_framing($mform,'studyplanblocks['.$n.'][keyname]','',$b->keyname);
        		sp_select_menu_without_framing($mform,'studyplanblocks['.$n.'][operator]','',$b->operator,sp_get_operators_hash());
        		sp_text_input_without_framing($mform,'studyplanblocks['.$n.'][value]','',$b->value,'width:50px;');
	        $mform->addElement('html', '</td><td>');
        		sp_select_menu_without_framing($mform,'studyplanblocks['.$n.'][activity]','',$b->activity,sp_get_modules_hash_for_current_course(),'width:150px;');
	        $mform->addElement('html', '</td><td>');
        		sp_select_menu_without_framing($mform,'studyplanblocks['.$n.'][completionactivity]','',$b->completionactivity,sp_get_modules_hash_for_current_course(),'width:150px;');
	        $mform->addElement('html', '</td><td style="white-space:nowrap;">');
	        	if ($b->markfordeletion==1) {
		        	$mform->addElement('html', '<input type="button" class="studyplan-block-add-button" value="Add" data-counter="'.$n.'" onclick="sp_add_row(this)">');
		        	$mform->addElement('html', '<span class="studyplan-block-buttons" style="display:none;">');
	        	} else {
		        	$mform->addElement('html', '<span class="studyplan-block-buttons">');
	        	}
	        	$mform->addElement('html', '<a href="#" onclick="sp_move_row(this,-1);return false;">&#9650;</a>&nbsp;');
	        	$mform->addElement('html', '<a href="#" onclick="sp_move_row(this,1);return false;">&#9660;</a>&nbsp;');
	        	$mform->addElement('html', '&nbsp;<a href="#" onclick="sp_delete_row(this);return false;" style="font-size:150%;vertical-align:middle;">&#215;</a>');
	        	$mform->addElement('html', '</span>');
        $mform->addElement('html', '</td></tr>');
        $this->current_block_number++;
	}
	
	public function sp_header_row($as_type=true) {
        $mform = $this->_form;
        $type_or_new="New";
        if ($as_type) { $type_or_new="Type"; }
        if (!$as_type) {
	    	$mform->addElement('html', '<tr class="studyplan-header">
	    								<th style="text-align:left">&nbsp;</th>
	    								</tr>');
        }
	    $mform->addElement('html', '<tr class="studyplan-header">
	    							<th style="text-align:left">'.$type_or_new.'</th>
	    							<th style="text-align:left">
	    							    ' . get_string('label', 'studyplan') . '
	    							</th>
	    							<th style="text-align:left">
	    							    ' . get_string('evaluate', 'studyplan') . '
	    							</th>
	    							<th style="text-align:left">
	    							    ' . get_string('assignment', 'studyplan') . '
	    							</th>
	    							<th style="text-align:left">
	    							    ' . get_string('completion', 'studyplan') . '
	    							</th>
	    							<th style="text-align:left">&nbsp;</th>
	    							</tr>');
	}

	public function sp_block_javascript() {
		return '
		<script language="JavaScript">
			function sp_add_row(elem) {
				var e=Y.one(elem)
				var c=parseInt(e.getAttribute("data-counter"))
				var r=e.ancestor(".studyplan-block")
				var t=e.ancestor(".studyplan-blocks-table")
				var b=t.one(".studyplan-blocks-entries")
				var x=t.one(".studyplan-block-empty")
				if (x) { x.remove(true) }
				//clone the row before the second study plan header
				var r2 = r.cloneNode(true);
	            b.append(r2);
	            r2.one(".studyplan-block-add-button").remove(true)
	            r2.one(".studyplan-block-buttons").show()
				//change the markfordeletion
				r2.all("input[type=\"hidden\"]").each(function(node){
					var n=node.getAttribute("name")
					if ((n) && (n.substring(0,16)=="studyplanblocks[") && (n.indexOf("]")>-1)) {
						n=n.substring(n.indexOf("]")+1,n.length)
						if (n=="[markfordeletion]") {
							node.set("value",0)
						}
					}
				})
				//clone select settings from original row (which dont seem to be copied when cloning)
				r2.all("select").each(function(node){
					var n=node.getAttribute("name")
					node.set("value",r.one("select[name=\'"+n+"\']").get("value"))
				})
				//get ready to increment counters on the original row
				c++
				var s=null
				//change the names on the fields in this row
				r.all("input, select").each(function(node){
					var n=node.getAttribute("name")
					if ((n) && (n.substring(0,16)=="studyplanblocks[") && (n.indexOf("]")>-1)) {
						n=n.substring(n.indexOf("]")+1,n.length)
						if (n=="[sequence]") { s=node }
						n="studyplanblocks["+c+"]"+n
						node.setAttribute("name",n)
					}
				})
				//increment the counter on the button and the sequence number
				e.setAttribute("data-counter",c)
				if (s) { s.set("value",c) }
			}
			
			function sp_move_row(elem,offset) {
				var e=Y.one(elem)
				var r=e.ancestor(".studyplan-block")
				var t=e.ancestor(".studyplan-blocks-table")
				var b=t.one(".studyplan-blocks-entries")
				if (offset==0) { return }
				if (offset<0) { //move up
					var s=r.get("previousSibling")
					if (!(s)) { return }
					s.get("parentNode").insertBefore(r,s)
				} else { //move down
					var s=r.get("nextSibling")
					if (!(s)) { return }
					s.get("parentNode").insertBefore(s,r)
				}
				//force sequence numbers
				var c=0
				t.all("input[type=\"hidden\"]").each(function(node){
					var n=node.getAttribute("name")
					if ((n) && (n.substring(0,16)=="studyplanblocks[") && (n.indexOf("]")>-1)) {
						n=n.substring(n.indexOf("]")+1,n.length)
						if (n=="[sequence]") {
							node.set("value",c)
							c++
						}
					}
				})
			}
			
			function sp_delete_row(elem) {
				//input[name="assigned_user_id_advanced[]"]
				var e=Y.one(elem)
				var r=e.ancestor(".studyplan-block")
				var t=e.ancestor(".studyplan-blocks-table")
				var b=t.one(".studyplan-blocks-entries")
				var n=r.one("input").getAttribute("name")
				if ((n) && (n.substring(0,16)=="studyplanblocks[") && (n.indexOf("]")>-1)) {
					n=n.substring(0,n.indexOf("]")+1)
					n=n+"[markfordeletion]"
				} else {
					return
				}
				var md=r.one("input[name=\""+n+"\"]")
				if (!(md)) { return }
				if (md.get("value")=="1") {
					md.set("value","0")
					r.setAttribute("style","")
				} else {
					md.set("value","1")
					r.setAttribute("style","background-color:red;")
				}
			}
		</script>';
	}

	public function sp_block_editor() {
    	global $COURSE;
        $mform = $this->_form;
        $studyplan_id = $this->current->id;
        $blocks=sp_get_blocks($studyplan_id);
        
	    $mform->addElement('html', '<table class="studyplan-blocks-table">');
	    $this->sp_header_row();
	    
	    $mform->addElement('html', '<tbody class="studyplan-blocks-entries">');
        foreach ($blocks as $b) {
        	$this->sp_block_row($b);
        }
        if ($this->current_block_number==0) {
	        #no rows -- drop in an empty result
	        $mform->addElement('html', '<tr class="studyplan-block-empty" style="background-color:orange">');
	        $mform->addElement('html', '<td colspan="6">' . get_string('nostudyplanblocks', 'studyplan') . ' </td></tr>');
		       
        }
        $mform->addElement('html', '</tbody>');
        
	    $this->sp_header_row(false);
	    $mform->addElement('html', '<tbody class="studyplan-blocks-new">');
	    $this->sp_block_row(false);
        $mform->addElement('html', '</tbody>');
        
        #$this->sp_block_row(new stdClass);
	    $mform->addElement('html', '</table>');
	    $mform->addElement('html', $this->sp_block_javascript());
	}

    /**
     * Defines forms elements
     */
    public function definition() {
    	global $COURSE;

        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));
        
        $mform->addElement('select', 'quiz', 'Quiz', sp_get_quiz_hash_for_current_course());
        
        #print "<pre>";
        #print_r($this->current->id);
        #print "</pre>";

        // Adding the standard "name" field
        #$mform->addElement('text', 'name', get_string('studyplanname', 'studyplan'), array('size'=>'64'));
        #note: get_string('studyplanname', 'studyplan') does internationalization lookup
        $mform->addElement('text', 'name', get_string('studyplanname', 'studyplan'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'studyplanname', 'studyplan');

        // Adding the standard "intro" and "introformat" fields
        $this->add_intro_editor();
        
        # $mform->addElement('advcheckbox', 'standardblock', 'Standard block on study plan page', 'Include the standard details and description block on the study plan page', array('group' => 1), array(0, 1));
        $mform->addElement('advcheckbox', 'standardblock', get_string('standardblock', 'studyplan'), get_string('standardblockinstruction', 'studyplan'), array('group' => 1), array(0, 1));
        
        //-------------------------------------------------------------------------------
        // Adding the rest of studyplan settings, spreeading all them into this fieldset
        // or adding more fieldsets ('header' elements) if needed for better logic

        $mform->addElement('header', 'studyplancontent', 'Content');
        $this->sp_block_editor();

        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }
}
