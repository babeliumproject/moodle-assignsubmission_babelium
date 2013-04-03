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
//require_once($CFG->libdir . '/portfoliolib.php');
//require_once($CFG->libdir . '/filelib.php');


require_once($CFG->dirroot . '/mod/assignment/type/babelium/babelium_widget.php');

/**
 * Extend the base assignment class for Babelium assignments
 */
class assignment_babelium extends assignment_base {

	var $filearea = 'submission';

	function assignment_babelium($cmid='staticonly', $assignment=NULL, $cm=NULL, $course=NULL) {
		parent::assignment_base($cmid, $assignment, $cm, $course);
		$this->type = 'babelium';
		babelium_parse_settings();
	}

	/**
	 * Displays the assignment in its various states
	 */
	function view() {
		global $OUTPUT, $CFG, $USER, $PAGE;

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

		$submission = $this->get_submission($USER->id, false);
		//Guest can not submit nor edit an assignment (bug: 4604)
		if (!is_enrolled($this->context, $USER, 'mod/assignment:submit')) {
			$editable = false;
		} else {
			$editable = $this->isopen() && (!$submission || $this->assignment->resubmit || !$submission->timemarked);
		}
		$editmode = ($editable and $edit);
		if ($editmode) {
			// prepare form and process submitted data
			$data = new stdClass();
			$data->id = $this->cm->id;
			$data->edit = 1;
			if ($submission) {
				$data->sid = $submission->id;
				$data->data1 = $submission->data1;
				$data->data2 = $submission->data2;
			} else {
				$data->sid = NULL;
				$data->data1 = NULL;
				$data->data2 = '';
			}
			
			//TODO
			//If the assignment is being already submitted, we should retrieve the response data and display that data in the edit mode
			//until the user decides to record again or not

			$exercise_data = !empty($data->data2) ? babelium_get_response_data($data->data1) : babelium_get_exercise_data($this->assignment->var1);

			if(!$exercise_data)
				throw new dml_exception("Error while retrieving Babelium external data");
			

			$mform = new mod_assignment_babelium_edit_form(null, array($data, $exercise_data['info'], $exercise_data['roles'], $exercise_data['languages'], $exercise_data['subtitles']));

			if ($mform->is_cancelled()) {
				redirect($PAGE->url);
			}
			//If we reach here means the user didn't click the cancel button?
			if ($data = $mform->get_data()) {
				$submission = $this->get_submission($USER->id, true); //create the submission if needed & its id

				$submission = $this->update_submission($data);

				//TODO fix log actions - needs db upgrade
				add_to_log($this->course->id, "assignment_babelium", "update submission", 'view.php?a='.$this->assignment->id, $this->assignment->id, $this->cm->id);
				$this->email_teachers($submission);

				//redirect to get updated submission date and word count
				redirect(new moodle_url($PAGE->url, array('saved'=>1)));
			}
		}

		add_to_log($this->course->id, "assignment_babelium", "view", "view.php?id={$this->cm->id}", $this->assignment->id, $this->cm->id);

		// print header, etc. and display form if needed
		if ($editmode) {
			$this->view_header(get_string('editmysubmission', 'assignment'));
		} else {
			$this->view_header();
		}

		//This displays the name of the assignment
		$this->view_intro();

		//This displays the dates of the assignment: available, due, last edition
		$this->view_dates();

		//If something's been saved this moment display a notification
		if ($saved) {
			echo $OUTPUT->notification(get_string('submissionsaved', 'assignment'), 'notifysuccess');
		}

		if (is_enrolled($this->context, $USER)) {
			if ($editmode) {
				echo $OUTPUT->box_start('generalbox', 'babeliumenter');
				$mform->display();
			} else {
				echo $OUTPUT->box_start('generalbox boxwidthwide boxaligncenter', 'babelium');
				if ($submission && has_capability('mod/assignment:exportownsubmission', $this->context)) {
					$html_content = '';
					$response_data = babelium_get_response_data($submission->data1);
					if($response_data)
    						$html_content = babelium_html_output(1,$response_data['info'],$response_data['subtitles']);
					echo $html_content;
					/*
					   if ($CFG->enableportfolios) {
					   require_once($CFG->libdir . '/portfoliolib.php');
					   $button = new portfolio_add_button();
					   $button->set_callback_options('assignment_portfolio_caller', array('id' => $this->cm->id), '/mod/assignment/locallib.php');
					   $fs = get_file_storage();
					   if ($files = $fs->get_area_files($this->context->id, 'mod_assignment', $this->filearea, $submission->id, "timemodified", false)) {
					   $button->set_formats(PORTFOLIO_FORMAT_RICHHTML);
					   } else {
					   $button->set_formats(PORTFOLIO_FORMAT_PLAINHTML);
					   }
					   $button->render();
					   }
					 */
				} else if ($this->isopen()){    //fix for #4206
					echo '<div style="text-align:center">'.get_string('emptysubmission', 'assignment').'</div>';
				}
			}
			echo $OUTPUT->box_end();
			if (!$editmode && $editable) {
				if (!empty($submission)) {
					$submitbutton = "editmysubmission";
				} else {
					$submitbutton = "addsubmission";
				}
				echo "<div style='text-align:center'>";
				echo $OUTPUT->single_button(new moodle_url('view.php', array('id'=>$this->cm->id, 'edit'=>'1')), get_string($submitbutton, 'assignment'));
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
		global $USER, $CFG, $OUTPUT;

		if (!$this->assignment->timeavailable && !$this->assignment->timedue) {
			return;
		}

		echo $OUTPUT->box_start('generalbox boxaligncenter', 'dates');
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
		echo $OUTPUT->box_end();
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
		global $CFG, $USER, $DB;

		$submission = $this->get_submission($USER->id, true);

		//Put in this object the fields of the database you wish to change
		$update = new stdClass();
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

		$DB->update_record('assignment_submissions', $update);

		$submission = $this->get_submission($USER->id);
		$this->update_grade($submission);
		return $submission;
	}

	/**
	 * Displays an overview of the submission of a certain user
	 * @param unknown_type $userid
	 * @param unknown_type $return
	 */
	function print_student_answer($userid, $return=false){
		global $CFG, $OUTPUT;
		if (!$submission = $this->get_submission($userid)) {
			return '';
		}

		$link = new moodle_url("/mod/assignment/type/babelium/file.php?id={$this->cm->id}&userid={$submission->userid}");
		$action = new popup_action('click', $link, 'file'.$userid, array('height' => 500, 'width' => 680));
		$image_tag = '<img src="http://'.$CFG->filter_babelium_serverdomain.'/resources/images/thumbs/'.$submission->data2.'/default.jpg" alt="submission" border="0" height="45" width="60"/>';
		$popup = $OUTPUT->action_link($link, $image_tag, $action, array('title'=>get_string('submission', 'assignment')));

        	$output = '<div class="files">'.
			  $popup .
			  '</div>';
		return $output;
	}

	/**
	 * Displays full info about the submission of the given user ID for the current assignment
	 * @param int $userid
	 * @param boolean $return
	 * 	Defaults to false. If true the list is returned rather than printed
	 */
	function print_user_files($userid=0, $return=false) {
		global $CFG, $USER, $OUTPUT;
	
		if (!$userid) {
			if (!isloggedin()) {
				return '';
			}
			$userid = $USER->id;
		}

		$output = '';

		$submission = $this->get_submission($userid);
		if (!$submission) {
			return $output;
		}
		
		$link = new moodle_url("/mod/assignment/type/babelium/file.php?id={$this->cm->id}&userid={$submission->userid}");
		$action = new popup_action('click', $link, 'file'.$userid, array('height' => 500, 'width' => 680));
		$image_tag = '<img src="http://'.$CFG->filter_babelium_serverdomain.'/resources/images/thumbs/'.$submission->data2.'/default.jpg" alt="submission" border="0" height="45" width="60"/>';
		$popup = $OUTPUT->action_link($link, $image_tag . get_string('popupinnewwindow','assignment'), $action, array('title'=>get_string('submission', 'assignment')));

		$wordcount = '<p id="wordcount" style="vertical-align=middle;">'. $popup . '&nbsp;</p>';

		$html_content = '';
		$response_data = babelium_get_response_data($submission->data1);
		if($response_data)
    			$html_content = babelium_html_output(0,$response_data['info'],$response_data['subtitles']);

		$output = $wordcount . $html_content;

		$output = '<div class="files">'.$output.'</div>';

		if ($return) {
			return $output;
		}
		echo $output;
	}

	/**
	 * Processes the submited data before displaying it in the submission details page
	 * @param unknown_type $submission
	 */
	function preprocess_submission(&$submission) {
		//error_log("preprocess submisssion : ".print_r($submission,true)."\n",3,"/tmp/moodle.log");
		//Inline comment does not exist for our assignment so do nothing
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
			$mform->addHelpButton('var1', 'babeliumAvailableRecordableExercises', 'assignment_babelium');
			$mform->setDefault('var1', 0);
			$mform->addElement('hidden', 'noexerciseavailable', 0);
		} else {
			$mform->addElement('static', 'noexercisemessage', get_string('babeliumAvailableRecordableExercises', 'assignment_babelium'), get_string('babeliumNoExerciseAvailable', 'assignment_babelium'));
			$mform->addElement('hidden', 'noexerciseavailable', 1);
		}

		$ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));

