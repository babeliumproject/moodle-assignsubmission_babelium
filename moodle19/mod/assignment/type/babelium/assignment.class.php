<?php

/**
 * Babelium Project open source collaborative second language oral practice - http://www.babeliumproject.com
 *
 * Copyright (c) 2012 GHyM and by respective authors (see below).
 *
 * This file is part of Babelium Project.
 *
 * Babelium Project is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Babelium Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/mod/assignment/lib.php');


require_once($CFG->dirroot . '/mod/assignment/type/babelium/babelium_widget.php');

/**
 * Extend the base assignment class for Babelium assignments
 */
class assignment_babelium extends assignment_base {

	var $filearea = 'submission';

	function assignment_babelium($cmid='staticonly', $assignment=NULL, $cm=NULL, $course=NULL) {
		parent::assignment_base($cmid, $assignment, $cm, $course);
		$this->type = 'babelium';
		
		//Parse the settings file if the filter plugin is not available
		babelium_parse_settings();
	}

	/**
	 * Displays the assignment in its various states
	 */
	function view() {
		global $USER;

		/* Script migration to Moodle's best-practices
		$jsConfig = array(
			'name' => 'assignment_babelium',
			'fullpath' => '/mod/assignment/type/babelium/assignment.js'
		);
		$PAGE->requires->js_init_call('M.assignment_babelium.init',null,false,$jsConfig);
		*/
		$edit  = optional_param('edit', 0, PARAM_BOOL);
		$saved = optional_param('saved', 0, PARAM_BOOL);

		$context = get_context_instance(CONTEXT_MODULE, $this->cm->id);
		require_capability('mod/assignment:view', $context);

		$submission = $this->get_submission();
		//Guest can not submit nor edit an assignment (bug: 4604)
		if (!has_capability('mod/assignment:submit', $context)) {
			$editable = null;
		} else {
			$editable = $this->isopen() && (!$submission || $this->assignment->resubmit || !$submission->timemarked);
		}
		$editmode = ($editable and $edit);
		if ($editmode) {
			//guest can not edit or submit assignment
			if (!has_capability('mod/assignment:submit', $context)) {
				print_error('guestnosubmit', 'assignment');
			}
		}

		add_to_log($this->course->id, "assignment", "view", "view.php?id={$this->cm->id}", $this->assignment->id, $this->cm->id);

		$defaults = new object();
		$defaults->id = $this->cm->id;
		if (!empty($submission)) {

			$defaults->data1 = $submission->data1;
			$defaults->data2 = $submission->data2;
		}
		$exercise_data = !empty($defaults->data2) ? babelium_get_response_data($defaults->data1) : babelium_get_exercise_data($this->assignment->var1);
		if(!$exercise_data)
			error("Error while retrieving Babelium external data");

		// prepare form and process submitted data
		//$mform = new mod_assignment_babelium_edit_form(null, array());
		$mform = new mod_assignment_babelium_edit_form(null, array($defaults, $exercise_data['info'], $exercise_data['roles'], $exercise_data['languages'], $exercise_data['subtitles']));

		//$mform->set_data($defaults);

		if ($mform->is_cancelled()) {
			redirect('view.php?id='.$this->cm->id);
		}

		if ($data = $mform->get_data()) {      // No incoming data?
			if ($editable && $this->update_submission($data)) {
				//TODO fix log actions - needs db upgrade
				$submission = $this->get_submission();
				add_to_log($this->course->id, 'assignment', 'babelium', 'view.php?a='.$this->assignment->id, $this->assignment->id, $this->cm->id);
				$this->email_teachers($submission);

				//redirect to get updated submission date and word count
				redirect('view.php?id='.$this->cm->id.'&saved=1');
			} else {
				// TODO: add better error message
				notify(get_string("error")); //submitting not allowed!
			}
		}

		/// print header, etc. and display form if needed
		if ($editmode) {
			$this->view_header(get_string('editmysubmission', 'assignment'));
		} else {
			$this->view_header(get_string('viewsubmissions', 'assignment'));
		}

		//This displays the name of the assignment
		$this->view_intro();

		//This displays the dates of the assignment: available, due, last edition
		$this->view_dates();

		//If something's been saved this moment display a notification
		if ($saved) {
			notify(get_string('submissionsaved', 'assignment'), 'notifysuccess');
		}

		if ($editmode) {
			print_box_start('generalbox', 'babeliumenter');
			$mform->display();
			print_box_end();
		} else {
			print_box_start('generalbox boxwidthwide boxaligncenter', 'babeliumenter');
			if ($submission) {
				$html_content = '';
				$response_data = babelium_get_response_data($submission->data1);
				if($response_data)
    					$html_content = babelium_html_output(1,$response_data['info'],$response_data['subtitles']);
				echo $html_content;
			} else if (!has_capability('mod/assignment:submit', $context)) { //fix for #4604
				if (isguest()) {
					echo '<div style="text-align:center">'. get_string('guestnosubmit', 'assignment').'</div>';
				} else {
					echo '<div style="text-align:center">'. get_string('usernosubmit', 'assignment').'</div>';
				}
			} else if ($this->isopen()){    //fix for #4206
				echo '<div style="text-align:center">'.get_string('emptysubmission', 'assignment').'</div>';
			}
			print_box_end();
			if ($editable) {
				echo "<div style='text-align:center'>";
				print_single_button('view.php', array('id'=>$this->cm->id,'edit'=>'1'), get_string('editmysubmission', 'assignment'));
				echo "</div>";
			}

		}

		$this->view_feedback();

		$this->view_footer();
	}

