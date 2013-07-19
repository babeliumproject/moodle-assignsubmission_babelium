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

require_once($CFG->dirroot . '/mod/assignment/type/babelium/babelium_gateway.php');

/**
 * Grabs the settings of Babelium from either the configuration file or the filter plugin 
 * (is they are available)
 */
function babelium_parse_settings(){
	global $CFG;
	
	$babelium_cfg_path = $CFG->dirroot . '/mod/assignment/type/babelium/babelium_config.php';
	
	if( is_readable($babelium_cfg_path) && !filter_plugin_installed('filter/babelium') )
	{
		require_once $babelium_cfg_path;
		$bcfg = new babelium_config();
		
		$CFG->filter_babelium_serverdomain    = $bcfg->babelium_babeliumWebDomain;
		$CFG->filter_babelium_serverport      = $bcfg->babelium_babeliumWebPort;
		$CFG->filter_babelium_apidomain       = $bcfg->babelium_babeliumApiDomain;
		$CFG->filter_babelium_apiendpoint     = $bcfg->babelium_babeliumApiEndPoint; 
		$CFG->filter_babelium_accesskey       = $bcfg->babelium_babeliumApiAccessKey;
		$CFG->filter_babelium_secretaccesskey = $bcfg->babelium_babeliumApiSecretAccessKey;	
	} 
	//else 
	//{
	//	print "Babelium filter is installed, reading the connection settings from there.";
	//}	
}

/**
 * Checks if the provided filter path is installed
 * 
 * @param String $filterpath
 * 		The filter path in the format "folder/plugin_name" to check for files
 * @return boolean
 * 		true if the filter is installed, false otherwise
 */
function filter_plugin_installed($filterpath){
	global $CFG;
	$pluginpath = "$CFG->dirroot/$filterpath/filter.php";
	return is_readable($pluginpath);
}

/**
 * Checks if the provided filter path is installed and enabled (check "Site Administration -> Plugins -> Filters" or 
 * "Site Administration -> Modules -> Filters" area to see if a filter is enabled) in the moodle platform. Uses the built-in
 * function filter_is_enabled() for Moodle 2.x and raw checkups for Moodle 1.9.
 * 
 * @param String $filterpath
 * 		The filter path in the format "folder/plugin_name" to check for files
 * @return boolean
 * 		true if the filter is installed and enabled, false otherwise
 */
function filter_plugin_enabled($filterpath){
	global $CFG;
	// filter_is_enabled() function belongs to Moodle 2.x
	if(function_exists('filter_is_enabled')){
		return filter_is_enabled($filterpath);
	} else {
		// get all the currently selected filters
		if (!empty($CFG->textfilters)) {
			$activefilters = explode(',', $CFG->textfilters);
		} else {
			$activefilters = array();
		}
		// check if filter plugin location is readable
		$pluginpath = "$CFG->dirroot/$filterpath/filter.php";
		return is_readable($pluginpath) && in_array($filterpath, $activefilters);
	}
}

/**
 * Returns html code for displaying the babelium widget with the provided information
 *
 * @param int $mode
 *	States whether we should return the widget in play ($mode = 0) or review ($mode = 1) status
 * @param array $info
 *	Information about the exercise/response the widget is going to display
 * @param array $subs
 *	The subtitles & roles this exercise/response is going to use
 * @return String $html_content
 *	An html snippet that loads the babelium player and its related scripts
 */