		$mform->addElement('select', 'resubmit', get_string('allowresubmit', 'assignment'), $ynoptions);
		$mform->addHelpButton('resubmit', 'allowresubmit', 'assignment');
		$mform->setDefault('resubmit', 0);

		$mform->addElement('select', 'emailteachers', get_string('emailteachers', 'assignment'), $ynoptions);
		$mform->addHelpButton('emailteachers', 'emailteachers', 'assignment');
		$mform->setDefault('emailteachers', 0);

	}
	/*
	   function portfolio_exportable() {
	   return true;
	   }

	   function portfolio_load_data($caller) {
	   $submission = $this->get_submission();
	   $fs = get_file_storage();
	   if ($files = $fs->get_area_files($this->context->id, 'mod_assignment', $this->filearea, $submission->id, "timemodified", false)) {
	   $caller->set('multifiles', $files);
	   }
	   }

	   function portfolio_get_sha1($caller) {
	   $submission = $this->get_submission();
	   $textsha1 = sha1(format_text($submission->data1, $submission->data2));
	   $filesha1 = '';
	   try {
	   $filesha1 = $caller->get_sha1_file();
	   } catch (portfolio_caller_exception $e) {} // no files
	   return sha1($textsha1 . $filesha1);
	   }

	   function portfolio_prepare_package($exporter, $user) {
	   $submission = $this->get_submission($user->id);
	   $options = portfolio_format_text_options();
	   $html = format_text($submission->data1, $submission->data2, $options);
	   $html = portfolio_rewrite_pluginfile_urls($html, $this->context->id, 'mod_assignment', $this->filearea, $submission->id, $exporter->get('format'));
	   if (in_array($exporter->get('formatclass'), array(PORTFOLIO_FORMAT_PLAINHTML, PORTFOLIO_FORMAT_RICHHTML))) {
	   if ($files = $exporter->get('caller')->get('multifiles')) {
	   foreach ($files as $f) {
	   $exporter->copy_existing_file($f);
	   }
	   }
	   return $exporter->write_new_file($html, 'assignment.html', !empty($files));
	   } else if ($exporter->get('formatclass') == PORTFOLIO_FORMAT_LEAP2A) {
	   $leapwriter = $exporter->get('format')->leap2a_writer();
	   $entry = new portfolio_format_leap2a_entry('assignmentbabelium' . $this->assignment->id, $this->assignment->name, 'resource', $html);
	   $entry->add_category('web', 'resource_type');
	   $entry->published = $submission->timecreated;
	   $entry->updated = $submission->timemodified;
	   $entry->author = $user;
	   $leapwriter->add_entry($entry);
	   if ($files = $exporter->get('caller')->get('multifiles')) {
	   $leapwriter->link_files($entry, $files, 'assignmentbabelium' . $this->assignment->id . 'file');
	   foreach ($files as $f) {
	   $exporter->copy_existing_file($f);
	   }
	   }
	   $exporter->write_new_file($leapwriter->to_xml(), $exporter->get('format')->manifest_name(), true);
	   } else {
	   debugging('invalid format class: ' . $exporter->get('formatclass'));
	   }
	   }
	 */
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
	/*
	   public function send_file($filearea, $args) {
	   global $USER;
	   require_capability('mod/assignment:view', $this->context);

	   $fullpath = "/{$this->context->id}/mod_assignment/$filearea/".implode('/', $args);

	   $fs = get_file_storage();
	   if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
	   send_file_not_found();
	   }

	   if (($USER->id != $file->get_userid()) && !has_capability('mod/assignment:grade', $this->context)) {
	   send_file_not_found();
	   }

	   session_get_instance()->write_close(); // unlock session during fileserving
	   send_stored_file($file, 60*60, 0, true);
	   }*/

	/**
	 * creates a zip of all assignment submissions and sends a zip to the browser
	 */
	/*
	   public function download_submissions() {
	   global $CFG, $DB;

	   raise_memory_limit(MEMORY_EXTRA);

	   $submissions = $this->get_submissions('','');
	   if (empty($submissions)) {
	   print_error('errornosubmissions', 'assignment');
	   }
	   $filesforzipping = array();

	//NOTE: do not create any stuff in temp directories, we now support unicode file names and that would not work, sorry

	//babelium assignment can use html
	$filextn=".html";

	$groupmode = groups_get_activity_groupmode($this->cm);
	$groupid = 0;   // All users
	$groupname = '';
	if ($groupmode) {
	$groupid = groups_get_activity_group($this->cm, true);
	$groupname = groups_get_group_name($groupid).'-';
	}
	$filename = str_replace(' ', '_', clean_filename($this->course->shortname.'-'.$this->assignment->name.'-'.$groupname.$this->assignment->id.".zip")); //name of new zip file.
	foreach ($submissions as $submission) {
	$a_userid = $submission->userid; //get userid
	if ((groups_is_member($groupid,$a_userid)or !$groupmode or !$groupid)) {
	$a_assignid = $submission->assignment; //get name of this assignment for use in the file names.
	$a_user = $DB->get_record("user", array("id"=>$a_userid),'id,username,firstname,lastname'); //get user firstname/lastname
	$submissioncontent = "<html><body>". format_text($submission->data1, $submission->data2). "</body></html>";      //fetched from database
	//get file name.html
	$fileforzipname =  clean_filename(fullname($a_user) . "_" .$a_userid.$filextn);
	$filesforzipping[$fileforzipname] = array($submissioncontent);
	}
	}      //end of foreach

	if ($zipfile = assignment_pack_files($filesforzipping)) {
	send_temp_file($zipfile, $filename); //send file and delete after sending.
	}
	}*/
}

