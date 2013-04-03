<?php

// This file is part of Babelium - http://babeliumproject.com/
//
// Babelium is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Babelium is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Babelium.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file defines the connection settings for the Babelium assignment type.
 * Since no settings page is available for assignment types we use this filter instead.
 *
 * @package   filter_babelium
 * @copyright 2012 Babelium Project {@link http://babeliumproject.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if($ADMIN->fulltree) {
	
	$items = array();

	$items[] = new admin_setting_configtext('filter_babelium_serverdomain',
					    					get_string('serverdomain','filter_babelium'),
					    					get_string('serverdomain_help','filter_babelium'),
					    					'babeliumproject.com',
					    					PARAM_TEXT);

	$items[] = new admin_setting_configtext('filter_babelium_serverport',
					    					get_string('serverport','filter_babelium'),
					   						get_string('serverport_help','filter_babelium'),
					    					'80',
					    					PARAM_INT);

	$items[] = new admin_setting_configtext('filter_babelium_apidomain',
					    					get_string('apidomain','filter_babelium'),
					    					get_string('apidomain_help','filter_babelium'),
					    					'babeliumproject.com/api',
					    					PARAM_TEXT);
					    
	$items[] = new admin_setting_configtext('filter_babelium_apiendpoint',
					    					get_string('apiendpoint','filter_babelium'),
					    					get_string('apiendpoint_help','filter_babelium'),
					    					'rest.php',
					    					PARAM_TEXT);

	$items[] = new admin_setting_configpasswordunmask('filter_babelium_accesskey',
					    	     					  get_string('accesskey','filter_babelium'),
					    	      					  get_string('accesskey_help','filter_babelium'),
						      						  '');

	$items[] = new admin_setting_configpasswordunmask('filter_babelium_secretaccesskey',
					    	      					  get_string('secretaccesskey','filter_babelium'),
					    	      					  get_string('secretaccesskey_help','filter_babelium'),
						      						  '');
	
	foreach($items as $item){
		$settings->add($item);
	}

}