	/**
	 * Display the assignment dates
	 */
	function view_dates() {
		global $USER, $CFG;

		if (!$this->assignment->timeavailable && !$this->assignment->timedue) {
			return;
		}

		print_simple_box_start('center', '', '', 0, 'generalbox', 'dates');
		echo '<table>';
		if ($this->assignment->timeavailable) {
			echo '<tr><td class="c0">'.get_string('availabledate','assignment').':</td>';
			echo '    <td class="c1">'.userdate($this->assignment->timeavailable).'</td></tr>';
		}
		if ($this->assignment->timedue) {
			echo '<tr><td class="c0">'.get_string('duedate','assignment').':</td>';
			echo '    <td class="c1">'.userdate($this->assignment->timedue).'</td></tr>';
		}
		$submission = $this->get_submission($USER->id);
		if ($submission) {
			echo '<tr><td class="c0">'.get_string('lastedited').':</td>';
			echo '    <td class="c1">'.userdate($submission->timemodified);
			//TODO replace the word counting code with something that tells us how many submission attempts have been made
		}
		echo '</table>';
		print_simple_box_end();
	}

	/**
	 * Inserts/updates the submitted data in the database
	 * 
	 * @param mixed $data
	 * 		Contains the data of the edit form
	 * @return mixed $submission
	 * 		
	 */
	function update_submission($data) {
		global $CFG, $USER;

		$submission = $this->get_submission($USER->id, true);

		//Put in this object the fields of the database you wish to change
		$update = new object();
		$update->id           = $submission->id;
		$update->data1        = $data->data1; //The id of the response
		$update->data2        = $data->data2; //The hash of the response
		$update->timemodified = time();

		//The user recorded a new response and wants to keep the new version
		if($submission->data2 != $data->data2){
			$responsedata = babelium_save_response_data($this->assignment->var1, $data->exerciseDuration, $data->subtitleId, $data->recordedRole, $data->data2);
			if(!$responsedata)
				throw new moodle_exception('babeliumErrorSavingResponse','assignment_babelium'); 
			$update->data1 = $responsedata['responseId'];
			//$update->data2 = $responsedata['responseThumbnail'];
		} else {
			$update->data1 = $submission->data1;
		}

		if (!update_record('assignment_submissions', $update)) { return false; }

		$submission = $this->get_submission($USER->id);
		$this->update_grade($submission);
		return true;
	}