class mod_assignment_babelium_edit_form extends moodleform {
	function definition() {

		global $CFG, $SESSION, $PAGE;
		
		$PAGE->requires->string_for_js('babeliumViewRecording', 'assignment_babelium');
		$PAGE->requires->string_for_js('babeliumViewExercise', 'assignment_babelium');
		$PAGE->requires->string_for_js('babeliumStartRecording', 'assignment_babelium');
		$PAGE->requires->string_for_js('babeliumStopRecording', 'assignment_babelium');

		$mform = $this->_form;

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
		$mform->addElement('hidden', 'id');
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
		//$babeliumactions[] = $mform->createElement('button', 'viewRecordingExerciseBtn', get_string('babeliumViewExercise', 'assignment_babelium'), empty($data->data2) ? 'style="display:none;"' : null);
		$babeliumactions[] = $mform->createElement('button', 'viewRecordingBtn', get_string('babeliumViewRecording', 'assignment_babelium'), empty($data->data2) ? 'style="display:none;"' : null);
		$babeliumactions[] = $mform->createElement('button', 'viewExerciseBtn', get_string('babeliumViewExercise', 'assignment_babelium'), empty($data->data2) ? 'style="display:none;"' : null);
		$mform->addGroup($babeliumactions, 'babeliumActions', '', '', false);

		$this->add_action_buttons();

		$this->set_data($data);
	}

}


