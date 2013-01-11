<?php

require_once 'utils/Config.php';
require_once 'utils/Datasource.php';
require_once 'utils/VideoProcessor.php';

/**
 * This is a minimum subset of the services Babelium provides, quickly piled up
 * to test a certain communication configuration for the moodle plugin.
 * We'll be modularizing the final solution but for now please forgive the
 * monolithic architecture.
 */
class PluginSubset{

	//Configuration value object
	private $conf;
	
	//Database interaction object
	private $db;

	//Multimedia file handling object
	private $mediaHelper;

	public static $userId;

	public function __construct(){
		try{
			error_log("INSTANCE KEY ".self::$userId."\n",3,"/tmp/test.log");
			$this->conf = new Config();
			$this->db = new Datasource($this->conf->host, $this->conf->db_name, $this->conf->db_username, $this->conf->db_password);
			$this->mediaHelper = new VideoProcessor();
		} catch(Exception $e){
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * SUBTITLE.PHP
	 */
	//Not needed, retrieving the subtitle lines does the work processing the output
	//public function getExerciseRoles($exerciseId = 0) {
	//	if(!$exerciseId)
	//	return false;
	//
	//	$sql = "SELECT MAX(id) as id,
	//		       fk_exercise_id as exerciseId,
	//		       character_name as characterName
	//		FROM exercise_role WHERE (fk_exercise_id = %d) 
	//		GROUP BY exercise_role.character_name ";
	//
	//	$searchResults = $this->db->_multipleSelect( $sql, $exerciseId );
	//
	//	return $searchResults;
	//}

	public function getSubtitleLines($subtitle=null) {
		
		error_log("SUBTITLE_LINES: ".print_r($subtitle,true)."\n",3,"/tmp/test.log");	
		if(!$subtitle)
			return false;

		$sql = "SELECT SL.id,
			       SL.show_time as showTime,
			       SL.hide_time as hideTime, 
			       SL.text, 
			       SL.fk_exercise_role_id as exerciseRoleId, 
			       ER.character_name as exerciseRoleName, 
			       S.id as subtitleId
		        FROM (subtitle_line AS SL INNER JOIN subtitle AS S ON SL.fk_subtitle_id = S.id) 
		        INNER JOIN exercise AS E ON E.id = S.fk_exercise_id 
		        RIGHT OUTER JOIN exercise_role AS ER ON ER.id=SL.fk_exercise_role_id
			WHERE ";
		if(!$subtitle->id){
			$sql .= "S.id = (SELECT MAX(SS.id)
					 FROM subtitle SS 
					 WHERE SS.fk_exercise_id =%d AND SS.language = '%s') ";
			$searchResults = $this->db->_multipleSelect ( $sql, $subtitle->exerciseId, $subtitle->language );
		}else{
			$sql .= "S.id=%d";
			$searchResults = $this->db->_multipleSelect ( $sql, $subtitle->id );
		}

		$recastedResults = $searchResults; //this is a dummy assignment for cross-compatibility
		return $recastedResults;
	}

	/**
	 * EXERCISE.PHP
	 */
	public function getRecordableExercises(){
		$where = "";
		$userId = self::$userId;
		if ($userId)
			//$where = " AND u.ID = ". $userId . " " ;
			//sprintf() if you're going to use % as a character scape it putting it twice %%, otherwise there'll be problems while parsing your string
			$where = " AND ( u.ID = ". $userId ." OR (e.license like 'cc-%%' AND e.language IN (select language from user_languages where id=u.ID))) ";
		else
			return;

		$sql = "SELECT e.id,
			       e.title, 
			       e.description, 
			       e.language, 
			       e.tags, 
			       e.source, 
			       e.name, 
			       e.thumbnail_uri as thumbnailUri,
       			       e.adding_date as addingDate, 
			       e.duration, 
			       u.name as userName, 
       			       avg (suggested_level) as avgDifficulty,
			       e.status, 
			       e.license, 
			       e.reference
			       FROM   exercise e 
				 		INNER JOIN users u ON e.fk_user_id= u.ID
				 		INNER JOIN subtitle t ON e.id=t.fk_exercise_id
       				    LEFT OUTER JOIN exercise_score s ON e.id=s.fk_exercise_id
       				    LEFT OUTER JOIN exercise_level l ON e.id=l.fk_exercise_id
       			 WHERE e.status = 'Available' AND t.complete = 1 ". $where . " 
			 GROUP BY e.id
			 ORDER BY e.name DESC, e.language DESC";
		$searchResults = $this->db->_multipleSelect($sql);
		
		return $searchResults;
	}

	public function getExerciseById($id = 0){
		if(!$id)
			return;
			
		$sql = "SELECT e.id, 
			       e.title, 
			       e.description, 
			       e.language, 
			       e.tags, 
			       e.source, 
			       e.name, 
			       e.thumbnail_uri as thumbnailUri,
			       e.adding_date as addingDate, 
			       e.duration, 
			       u.name as userName, 
			       avg (suggested_level) as avgDifficulty,
			       e.status, 
			       e.license, 
			       e.reference
			FROM   exercise e INNER JOIN users u ON e.fk_user_id= u.ID
			LEFT OUTER JOIN exercise_score s ON e.id=s.fk_exercise_id
			LEFT OUTER JOIN exercise_level l ON e.id=l.fk_exercise_id
			WHERE (e.id = %d AND e.status='Available')
			GROUP BY e.id";
		$result = $this->db->_singleSelect($sql,$id);
		//if($result)
		//	$result->avgRating = $this->getExerciseAvgBayesianScore($result->id)->avgRating;

		return $result;
	}

	public function getExerciseLocales($exerciseId=0) {
		if(!$exerciseId)
			return false;

		$sql = "SELECT DISTINCT language as locale FROM subtitle
				WHERE fk_exercise_id = %d";

		$results = $this->db->_multipleSelect ( $sql, $exerciseId );

		return $results; // return languages
	}	

	/*
	private function getExerciseAvgScore($exerciseId){

		$sql = "SELECT e.id,
				       avg (suggested_score) as avgRating, 
				       count(suggested_score) as ratingCount
					FROM exercise e LEFT OUTER JOIN exercise_score s ON e.id=s.fk_exercise_id    
					WHERE (e.id = '%d' ) GROUP BY e.id";

		return $this->db->_singleSelect($sql, $exerciseId);
	}

	public function getExerciseAvgBayesianScore($exerciseId = 0){
		if(!$exerciseId)
		return false;


		if(!isset($this->exerciseMinRatingCount)){
			$sql = "SELECT prefValue FROM preferences WHERE (prefName = 'minVideoRatingCount')";

			$result = $this->db->_singleSelect($sql);

			if($result)
			$this->exerciseMinRatingCount = $result->prefValue;
			else
			$this->exerciseMinRatingCount = 0;
		}

		if(!isset($this->exerciseGlobalAvgRating)){
			$this->exerciseGlobalAvgRating = $this->getExercisesGlobalAvgScore();
		}

		$exerciseRatingData = $this->getExerciseAvgScore($exerciseId);

		$exerciseAvgRating = $exerciseRatingData->avgRating;
		$exerciseRatingCount = $exerciseRatingData->ratingCount;

		if ($exerciseRatingCount == 0) $exerciseRatingCount = 1;

		$exerciseBayesianAvg = ($exerciseAvgRating*($exerciseRatingCount/($exerciseRatingCount + $this->exerciseMinRatingCount))) +
		($this->exerciseGlobalAvgRating*($this->exerciseMinRatingCount/($exerciseRatingCount + $this->exerciseMinRatingCount)));

		$exerciseRatingData->avgRating = $exerciseBayesianAvg;

		return $exerciseRatingData;

	}

	private function getExercisesGlobalAvgScore(){
		$sql = "SELECT avg(suggested_score) as globalAvgScore FROM exercise_score ";

		return ($result = $this->db->_singleSelect($sql)) ? $result->globalAvgScore : 0;
	}
	*/


	/**
	 * RESPONSE.PHP
	 */
	public function admSaveResponse($data){
		try{
			$userId = self::$userId;
			if(!$userId)
				return;
			set_time_limit(0);
			$this->_getResourceDirectories();
			$thumbnail = 'default.jpg';

			try{
				$videoPath = $this->conf->red5Path .'/'. $this->responseFolder .'/'. $data->fileIdentifier . '.flv';
				error_log("WHAT IS NOT A FILE ".$videoPath,3,"/tmp/test.log");
				$mediaData = $this->mediaHelper->retrieveMediaInfo($videoPath);
				$duration = $mediaData->duration;

				if($mediaData->hasVideo){
					$snapshot_output = $this->mediaHelper->takeFolderedRandomSnapshots($videoPath, $this->conf->imagePath, $this->conf->posterPath);
					//$thumbnail = 'default.jpg';
				} else {
					//Make a folder with the same hash as the audio-only response and link to the parent folder's nothumb.png
					$thumbPath = $this->conf->imagePath . '/' . $data->fileIdentifier;
					if(!is_dir($thumbPath)){
						if(!mkdir($thumbPath))
							throw new Exception("You don't have enough permissions to create the thumbail folder: ".$thumbPath."\n");
						if(!is_writable($thumbPath))
							throw new Exception("You don't have enough permissions to write to the thumbnail folder: ".$thumbPath."\n");
						if( !symlink($this->conf->imagePath.'/nothumb.png', $thumbPath.'/default.jpg')  )
							throw new Exception ("Couldn't create link for the thumbnail\n");
					} else {
						throw new Exception("A directory with this name already exists: ".$thumbPath."\n");
					}
				}
			} catch (Exception $e){
				throw new Exception($e->getMessage());
			}


			$insert = "INSERT INTO response (fk_user_id, fk_exercise_id, file_identifier, is_private, thumbnail_uri, source, duration, adding_date, rating_amount, character_name, fk_subtitle_id) ";
			$insert = $insert . "VALUES ('%d', '%d', '%s', 1, '%s', '%s', '%s', now(), 1, '%s', %d ) ";

			$result = $this->db->_insert($insert, $userId , $data->exerciseId, $data->fileIdentifier, $thumbnail, $data->source, $duration, $data->characterName, $data->subtitleId );
			if($result){
				$r = new stdClass();
				$r->responseId = $result;
				if($thumbnail == 'default.jpg')
				$r->responseThumbnail = 'http://' . $_SERVER['HTTP_HOST'] . '/resources/images/thumbs/' . $data->fileIdentifier . '/default.jpg';
				else
				$r->responseThumbnail = 'http://' . $_SERVER['HTTP_HOST'] . '/resources/images/thumbs/nothumb.png';
				$r->responseFileIdentifier = $data->fileIdentifier;
				$this->linkToPlaceholderVideo($r->responseFileIdentifier);
				return $r;
			} else {
				return false;
			}
		}catch(Exception $e){
			throw new Exception($e->getMessage());
		}

	}

	private function _getResourceDirectories(){
		$sql = "SELECT prefValue
			FROM preferences
			WHERE (prefName='exerciseFolder' OR prefName='responseFolder' OR prefName='evaluationFolder') 
			ORDER BY prefName";
		$result = $this->db->_multipleSelect($sql);
		if($result){
			$this->evaluationFolder = $result[0] ? $result[0]->prefValue : '';
			$this->exerciseFolder = $result[1] ? $result[1]->prefValue : '';
			$this->responseFolder = $result[2] ? $result[2]->prefValue : '';
		}
	}

	private function linkToPlaceholderVideo($responseIdentifier){
		$this->_getResourceDirectories();
		$linkName = $this->conf->red5Path . '/' . $this->responseFolder . '/' . $responseIdentifier . '_merge.flv';
		$target = $this->conf->red5Path . '/placeholder_merge.flv';

		if(!is_readable($target) || !is_writable($this->conf->red5Path . '/'. $this->responseFolder) )
		throw new Exception("You don't have permissions to read/write in the specified folders");

		if( !symlink($target, $linkName)  )
		throw new Exception ("Couldn't create a link for that target");
	}

	public function admGetResponseById($responseId){
		try{
			$sql = "SELECT r.file_identifier as responseName, 
				       r.character_name as responseRole, 
				       r.fk_subtitle_id as subtitleId, 
				       r.thumbnail_uri as responseThumbnailUri,
				       e.id as exerciseId,
				       e.name as exerciseName, 
				       e.duration, 
				       e.thumbnail_uri as exerciseThumbnailUri, 
				       e.title 
				FROM response r INNER JOIN exercise e ON r.fk_exercise_id = e.id
				WHERE (e.status='Available' AND r.id = '%d')";	
			//new SessionValidation(true);
			$result = $this->db->_singleSelect($sql, $responseId);
			return $result;
		} catch(Exception $e){
			throw new Exception($e->getMessage());
		}
	}


}
?>