function babelium_html_output($mode, $info, $subs){
	
	global $SESSION, $CFG;

	$exinfo = '""'; $exsubs = '""'; $rsinfo = '""'; $rssubs = '""';

	if($mode){
		$rsinfo = json_encode($info);
		$rssubs = json_encode($subs);
	} else {
		$exinfo = json_encode($info);
		$exsubs = json_encode($subs);
	}	
		
	$html_content = '';
	$html_content.='<h2 id="babelium-exercise-title">'.$info['title'].'</h2>';
	$html_content.='<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.js" language="javascript"></script>';
	$html_content.='<script src="'. $CFG->wwwroot .'/mod/assignment/type/babelium/script/swfobject.js" language="javascript"></script>';
	$html_content.='<script src="'. $CFG->wwwroot .'/mod/assignment/type/babelium/babelium.moodle.js" language="javascript"></script>';
	$html_content.='<div id="flashContent">
			 <p>To view this page ensure that Adobe Flash Player version 10.2.0 or greater is installed. </p>
			 <script type="text/javascript"> 
				var pageHost = ((document.location.protocol == "https:") ? "https://" : "http://"); 
				document.write("<a href=\'http://www.adobe.com/go/getflashplayer\'><img src=\'" 
						+ pageHost + "www.adobe.com/images/shared/download_buttons/get_flash_player.gif\' alt=\'Get Adobe Flash player\' /></a>" ); 
			</script> 
			</div>     
			<noscript><p>Either scripts and active content are not permitted to run or Adobe Flash Player version 10.2.0 or greater is not installed.</p></noscript>';
	$lang = isset($SESSION->lang) ? $SESSION->lang : '';
	$html_content .= '<script language="javascript" type="text/javascript">
				init("'.$CFG->filter_babelium_serverdomain.'", "'.$lang.'", '.$exinfo.', '.$exsubs.', '. $rsinfo .', '. $rssubs .');
			  </script>';
	return $html_content;
}

/**
 * Gets the list of recordable exercises available to this moodle instance
 * @return mixed $result
 * 		An array of exercise data if successful or false on error/when empty query results
 */
function babelium_get_available_exercise_list(){	
	$g = new babelium_gateway();
	$result = $g->newServiceCall('getRecordableExercises');
	return $result;
}

/**
 * Retrieves the locales, subtitles, roles and data of a particular exercise
 * @param int $exerciseid
 * 		An exercise identificator
 * @return mixed $exercise
 * 		An associative array with the info, the roles, the languages and the subtitle lines of the exercise, or false on error/when empty query results
 */
function babelium_get_exercise_data($exerciseid){
	$g = new babelium_gateway();
	$exerciseInfo = $g->newServiceCall('getExerciseById', array("id"=>$exerciseid));

	if(is_array($exerciseInfo)){
		$exerciseInfo = array(
			"exerciseId" => $exerciseInfo['id'],
			"exerciseName" => $exerciseInfo['name'],
			"duration" => $exerciseInfo['duration'],
			"exerciseThumbnailUri" => $exerciseInfo['thumbnailUri'],
			"title" => $exerciseInfo['title']
		);
	}
	
	$exerciseSubtitleLanguages = $g->newServiceCall('getExerciseLocales', array("exerciseId"=>$exerciseid));
	//TODO in future versions this should be replaced. We should retrieve all the available subtitle locales instead and change with js combo selection
	$exerciseSubtitleLines = $g->newServiceCall('getSubtitleLines', array("exerciseId"=>$exerciseid, "language"=>$exerciseSubtitleLanguages[0]['locale']));
	
	$exerciseRoles = array();
	foreach($exerciseSubtitleLines as $subline){
		$role = array("id"=>$subline['exerciseRoleId'], "characterName"=>$subline['exerciseRoleName']); 
		if(!in_array($role,$exerciseRoles))
			$exerciseRoles[] = $role;
	}
	
	if($exerciseInfo && $exerciseRoles && $exerciseSubtitleLanguages && $exerciseSubtitleLines && 
	   is_array($exerciseRoles[0]) && is_array($exerciseSubtitleLanguages[0]) && is_array($exerciseSubtitleLines[0])){
		$exercise = array("info" => $exerciseInfo, "roles" => $exerciseRoles, "languages" => $exerciseSubtitleLanguages, "subtitles" => $exerciseSubtitleLines); 
		return $exercise;
	} else {
		//TODO add some kind of log function here so that the admin knows what happened: add_to_log() maybe
		return;
	}
}

/**
 * Retrieves the locales, subtitles, roles and data of a particular response and its related exercise
 * @param int $responseid
 * @return mixed $response
 * 		An associative array with the info, the roles, the languages and the subtitle lines of the response, or false on error/when empty query results
 */
