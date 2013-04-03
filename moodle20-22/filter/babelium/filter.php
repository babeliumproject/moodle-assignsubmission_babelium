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
 * This file defines the actions performed by the Babelium filter
 *
 * @package   filter_babelium
 * @copyright 2012 Babelium Project {@link http://babeliumproject.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_babelium extends moodle_text_filter {

	/**
	 * Dummy filtering function
	 * @param String $text
	 * @param array $options
	 */
	function filter($text, array $options = array()) {
		global $CFG;

		//The names of the settings variables
		//$CFG->filter_babelium_serverdomain;
		//$CFG->filter_babelium_serverport;
		//$CFG->filter_babelium_apidomain;
		//$CFG->filter_babelium_apiendpoint;
		//$CFG->filter_babelium_accesskey;
		//$CFG->filter_babelium_secretaccesskey;

		return $text;
	}
}

?>