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
 * Babelium Assignment Type http://babeliumproject.com
 *
 * @package   assignment_babelium
 * @copyright Babelium Team
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// See http://docs.moodle.org/dev/version.php for a version.php template
 
defined('MOODLE_INTERNAL') || die();

// Minimum version of moodle to run this plugin
$plugin->requires  = 2007101570; // See http://docs.moodle.org/dev/Moodle_versions

// Internal version name (Format: release date of the plugin and version within that date)
$plugin->version   = 2012090600;
// Human-readable version name
$plugin->release   = '0.9.6 (Build: 2012090600)';

$plugin->cron      = 0;
$plugin->component = 'assignment_babelium';


