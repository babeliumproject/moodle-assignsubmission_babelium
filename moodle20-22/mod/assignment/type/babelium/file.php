<?php

/**
 * Babelium Project open source collaborative second language oral practice - http://www.babeliumproject.com
 *
 * Copyright (c) 2011 GHyM and by respective authors (see below).
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

require("../../../../config.php");
require("../../lib.php");
require("assignment.class.php");

$id     = required_param('id', PARAM_INT);      // Course Module ID
$userid = required_param('userid', PARAM_INT);  // User ID

$PAGE->set_url('/mod/assignment/type/babelium/file.php', array('id'=>$id, 'userid'=>$userid));

if (! $cm = get_coursemodule_from_id('assignment', $id)) {
    print_error('invalidcoursemodule');
}

if (! $assignment = $DB->get_record("assignment", array("id"=>$cm->instance))) {
    print_error('invalidid', 'assignment');
}

if (! $course = $DB->get_record("course", array("id"=>$assignment->course))) {
    print_error('coursemisconf', 'assignment');
}

if (! $user = $DB->get_record("user", array("id"=>$userid))) {
    print_error('usermisconf', 'assignment');
}

require_login($course->id, false, $cm);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
if (($USER->id != $user->id) && !has_capability('mod/assignment:grade', $context)) {
    print_error('cannotviewassignment', 'assignment');
}

if ($assignment->assignmenttype != 'babelium') {
    print_error('invalidtype', 'assignment');
}

$assignmentinstance = new assignment_babelium($cm->id, $assignment, $cm, $course);

if ($submission = $assignmentinstance->get_submission($user->id)) {
	$PAGE->set_pagelayout('popup');
	$PAGE->set_title(fullname($user,true).': '.$assignment->name);
	echo $OUTPUT->header();
	echo $OUTPUT->box_start('generalbox boxaligcenter', 'dates');
	echo '<table>';
	if ($assignment->timedue) {
		echo '<tr><td class="c0">'.get_string('duedate','assignment').':</td>';
		echo '    <td class="c1">'.userdate($assignment->timedue).'</td></tr>';
	}
	echo '<tr><td class="c0">'.get_string('lastedited').':</td>';
	echo '    <td class="c1">'.userdate($submission->timemodified);
	echo '</td></tr></table>';
	echo $OUTPUT->box_end();

	$html_content = '';
	$response_data = babelium_get_response_data($submission->data1);
	if($response_data)
    		$html_content = babelium_html_output(0,$response_data['info'],$response_data['subtitles']);

	echo $OUTPUT->box($html_content, 'generalbox boxaligncenter boxwidthwide');
	echo $OUTPUT->close_window_button();
	echo $OUTPUT->footer();
} else {
    print_string('emptysubmission', 'assignment');
}