function babelium_get_response_data($responseid){
	$g = new babelium_gateway();
	$responseInfo = $g->newServiceCall('admGetResponseById', array("responseId"=>$responseid));
	$responseSubtitleLines = $g->newServiceCall('getSubtitleLines', array("id"=>$responseInfo['subtitleId'],"language"=>''));

	$exerciseRoles = array();
	foreach($responseSubtitleLines as $subline){
		$role = array("id"=>$subline['exerciseRoleId'], "characterName"=>$subline['exerciseRoleName']); 
		if(!in_array($role,$exerciseRoles))
			$exerciseRoles[] = $role;
	}

	if($responseInfo && $responseSubtitleLines && $exerciseRoles && is_array($responseSubtitleLines[0]) && is_array($exerciseRoles[0])){
		$response = array("info" => $responseInfo, "subtitles" => $responseSubtitleLines, "roles" => $exerciseRoles, "languages" => null);
		return $response;
	} else {
		//TODO add logging for failure
		return;
	}
}

/**
 * Saves the data of a new response recorded using the plugin
 * @param int $exerciseId
 * 		An exercise identificator
 * @param int $exerciseDuration
 * 		The duration of the exercise in seconds
 * @param int $subtitleId
 * 		The identificator of the subtitles that were used on the recording process
 * @param string $recordedRole
 * 		The character name that was impersonated in the recording process 
 * @param String $responseName
 * 		The hash name of the recording file
 * @return mixed $responseData
 * 		Array with information about the newly saved response, or false on error
 */
function babelium_save_response_data($exerciseId, $exerciseDuration, $subtitleId, $recordedRole, $responseName){
	$g = new babelium_gateway();
	$parameters = array(
				"exerciseId" => $exerciseId,
				"duration" => $exerciseDuration,
				"subtitleId" => $subtitleId,
				"characterName" => $recordedRole,
				"fileIdentifier" => $responseName 
				);
	return $responsedata = $g->newServiceCall('admSaveResponse', $parameters);
}

/* HELPER FUNCTIONS */

/**
 * Filters the provided exercise list keeping only the exercises that match the given locale
 * 
 * @param array $exerciseList
 * 		An array of exercise objects
 * @param  string $locale
 * 		A standard Locale ID (LCID) tag. For example: en_US, fr_FR, en_GB, es_ES
 * @return array $filteredList
 * 		A subset of the provided list with exercises that match the requested locale ID
 */
function babelium_filter_exercise_list_by_locale($exerciseList, $locale){
	$filteredList = array();
	$sortingArray = array();
	$sortingField = "title";
	if (!$exerciseList || !$locale || !is_array($exerciseList)) return;
	foreach($exerciseList as $exercise){
		if($exercise['language'] == $locale) $filteredList[] = $exercise;
	}
	$filteredList = babelium_sort_list_by_field($filteredList, "title");

	return $filteredList;
}

/**
 * Sorts the provided object array using the provided field
 * 
 * @param array $list
 * 		An unordered list of objects that need be sorted
 * @param mixed $field
 * 		The field of the object by which the list if going to be sorted
 * @return array $sortedList
 * 		The same list given as input but ordered by the specified field
 */
function babelium_sort_list_by_field($list, $field){
	if (!$list || !$field || !is_array($list)) return;
	$sortedList = $list;
	$sortingArray = array();
	foreach($sortedList as $listItem){
		foreach($listItem as $key=>$value){
			if(!isset($sortingArray[$key])){
				$sortingArray[$key] = array();
			}
			//We force the lowercase conversion of the values to perform a case insensitive sorting
			$sortingArray[$key][] = mb_strtolower($value,'UTF-8');
		}
	}
	//TODO
	//Use locale aware string sorting to avoid acutes being misplaced. Need to figure out where to get the locale to use.
	//setlocale();
	//array_multisort($sortingArray[$field], SORT_ASC, SORT_LOCALE_STRING, $sortedList);

	array_multisort($sortingArray[$field], SORT_ASC, SORT_STRING, $sortedList);
	return $sortedList;
}
