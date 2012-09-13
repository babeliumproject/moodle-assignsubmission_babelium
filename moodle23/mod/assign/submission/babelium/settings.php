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
 * This file defines the admin settings for this plugin
 *
 * @package   assignsubmission_babelium
 * @copyright 2012 Babelium Project {@link http://babeliumproject.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Note this is on by default
$settings->add(new admin_setting_configcheckbox('assignsubmission_babelium/default',
                   				new lang_string('default', 'assignsubmission_babelium'),
                   				new lang_string('default_help', 'assignsubmission_babelium'), 
						0));

$settings->add(new admin_setting_configtext('assignsubmission_babelium/serverdomain',
					    new lang_string('serverdomain','assignsubmission_babelium'),
					    new lang_string('serverdomain_help','assignsubmission_babelium'),
					    'babeliumproject.com',
					    PARAM_TEXT));

$settings->add(new admin_setting_configtext('assignsubmission_babelium/serverport',
					    new lang_string('serverport','assignsubmission_babelium'),
					    new lang_string('serverport_help','assignsubmission_babelium'),
					    '80',
					    PARAM_INT));

$settings->add(new admin_setting_configtext('assignsubmission_babelium/apidomain',
					    new lang_string('apidomain','assignsubmission_babelium'),
					    new lang_string('apidomain_help','assignsubmission_babelium'),
					    'babeliumproject.com/api',
					    PARAM_TEXT));
					    
$settings->add(new admin_setting_configtext('assignsubmission_babelium/apiendpoint',
					    new lang_string('apiendpoint','assignsubmission_babelium'),
					    new lang_string('apiendpoint_help','assignsubmission_babelium'),
					    'rest.php',
					    PARAM_TEXT));

$settings->add(new admin_setting_configpasswordunmask('assignsubmission_babelium/accesskey',
					    	      new lang_string('accesskey','assignsubmission_babelium'),
					    	      new lang_string('accesskey_help','assignsubmission_babelium'),
						      ''));

$settings->add(new admin_setting_configpasswordunmask('assignsubmission_babelium/secretaccesskey',
					    	      new lang_string('secretaccesskey','assignsubmission_babelium'),
					    	      new lang_string('secretaccesskey_help','assignsubmission_babelium'),
						      ''));