	/**
	 * Displays an overview of the submission of a certain user
	 * @param unknown_type $userid
	 * @param unknown_type $return
	 */
	function print_student_answer($userid, $return=false){
		global $CFG;
		if (!$submission = $this->get_submission($userid)) {
			return '';
		}

		$link = '/mod/assignment/type/babelium/file.php?id='.$this->cm->id.'&amp;userid='.$submission->userid;
		$action = 'file'.$userid;
		$image_tag = '<img src="http://'.$CFG->filter_babelium_serverdomain.'/resources/images/thumbs/'.$submission->data2.'/default.jpg" alt="submission" border="0" height="45" width="60"/>';
		$popup = link_to_popup_window ($link, $action, $image_tag, 500, 680, get_string('submission', 'assignment'), 'none', true);

		$output = '<div class="files">'.
			  $popup .
			  '</div>';
		return $output;
	}

	/**
	 * Displays full info about the submission of a certain user
	 * @param unknown_type $userid
	 * @param unknown_type $return
	 */
	function print_user_files($userid, $return=false) {
		global $CFG;
		
		if (!$submission = $this->get_submission($userid)) {
			return '';
		}
		
		$link = '/mod/assignment/type/babelium/file.php?id='.$this->cm->id.'&amp;userid='.$submission->userid;
		$action = 'file'.$userid;
		$image_tag = '<img src="http://'.$CFG->filter_babelium_serverdomain.'/resources/images/thumbs/'.$submission->data2.'/default.jpg" alt="submission" border="0" height="45" width="60"/>';
		$popup = link_to_popup_window ($link, $action, $image_tag, 500, 680, get_string('submission', 'assignment'), 'none', true);

		//$output = '<div class="files">'.
		//	  $popup .
		//	  '</div>'; 


		$html_content = '';
		$response_data = babelium_get_response_data($submission->data1);
		if($response_data)
    			$html_content = babelium_html_output(0,$response_data['info'],$response_data['subtitles']);

		//Stolen code from file.php
		print_simple_box_start('center', '', '', 0, 'generalbox', 'wordcount');
		echo $popup;
		print_simple_box_end();
		print_simple_box($html_content, 'center', '100%');

		///End of stolen code from file.php

		if ($return) {
			//return $output;
		}
		//echo $output;
	}

	/**
	 * Processes the submited data before displaying it in the submission details page
	 * @param unknown_type $submission
	 */
	function preprocess_submission(&$submission) {
		
		
		//if ($this->assignment->var1 && empty($submission->submissioncomment)) {  // comment inline
		//if ($this->usehtmleditor) {
		// Convert to html, clean & copy student data to teacher
		//    $submission->submissioncomment = format_text($submission->data1, $submission->data2);
		//    $submission->format = FORMAT_HTML;
		//} else {
		// Copy student data to teacher
		//    $submission->submissioncomment = $submission->data1;
		//    $submission->format = $submission->data2;
		//}
		//}
	}

