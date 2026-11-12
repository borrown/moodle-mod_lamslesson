<?php
declare(strict_types=1);

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
 * The main lamslesson configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package   mod_lamslesson
 * @copyright 2011 LAMS Foundation - Ernie Ghiglione (ernieg@lamsfoundation.org) 
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GNU GPL v2
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_lamslesson_mod_form extends moodleform_mod {

    function definition() {
      global $COURSE, $USER, $CFG, $PAGE;

        $mform =& $this->_form;

        if (!empty($this->_instance)) {
	  $sequence_id = $this->current->sequence_id;
	  $currentsequence = get_string('currentsequence','lamslesson');
	  $updatewarning =  get_string('updatewarning','lamslesson');
	} else {
	  $sequence_id = 0;
	  $currentsequence = '';
	  $updatewarning = '';
	}

    //-------------------------------------------------------------------------------
    /// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

    /// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('lamslessonname', 'lamslesson'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_TEXT);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'lamslessonname', 'lamslesson');

    // Adding the standard "intro" and "introformat" fields
        $this->standard_intro_elements();

//-------------------------------------------------------------------------------
    /// Adding the rest of lamslesson settings, spreeading all them into this fieldset
    /// or adding more fieldsets ('header' elements) if needed for better logic

    // Set needed vars
		//$context = get_context_instance(CONTEXT_COURSE, $COURSE->id); 
		// using context_course now instead
	$context = context_course::instance($COURSE->id);
		
	$canmanage = has_capability('mod/lamslesson:manage', $context);

    //-- Open Author & Preview URL buttons

        $authorpreviewbutton = '';
        $customcsv = '';
        $lds = '[]';

	// Check whether this person has the right permissions to see the open button. 
	if ($canmanage) {

	  $customcsv = "$USER->username,$COURSE->id,$CFG->lamslesson_serverid";
	  $authorurl = lamslesson_get_url($USER->username, $USER->firstname, $USER->lastname, $USER->email, $USER->lang, $USER->country, 0, (int)$COURSE->id, $COURSE->fullname, (int)$COURSE->timecreated, LAMSLESSON_PARAM_AUTHOR_METHOD, $customcsv);

	  $openauthorlabel = get_string('openauthor', 'lamslesson');
	  $openpreviewlabel = get_string('previewthislesson', 'lamslesson');
	  $refreshlabel = get_string('refresh', 'lamslesson');

    	  // display user's lams workspace
    	  $lds = lamslesson_get_sequences_rest($USER->username, $USER->firstname, $USER->lastname, $USER->email, (int)$COURSE->id, $COURSE->fullname, (int)$COURSE->timecreated, $USER->country, $USER->lang) ;

	  // html "chunk" for open Author button 

    	$authorpreviewbutton .= html_writer::start_tag('div', array('id' => 'buttons', 'class' => 'lamslesson-buttons'));
        
        // Preview button
        $authorpreviewbutton .= html_writer::link('#', $openpreviewlabel, array('id' => 'previewbutton', 'style' => 'visibility:hidden;margin: 1em;', 'class' => 'btn btn-primary', 'data-action' => 'preview'));

        //Authoring button
        $authorpreviewbutton .= html_writer::link('#', $openauthorlabel, array('class' => 'btn btn-primary', 'style' => 'margin: 1em;', 'id' => 'authorbutton', 'data-action' => 'author', 'data-url' => $authorurl));

        //Refresh button
	$authorpreviewbutton .= html_writer::link('#', $refreshlabel, array('class' => 'btn btn-primary', 'style' => 'margin: 1em;', 'id' => 'refreshbutton', 'data-action' => 'refresh'));

        $authorpreviewbutton .= html_writer::end_tag('div');
	}

        // html "chunk" for the sequences tree. The data is read by the AMD module.
        $treecomponent = html_writer::tag('div', '', array(
            'id' => 'treeDiv',
            'data-region' => 'lamslesson-tree',
            'data-sequences' => $lds,
            'data-course' => (string)$COURSE->id,
            'data-preview-url' => $CFG->wwwroot.'/mod/lamslesson/preview.php?',
            'data-sequence-id' => (string)$sequence_id,
            'data-update-warning' => $updatewarning,
            'data-current-sequence' => $currentsequence,
        ));
        $treecomponent .= html_writer::tag('div', '', array('id' => 'updatesequence'));

        // Now we put the two html chunks together
        $html = $authorpreviewbutton . $treecomponent;

        $mform->addElement('hidden', 'sequence_id');
        $mform->setType('sequence_id', PARAM_INT);

        $mform->addElement('hidden', 'customCSV', $customcsv);
        $mform->setType('customCSV', PARAM_TEXT);

        $mform->addElement('header', 'selectsequence', get_string('selectsequence', 'lamslesson'));
		$mform->setExpanded('selectsequence', true);

        $mform->addElement('static', 'sequencemessage', '', $html);
	$mform->addElement('advcheckbox', 'displaydesign', get_string('displaydesign', 'lamslesson'),'',array(0,1));
	$mform->addHelpButton('displaydesign', 'displaydesign', 'lamslesson');
	$mform->addElement('advcheckbox', 'allowlearnerrestart', get_string('allowlearnerrestart', 'lamslesson'),'', array(0,1));
	$mform->addHelpButton('allowlearnerrestart', 'allowlearnerrestart', 'lamslesson');

		//-------------------------------------------------------------------------------
		$this->standard_grading_coursemodule_elements();

		// set the default grade to 'No Grade' so it doesn't record
		// anything on gradebook unless specifically set.
		$mform->setDefault('grade', 0);

        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
		//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();

        $PAGE->requires->js_call_amd('mod_lamslesson/preview', 'init');
    }
	
    function validation($data, $files) {
      $errors = array();
      // a sequence needs to be selected                                                                                   
      if (empty($data['sequence_id']) || $data['sequence_id'] <= 0) {
			$errors['sequencemessage'] = get_string('sequencenotselected', 'lamslesson');
      }
      return $errors;
    }

    function add_completion_rules() {
        $mform =& $this->_form;

        $mform->addElement('checkbox', 'completionfinish', '', get_string('completionfinish', 'lamslesson'));
        return array('completionfinish');
    }

    function completion_rule_enabled($data) {
        return !empty($data['completionfinish']);
    }

}
