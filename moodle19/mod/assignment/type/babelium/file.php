<?php

require("../../../../config.php");
require("../../lib.php");
require("assignment.class.php");

$id     = required_param('id', PARAM_INT);      // Course Module ID
$userid = required_param('userid', PARAM_INT);  // User ID

//$PAGE->set_url('/mod/assignment/type/babelium/file.php', array('id'=>$id, 'userid'=>$userid));

if (! $cm = get_coursemodule_from_id('assignment', $id)) {
        error("Course Module ID was incorrect");
    }

    if (! $assignment = get_record("assignment", "id", $cm->instance)) {
        error("Assignment ID was incorrect");
    }

    if (! $course = get_record("course", "id", $assignment->course)) {
        error("Course is misconfigured");
    }

    if (! $user = get_record("user", "id", $userid)) {
        error("User is misconfigured");
    }

    require_login($course->id, false, $cm);

    if (($USER->id != $user->id) && !has_capability('mod/assignment:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        error("You can not view this assignment");
    }

    if ($assignment->assignmenttype != 'babelium') {
        error("Incorrect assignment type");
    }


$assignmentinstance = new assignment_babelium($cm->id, $assignment, $cm, $course);

if ($submission = $assignmentinstance->get_submission($user->id)) {

	print_header(fullname($user,true).': '.$assignment->name);

	print_simple_box_start('center', '', '', '', 'generalbox', 'dates');
    echo '<table>';
    if ($assignment->timedue) {
		echo '<tr><td class="c0">'.get_string('duedate','assignment').':</td>';
        echo '    <td class="c1">'.userdate($assignment->timedue).'</td></tr>';
   	}
	echo '<tr><td class="c0">'.get_string('lastedited').':</td>';
	echo '    <td class="c1">'.userdate($submission->timemodified);
		//TODO print something like the number of attempts made by the user
	echo '</table>';
	print_simple_box_end();

	$html_content = '';
	$response_data = babelium_get_response_data($submission->data1);
	if($response_data)
		$html_content = babelium_html_output(0,$response_data['info'],$response_data['subtitles']);
   	print_simple_box($html_content, 'center', '100%');
    close_window_button();
    print_footer('none');
} else {
    print_string('emptysubmission', 'assignment');
}