	/**
	 * Form to display when adding a new assignment of this type
	 * @param unknown_type $mform
	 */
	function setup_elements(&$mform) {
		global $CFG, $COURSE;

		$exercises = array();
		$exercisesMenu = array();

		$exercises = babelium_get_available_exercise_list();
		if($exercises && count($exercises) > 0){
			foreach ($exercises as $exercise) {
				$exercisesMenu[$exercise['id']] = $exercise['title'];
			}
		}
		if(count($exercisesMenu)>0){
			$mform->addElement('select', 'var1', get_string('babeliumAvailableRecordableExercises', 'assignment_babelium'), $exercisesMenu);

			//Seems there's a bug with this. I tried deleting the cache of my browser, but the error still appear even to the help message is correctly displayed.
			$mform->setHelpButton('var1', array('babeliumAvailableRecordableExercises', get_string('babeliumAvailableRecordableExercises', 'assignment_babelium'), 'assignment_babelium'));
			$mform->setDefault('var1', 0);
			$mform->addElement('hidden', 'noexerciseavailable', 0);
		} else {
			$mform->addElement('static', 'noexercisemessage', get_string('babeliumAvailableRecordableExercises', 'assignment_babelium'), get_string('babeliumNoExerciseAvailable', 'assignment_babelium'));
			$mform->addElement('hidden', 'noexerciseavailable', 1);
		}

		$ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));

		$mform->addElement('select', 'resubmit', get_string('allowresubmit', 'assignment'), $ynoptions);
		$mform->setHelpButton('resubmit', array('resubmit', get_string('allowresubmit', 'assignment'), 'assignment'));	   
		$mform->setDefault('resubmit', 0);

		$mform->addElement('select', 'emailteachers', get_string('emailteachers', 'assignment'), $ynoptions);
		$mform->setHelpButton('emailteachers', array('emailteachers', get_string('emailteachers', 'assignment'), 'assignment'));
		$mform->setDefault('emailteachers', 0);

		$mform->disabledIf('submitbutton', 'noexerciseavailable', 'eq', 1);
		$mform->disabledIf('submitbutton2', 'noexerciseavailable', 'eq', 1);

	}
	function extend_settings_navigation($node) {
		global $PAGE, $CFG, $USER;

		// get users submission if there is one
		$submission = $this->get_submission();
		if (is_enrolled($PAGE->cm->context, $USER, 'mod/assignment:submit')) {
			$editable = $this->isopen() && (!$submission || $this->assignment->resubmit || !$submission->timemarked);
		} else {
			$editable = false;
		}

		// If the user has submitted something add a bit more stuff
		if ($submission) {
			// Add a view link to the settings nav
			$link = new moodle_url('/mod/assignment/view.php', array('id'=>$PAGE->cm->id));
			$node->add(get_string('viewmysubmission', 'assignment'), $link, navigation_node::TYPE_SETTING);

			if (!empty($submission->timemodified)) {
				$submittednode = $node->add(get_string('submitted', 'assignment') . ' ' . userdate($submission->timemodified));
				$submittednode->text = preg_replace('#([^,])\s#', '$1&nbsp;', $submittednode->text);
				$submittednode->add_class('note');
				if ($submission->timemodified <= $this->assignment->timedue || empty($this->assignment->timedue)) {
					$submittednode->add_class('early');
				} else {
					$submittednode->add_class('late');
				}
			}
		}

		if (!$submission || $editable) {
			// If this assignment is editable once submitted add an edit link to the settings nav
			$link = new moodle_url('/mod/assignment/view.php', array('id'=>$PAGE->cm->id, 'edit'=>1, 'sesskey'=>sesskey()));
			$node->add(get_string('editmysubmission', 'assignment'), $link, navigation_node::TYPE_SETTING);
		}
	}
}

class mod_assignment_babelium_edit_form extends moodleform {
	function definition() {
		
		$mform =& $this->_form;
		
		//PATCH It seems moodle 1.9 doesn't support JS strings so i'm gonna output the same code in raw js
		$strings_for_js = 
		'<script type="text/javascript">
			var M ={};
			M.str = {};
			M.str.assignment_babelium = {};
			M.str.assignment_babelium.babeliumViewRecording="'.get_string('babeliumViewRecording', 'assignment_babelium').'";
			M.str.assignment_babelium.babeliumViewExercise="'.get_string('babeliumViewExercise', 'assignment_babelium').'";
			M.str.assignment_babelium.babeliumStartRecording="'.get_string('babeliumStartRecording', 'assignment_babelium').'";
			M.str.assignment_babelium.babeliumStopRecording="'.get_string('babeliumStopRecording', 'assignment_babelium').'";
		</script>';		

		$mform->addElement('html',$strings_for_js);
		//ENDPATCH

		list($data, $exinfo, $exroles, $exlangs, $exsubs) = $this->_customdata;

		$roleMenu = array();
		if($exroles && count($exroles) > 0){
			foreach ($exroles as $exrole) {
				if(isset($exrole['characterName']) && $exrole['characterName'] != "NPC"){
					$roleMenu[$exrole['characterName']] = $exrole['characterName'];
				}
			}
		}
		
		//TODO Currently we only allow one language for the subtitles so this element is not needed for now
		//$localeMenu = array();
		//if($exlangs && count($exlangs) > 0){
		//	foreach($exlangs as $exlang){
		//		if(isset($exlang['locale']))
		//			$localeMenu[$exlang['locale']] = $exlang['locale'];
		//	}
		//}

		// hidden params
		$mform->addElement('hidden', 'id', 0);
		$mform->setType('id', PARAM_INT);

		$mform->addElement('hidden', 'edit');
		$mform->setType('edit', PARAM_INT);

		$mform->addElement('hidden', 'data1');
		//$mform->setType('data1', PARAM_INT);

		$mform->addElement('hidden', 'data2');
		$mform->setType('data2', PARAM_TEXT);
		//$mform->addRule('data2','You cannot save the assignment without recording something','required');
		$mform->addRule('data2', get_string('required'), 'required', null, 'client');
		
		//The role selected in the combobox the last time the user pushed the 'Start Recording' button 
		$mform->addElement('hidden', 'recordedRole');
		$mform->setType('recordedRole', PARAM_TEXT);

		$mform->addElement('hidden', 'subtitleId', $exsubs[0]['subtitleId']);
		$mform->addElement('hidden', 'exerciseDuration', $exinfo['duration']);

		//Returns a string with all the html and script tags needed to init the babelium widget
		$html_content = babelium_html_output(!empty($data->data2),$exinfo,$exsubs);
		
		$mform->addElement('html',$html_content); 
		$mform->addElement('select', 'roleCombo', get_string('babeliumChooseRole', 'assignment_babelium'), $roleMenu);
		
		//TODO Currently, we only allow one language for the subtitles so this element is not needed for now
		//$mform->addElement('select', 'localeCombo', get_string('babeliumChooseSubLang', 'assignment_babelium'), $localeMenu);

		$recmethods=array();
		$recmethods[] = $mform->createElement('radio', 'recmethod','none', get_string('babeliumMicOnly','assignment_babelium'), 0);
		$recmethods[] = $mform->createElement('radio', 'recmethod','none', get_string('babeliumWebcamMic','assignment_babelium'), 1);
		$mform->addGroup($recmethods, 'radioar', get_string('babeliumChooseRecMethod','assignment_babelium'), array(' '), false);

		//TODO check how the help dynamic popups retrieve their texts to apply the same principle to the label of these two buttons
		$babeliumactions=array();
		$babeliumactions[] = $mform->createElement('button', 'startStopRecordingBtn', get_string('babeliumStartRecording','assignment_babelium'));
		//$babeliumactions[] = $mform->createElement('button', 'viewRecordingExerciseBtn', get_string('babeliumViewExercise', 'assignment_babelium'),empty($data->data2) ? 'style="display:none;"' : null);
		$babeliumactions[] = $mform->createElement('button', 'viewRecordingBtn', get_string('babeliumViewRecording', 'assignment_babelium'), empty($data->data2) ? 'style="display:none;"' : null);
		$babeliumactions[] = $mform->createElement('button', 'viewExerciseBtn', get_string('babeliumViewExercise', 'assignment_babelium'), empty($data->data2) ? 'style="display:none;"' : null);
		$mform->addGroup($babeliumactions, 'babeliumActions', '', '', false);

		$this->add_action_buttons();

		$this->set_data($data);
	}

}


