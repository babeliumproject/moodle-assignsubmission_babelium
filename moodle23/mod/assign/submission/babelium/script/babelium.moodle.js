
//Global scope objects
var bpExercises = null;
var exerciseInfo = null;
var exerciseSubs = null;
var responseInfo = null;
var respponseSubs = null;

function init(babeliumDomain, locale, exInfo, exSubs, rInfo, rSubs){
	
	if(exInfo && exSubs){
		exerciseInfo = exInfo;
		exerciseSubs = exSubs;
	}
	if(rInfo && rSubs){
		responseInfo = rInfo;
		responseSubs = rSubs;
	}
	
	//Load flash object
	flashLoader(babeliumDomain,locale,"");
}

//This function is top-level until finding a way to route ExternalInterface.call() to object encapsulation
function onConnectionReady(playerid){
	var bpPlayer = document.getElementById(playerid);
	if (!bpPlayer) {
		Alert("There was a problem while loading the video player.");
		return;
	}
	//console.log("Streaming connection established.");
	bpExercises = new exercise();
	if(exerciseInfo && exerciseSubs)
		bpExercises.loadExerciseStatic(bpPlayer, exerciseInfo, exerciseSubs);
	if(responseInfo && responseSubs)
		bpExercises.loadResponseStatic(bpPlayer, responseInfo, responseSubs);
}


function flashLoader(babeliumDomain, locale, jsCallbackObj){
	// For version detection, set to min. required Flash Player version, or 0 (or 0.0.0), for no version detection. 
	var swfVersionStr = "10.2.0";
	// To use express install, set to playerProductInstall.swf, otherwise the empty string. 
	var xiSwfUrlStr = "http://"+babeliumDomain+"/playerProductInstall.swf";
	var flashvars = {};
	flashvars.locale = locale; //Overrides the default locale thats established via system info, to the specified locale
	flashvars.jsCallbackObj = jsCallbackObj; //Where to point the ExternalInterface.call() methods to avoid global scope methods
	var params = {};
	params.quality = "high";
	params.bgcolor = "#ffffff";
	params.allowscriptaccess = "always"; //The swf file is stored in a different domain
	params.allowfullscreen = "true";
	params.wmode = "window";
	var attributes = {};
	attributes.id = "babeliumPlayer";
	attributes.name = "babeliumPlayer";
	attributes.align = "middle";
	swfobject.embedSWF("http://"+babeliumDomain+"/babeliumPlayer.swf", "flashContent", "640", "380", swfVersionStr, xiSwfUrlStr, flashvars, params, attributes);
	// JavaScript enabled so display the flashContent div in case it is not replaced with a swf object.
	swfobject.createCSS("#flashContent", "display:block;text-align:left;");
};


function cuePointManager(){
	
	this.cpm_cuelist=[];//new ArrayCollection();
	this.cpm_exerciseId=-1;
	this.cpm_subtitleId=-1;
	
	this.roleColors = [0xffffff, 0xfffd22, 0x69fc00, 0xfd7200, 0x056cf9, 0xff0f0b, 0xc314c9, 0xff6be5];
	this.colorDictionary = [];

	this.threshold = 0.08; //the amount of looseness we allow in the time polling

	// http://stackoverflow.com/questions/4818615/using-getjson-with-callback-within-a-javascript-object
	var instance = this;
	
	this.reset = function(){
		cpm_exerciseId=-1;
		cpm_subtitleId=-1;
		cpm_cuelist = [];
	};
	

	this.setVideo = function(videoId){
		this.cpm_exerciseId=videoId;
	};
	

	this.currentSubtitle = function(){
		return this.cpm_subtitleId;
	};
	
	this.addCue = function(cueobj){
		this.cpm_cuelist.push(cueobj);
		this.cpm_cuelist.sort(this.sortByStartTime);
	};

	this.setCueAt = function(cueobj, pos){
		this.cpm_cuelist.setItemAt(cueobj, pos);
	};

	this.getCueAt = function(pos){
		return this.cpm_cuelist[pos];
	};

	this.removeCueAt = function(pos){
		return this.cpm_cuelist.removeItemAt(pos);
	};

	this.getCueIndex = function(cueobj){
		return this.cpm_cuelist.getItemIndex(cueobj);
	};

	this.removeAllCue = function(){
		this.cpm_cuelist = [];
	};

	this.setCueList = function (cuelist){
		this.cpm_cuelist=cuelist;
	};
	
	this.getCuelist = function(){
		return this.cpm_cuelist;
	};
	
	this.sortByStartTime = function(a,b){
		if (a.startTime > b.startTime) return 1;
		if (a.startTime < b.startTime) return -1;
		return 0;
	};
	
	this.sortByEndTime = function(a,b){
		if (a.endTime > b.endTime) return 1;
		if (a.endTime < b.endTime) return -1;
		return 0;
	};

	this.setCueListStartCommand = function(command){
		for (var i in this.cpm_cuelist){
			this.cpm_cuelist[i].setStartCommand(command);
		}
	};

	this.setCueListEndCommand = function(command){	
		for (var i in this.cpm_cuelist){
			this.cpm_cuelist[i].setEndCommand(command);
		}
	};

	/**
	 * ActionScript callback function - OnEnterFrame
	 *
	 **/
	this.monitorCuePoints = function(time){
		var curTime=time;
		for (var i in this.cpm_cuelist){
			if (((curTime - this.threshold) < this.cpm_cuelist[i].startTime && this.cpm_cuelist[i].startTime < (curTime + this.threshold))){
				this.cpm_cuelist[i].executeStartCommand();
				break;
			}
			if (((curTime - this.threshold) < this.cpm_cuelist[i].endTime && this.cpm_cuelist[i].endTime < (curTime + this.threshold))){
				this.cpm_cuelist[i].executeEndCommand();
				break;
			}
		}
	};

	this.addCueFromSubtitleLine = function(subline){
		var found = false;
		var color = this.roleColors[0];
		for(var i=0; i < this.colorDictionary.length; i++){
			if(this.colorDictionary[i] == subline.exerciseRoleId){
				found = true;
				color = this.roleColors[i];
				break;
			}
		}
		if(!found){
			this.colorDictionary.push(subline.exerciseRoleId);
			color = this.roleColors[this.colorDictionary.length-1];
		}
		
		var cueObj=new cueObject(subline.subtitleId, subline.showTime, subline.hideTime, subline.text, subline.exerciseRoleId, subline.exerciseRoleName,null,null,color);
		this.addCue(cueObj);
	};

	/**
	 * Return cuepoint list in array mode with startTime and role
	 **/
	this.cues2rolearray = function(){
		var arrows = [];
		var cuelist = this.getCuelist();
		for (var i in cuelist)
			arrows.push({'startTime': cuelist[i].startTime, 'endTime': cuelist[i].endTime, 'role': cuelist[i].role});

		return arrows;
	};
}


function cueObject(subtitleId, startTime, endTime, text, roleId, role, startCommand, endCommand, textColor)
{
	this.defaultParamValues = [0,-1,-1,null,0,null,null,null,0xffffff];
	
	this.subtitleId=subtitleId;
	this.startTime=startTime;
	this.endTime=endTime;
	this.text=text;
	this.roleId=roleId;
	this.role=role;
	this.startCommand=startCommand;
	this.endCommand=endCommand;
	if(textColor == null)
		this.textColor=defaultParamValues[9];
	else
		this.textColor=textColor;
		
	this.executeStartCommand = function(){
		this.startCommand.execute();
	};
		
	this.executeEndCommand = function(){
		this.endCommand.execute();
	};
		
	this.setStartCommand = function(command){
		this.startCommand = command;
	};
		
	this.setEndCommand = function(command){
		this.endCommand = command;
	};

}


function onPlaybackCuePoint(cue, videoPlayer, dg)
{
	//Retrieve the videoPlayer object using DOM
	this.VP=videoPlayer;
	//This object should reflect a DataGrid of ActionScript or an HTML table
	//this.dg=dg;
	this.cue=cue;

	this.execute = function(){
		if (this.cue){
			this.VP.setSubtitle(this.cue.text,this.cue.textColor);
			//console.log("Show subtitle");
			//var index:int = CuePointManager.getInstance().getCueIndex(cue);
			//if(dg != null && dg.rowCount > index)
			//	dg.selectedIndex = index;
		} else {
			this.VP.setSubtitle('',0x000000);
			//console.log("Stop Other Role/Hide subtitle");
		}
	};
}


function onRecordingOtherRoleCuePoint(cue, VP)
{
	//Retrieve the videoPlayer object using DOM
	this.VP = VP;
	this.cue = cue;

	this.execute = function()
	{
		var time = this.cue.endTime - this.cue.startTime;
		this.VP.setSubtitle(this.cue.text, this.cue.textColor);
		this.VP.startTalking(this.cue.role, time);
		this.VP.highlight(false);
		//console.log("Start Other Role");
	};
}


function onRecordingSelectedRoleStartCuePoint(cue, VP){
	
	this.VP=VP;
	this.cue = cue;

	this.execute = function(){
		var time= this.cue.endTime - this.cue.startTime;
		this.VP.muteVideo(true);
		this.VP.muteRecording(false);
		this.VP.setSubtitle(this.cue.text, this.cue.textColor);
		this.VP.startTalking(this.cue.role, time);
		this.VP.highlight(true);
		//console.log("Start Recording your role");
	};
}


function onRecordingSelectedRoleStopCuePoint(VP){
	this.VP=VP;

	this.execute = function(){
		this.VP.muteRecording(true);
		this.VP.muteVideo(false);
		this.VP.setSubtitle('',0x000000);
		this.VP.highlight(false);
		//console.log("Stop Recording your role");
	};
}


function onReplayRecordingCuePoint(cue, subHolder)
{
	this.VP = subHolder;
	this.cue = cue;

	this.execute = function(){
		if(this.cue){
			this.VP.setSubtitle(this.cue.text, this.cue.textColor);
			var time = this.cue.endTime - this.cue.startTime;
			this.VP.startTalking(this.cue.role, time);
		} else
			this.VP.setSubtitle('',0x000000);
	};	
}


function exercise() {

	// http://stackoverflow.com/questions/4818615/using-getjson-with-callback-within-a-javascript-object	
	var instance = this;

	this.bpPlayer = null;
	this.cueManager = null;

	this.bpPlayerStates = {
		PLAY_STATE : 0,
		PLAY_BOTH_STATE : 1,
		RECORD_MIC_STATE : 2,
		RECORD_BOTH_STATE : 3
	};

	this.currentMedia = null;

	this.cueManagerReady = false;

	this.locales = [];
	this.roles = []; //Maps a role name with its role ID

	this.selectedRole = null;
	this.selectedLocale = null;
	this.recordedFilename = null;

	this.loadExerciseStatic = function(videoPlayer, mediaInfo, subtitleInfo){
		this.bpPlayer = videoPlayer;
		// bpPlayer.addEventListener('onVideoStartedPlaying','videoStartedPlayingListener');
		this.bpPlayer.addEventListener('onRecordingAborted','bpExercises.recordingAbortedListener');
		this.bpPlayer.addEventListener('onRecordingFinished','bpExercises.recordingFinishedListener');
		
		//onExerciseSelected part
		this.exerciseName = mediaInfo.exerciseName;
		this.exerciseTitle = mediaInfo.exerciseTitle;
		this.exerciseId = mediaInfo.exerciseId;
		this.recordedFilename = mediaInfo.responseName;
		this.selectedRole = mediaInfo.responseRole;
		this.currentMedia = mediaInfo;
	
		this.cueManagerReady = false;
		this.cueManager = new cuePointManager();
		for(var i in exerciseSubs) {
			this.cueManager.addCueFromSubtitleLine(subtitleInfo[i]);
		}

		this.bpPlayer.stopVideo();
		this.bpPlayer.setState(this.bpPlayerStates.PLAY_STATE);
		if(this.recordedFilename && this.recordedFilename.length > 0){
			this.bpPlayer.responseSource(this.recordedFilename + '_merge');
		}else{
			this.bpPlayer.exerciseSource(this.exerciseName);
		}

		this.bpPlayer.removeEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
		this.bpPlayer.addEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
		this.setupPlayCommands();		
	};

	this.loadResponseStatic = function(videoPlayer, mediaInfo, subtitleInfo){
		this.bpPlayer = videoPlayer;
		// bpPlayer.addEventListener('onVideoStartedPlaying','videoStartedPlayingListener');
		this.bpPlayer.addEventListener('onRecordingAborted','bpExercises.recordingAbortedListener');
		this.bpPlayer.addEventListener('onRecordingFinished','bpExercises.recordingFinishedListener');
		
		//onExerciseSelected part
		this.exerciseName = mediaInfo.exerciseName;
		this.exerciseTitle = mediaInfo.exerciseTitle;
		this.exerciseId = mediaInfo.exerciseId;
		this.recordedFilename = mediaInfo.responseName;
		this.selectedRole = mediaInfo.responseRole;
		this.currentMedia = mediaInfo;

		this.cueManagerReady = false;
		this.cueManager = new cuePointManager();
		for(var i in subtitleInfo) {
			this.cueManager.addCueFromSubtitleLine(subtitleInfo[i]);
		}
		this.bpPlayer.addEventListener('onEnterFrame', 'bpExercises.enterFrameListener');	
		this.setupRecordingCommands();
		this.bpPlayer.exerciseSource(this.exerciseName);
		this.bpPlayer.setState(this.bpPlayerStates.PLAY_BOTH_STATE);
		this.bpPlayer.secondSource(this.recordedFilename);
		this.bpPlayer.seek(false);
		//Delay the call to show arrows until the metadata of the video is acquired
		this.bpPlayer.addEventListener('onMetadataRetrieved', 'bpExercises.onMetadataRetrieved');
		//this.showArrows();	
	}	

	this.loadExercise = function(videoPlayer, ex) {
		this.bpPlayer = videoPlayer;
		this.cueManager = new cuePointManager();
		this.setupVideoPlayer();
		this.onExerciseSelected(ex);
	};

	this.loadResponse = function(videoPlayer, resp){
		this.bpPlayer = videoPlayer;
		this.cueManager = new cuePointManager();
		this.onResponseSelected(resp);
	};	

	this.setupVideoPlayer = function() {
		// bpPlayer.addEventListener('onVideoPlayerReady','videoPlayerReadyListener');
		// bpPlayer.addEventListener('onVideoStartedPlaying','videoStartedPlayingListener');
		this.bpPlayer.addEventListener('onRecordingAborted','bpExercises.recordingAbortedListener');
		this.bpPlayer.addEventListener('onRecordingFinished','bpExercises.recordingFinishedListener');
	};

	this.onExerciseSelected = function(exercise) {
		// Store selected exercise's information
		this.exerciseName = exercise.name;
		this.exerciseTitle = exercise.title;
		this.exerciseId = exercise.id;
		this.currentExercise = exercise;
		this.cueManagerReady = false;

		this.prepareExercise();
		this.resetCueManager();
	};

	this.onResponseSelected = function(response) {
		this.currentResponse = response;
		console.log(this.currentResponse);
		this.cueManagerReady = false;
		this.resetCueManager();
		this.prepareResponse();
	};

	this.prepareExercise = function() {
		this.bpPlayer.stopVideo();
		this.bpPlayer.setState(this.bpPlayerStates.PLAY_STATE);
		this.bpPlayer.exerciseSource(this.exerciseName);
	};

	this.prepareResponse = function() {
		
		this.prepareCueManagerEvaluation();
	};



	this.resetCueManager = function() {
		this.cueManager.reset();
		this.bpPlayer.removeEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
	};

	this.prepareCueManager = function() {
		//this.cueManager.setVideo(this.exerciseId);
		//this.selectedLocale = $('#localeCombo option:selected').text();
		this.bpPlayer.removeEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
		this.bpPlayer.addEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
	};

	this.prepareCueManagerEvaluation = function(){

		this.bpPlayer.removeEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
		this.bpPlayer.addEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
	};


	/**
	 * ActionScript callback
	 * It is called each time the flash object refreshes its display list (around 20 times per second on average computers with standard cpu usage)  
         */
	this.enterFrameListener = function(event) {
		this.cueManager.monitorCuePoints(event);
	};

	/**
	 * Callback from another scope, use the 'instance' variable to access local properties/methods
	 */
	this.onSubtitlesRetrieved = function() {
		if(instance.currentResponse == undefined)
			instance.setupPlayCommands();
		else{
			//instance.bpPlayer.setState(instance.bpPlayerStates.PLAY_BOTH_STATE);
			instance.bpPlayer.setState(instance.bpPlayerStates.PLAY_STATE);
			instance.bpPlayer.responseSource(instance.currentResponse.file_identifier + '_merge');
			//instance.bpPlayer.secondSource(instance.currentResponse.file_identifier);
			//instance.selectedRole = instance.currentResponse.character_name;
			instance.setupPlayCommands();
			//instance.setupRecordingCommands();
			//instance.showArrows();
			instance.bpPlayer.addEventListener('onMetadataRetrieved', 'bpExercises.onMetadataRetrieved');
		}
	};

	this.onMetadataRetrieved = function(event) {
		if(this.bpPlayer.getState() == this.bpPlayerStates.PLAY_BOTH_STATE)
			this.showArrows();
	};

	this.setupPlayCommands = function() {
		var auxList = this.cueManager.getCuelist();
		if (auxList.length <= 0)
			return;

		for ( var i in auxList) {
			auxList[i].setStartCommand(new onPlaybackCuePoint(auxList[i],this.bpPlayer));
			auxList[i].setEndCommand(new onPlaybackCuePoint(null, this.bpPlayer));
		}
		this.cueManagerReady = true;

		//TODO statistical feature. To be implemented yet
		//this.videoStartedPlayingListener(null);
	};

	this.setupReplayCommands = function() {
		var auxList = this.cueManager.getCuelist();

		if (auxList.length <= 0)
			return;

		for ( var i in auxList) {
			auxList[i].setStartCommand(new onReplayRecordingCuePoint(auxList[i], this.bpPlayer));
			auxList[i].setEndCommand(new onReplayRecordingCuePoint(null, this.bpPlayer));
		}

		this.cueManagerReady = true;
	};

	this.setupRecordingCommands = function() {
		var auxList = this.cueManager.getCuelist();

		if (auxList.length <= 0)
			return;

		for ( var i in auxList) {

			if (auxList[i].role != this.selectedRole) {
				auxList[i].setStartCommand(new onRecordingOtherRoleCuePoint(auxList[i], this.bpPlayer));
				auxList[i].setEndCommand(new onPlaybackCuePoint(null, this.bpPlayer));
			} else {
				auxList[i].setStartCommand(new onRecordingSelectedRoleStartCuePoint(auxList[i], this.bpPlayer));
				auxList[i].setEndCommand(new onRecordingSelectedRoleStopCuePoint(this.bpPlayer));
			}
		}
		this.bpPlayer.seek(false);
		this.cueManagerReady = true;
	};

	/**
	 * ActionScript callback function
	 * The recording process ended successfully. Save the filename of this recording and switch to "Review Recording" state
	 */
	this.recordingFinishedListener = function(recFilename) {
		// Store last recorded response's filename
		this.recordedFilename = recFilename;


		var mform = document.forms['mform1'];
		//mform.elements["responseid"].value = responseId;
		mform.elements["responsehash"].value = recFilename;

		console.log("Response recording ended");

		
		// Set the videoplayer to playback both the exercise and the
		// last response.
		this.setupRecordingCommands();
		this.bpPlayer.exerciseSource(this.exerciseName);
		this.bpPlayer.setState(this.bpPlayerStates.PLAY_BOTH_STATE);
		this.bpPlayer.secondSource(this.recordedFilename);

		this.bpPlayer.seek(false);
		this.bpPlayer.stopVideo();

		$('#id_startStopRecordingBtn').val(M.str.assignsubmission_babelium.babeliumStartRecording);
		//$('#id_viewRecordingExerciseBtn').val(M.str.assignsubmission_babelium.babeliumViewExercise);
		//$('#id_viewRecordingExerciseBtn').show();
		$('#id_viewRecordingBtn').show();
		$('#id_viewExerciseBtn').show();
	};

	/**
	 * ActionScript callback function
	 * The recording process failed for some reason. Display an error and return to the "View Exercise" state
	 */
	this.recordingAbortedListener = function() {
		alert("Devices not working");
		this.recordingError();
		this.prepareExercise();

		this.bpPlayer.addEventListener('onEnterFrame', 'bpExercises.enterFrameListener');	
		this.setupPlayCommands();
			
		$('#id_startStopRecordingBtn').val(M.str.assignsubmission_babelium.babeliumStartRecording);
		if(this.recordedFilename){
			//$('#id_viewRecordingExerciseBtn').val(M.str.assignsubmission_babelium.babeliumViewRecording);
			//$('#id_viewRecordingExerciseBtn').show();
			$('#id_viewRecordingBtn').show();
			$('#id_viewExerciseBtn').show();
		}
	};

	this.recordingError = function() {
		this.hideArrows();
		this.bpPlayer.unattachUserDevices();
		this.bpPlayer.setState(this.bpPlayerStates.PLAY_STATE);

		this.bpPlayer.removeEventListener('onEnterFrame','bpExercises.onEnterFrameListener');
	};

	this.showArrows = function() {
		console.log("showArrows was called")
		this.bpPlayer.arrows(true);
		this.bpPlayer.setArrows(this.cueManager.cues2rolearray(), this.selectedRole);
	};

	this.hideArrows = function() {
		this.bpPlayer.arrows(false);
		this.bpPlayer.removeArrows();
	};

	/**
	 * Dynamic service call
	 * Stores how many times the user played the video during the session
	 */
	/* TODO
	this.videoStartedPlayingListener = function() {
		this.exerciseStartedPlaying = true;
		if (this.cueManagerReady && this.exerciseStartedPlaying) {
			this.exerciseStartedPlaying = false;
			var subtitlesAreUsed = this.bpPlayer.subtitlePanelVisible;
			var subtitleId = this.cueManager.currentSubtitle;
			var parameters = {
				'id' : 0,
				'userSessionId' : 0,
				'exerciseId' : this.exerciseId,
				'responseAttempt' : false,
				'responseId' : 0,
				'incidenceDate' : '',
				'subtitlesAreUsed' : subtitlesAreUsed,
				'subtitleId' : subtitleId,
				'exerciseRoleId' : 0
			};
			if (this.exerciseId > 0 && subtitleId > 0)
				bpServices.send(false, 'watchExercise', parameters, null);
		}
	};
	*/

	/**
	 * Dynamic service call
	 * Stores how many recording attemps the user made during the session
	 */
	/* TODO
	this.statisticRecAttempt = function() {
		var subtitlesAreUsed = this.bpPlayer.subtitlePanelVisible();
		var subtitleId = this.cueManager.currentSubtitle();
		var roleId = 0;
		var roles = this.roles;
		for ( var i in roles) {
			if (roles[i].characterName == this.selectedRole) {
				roleId = roles[i].id;
				break;
			}
		}

		// Ajax call to the appointed REST service
		var parameters = {
			'id' : 0,
			'userSessionId' : 0,
			'exerciseId' : this.exerciseId,
			'responseAttempt' : true,
			'responseId' : 0,
			'incidenceDate' : '',
			'subtitlesAreUsed' : subtitlesAreUsed,
			'subtitleId' : subtitleId,
			'exerciseRoleId' : roleId
		};

		bpServices.send(false,'exerciseAttemptResponse', parameters, function(data){});
	};
	*/
		
	this.startStopRecordingClickHandler = function(){
		
		//We are currently in the middle of a recording process, pressing the button should abort the recording
		if(this.bpPlayer.getState() == this.bpPlayerStates.RECORD_MIC_STATE || this.bpPlayer.getState() == this.bpPlayerStates.RECORD_BOTH_STATE){
			this.recordingError();
			this.prepareExercise();
			this.bpPlayer.addEventListener('onEnterFrame', 'bpExercises.enterFrameListener');	
			this.setupPlayCommands();
			
			$('#id_startStopRecordingBtn').val(M.str.assignsubmission_babelium.babeliumStartRecording);
			if(this.recordedFilename){
				//$('#id_viewRecordingExerciseBtn').val(M.str.assignsubmission_babelium.babeliumViewRecording);
				//$('#id_viewRecordingExerciseBtn').show();
				$('#id_viewRecordingBtn').show();
				$('#id_viewExerciseBtn').show();
			}
		} else {	
			//Determine which role the user is going to impersonate
			this.selectedRole = $('#id_roleCombo option:selected').text();
			var mform = document.forms['mform1'];
			mform.elements["recordedRole"].value = this.selectedRole;
	
			//This is just in case a previous state changed the primary video of the videoplayer
			//instance.bpPlayer.exerciseSource(instance.exerciseName);	
	
			//Prepare the cuepoints for the recording
			this.setupRecordingCommands();

			//Determine which devices the video player should attach
			if ($("input[name=recmethod]:checked").val() == 0) {
				this.bpPlayer.setState(instance.bpPlayerStates.RECORD_MIC_STATE);
			} else {
				this.bpPlayer.setState(instance.bpPlayerStates.RECORD_BOTH_STATE);
			}
		
			//Show the arrows for the selected role
			this.showArrows();

			$('#id_startStopRecordingBtn').val(M.str.assignsubmission_babelium.babeliumStopRecording);
			//$('#id_viewRecordingExerciseBtn').hide();
			$('#id_viewRecordingBtn').hide();
			$('#id_viewExerciseBtn').hide();

			//TODO this is dynamic data saving, we should refactor the services before using it
			//Save statistical data
			//instance.statisticRecAttempt();
		}
	};

	/*
	this.viewRecordingExerciseClickHandler = function(){
		if(this.bpPlayer.getState() == this.bpPlayerStates.PLAY_BOTH_STATE){
			this.recordingError();
			this.prepareExercise();
			this.bpPlayer.addEventListener('onEnterFrame', 'bpExercises.enterFrameListener');	
			this.setupPlayCommands();
			$('#id_viewRecordingExerciseBtn').val(M.str.assignsubmission_babelium.babeliumViewRecording);
		} else {
			this.setupRecordingCommands();
			this.bpPlayer.exerciseSource(instance.exerciseName);
			this.bpPlayer.setState(instance.bpPlayerStates.PLAY_BOTH_STATE);
			this.bpPlayer.secondSource(instance.recordedFilename);
			this.bpPlayer.seek(false);
			this.showArrows();	
			$('#id_viewRecordingExerciseBtn').val(M.str.assignsubmission_babelium.babeliumViewExercise);
		}
	};
	*/
	
	this.viewRecordingClickHandler = function(){
		this.setupRecordingCommands();
		this.bpPlayer.exerciseSource(instance.exerciseName);
		this.bpPlayer.setState(instance.bpPlayerStates.PLAY_BOTH_STATE);
		this.bpPlayer.secondSource(instance.recordedFilename);
		this.bpPlayer.seek(false);
		this.showArrows();
	}
	
	this.viewExerciseClickHandler = function(){
		this.recordingError();
		this.prepareExercise();
		this.bpPlayer.addEventListener('onEnterFrame', 'bpExercises.enterFrameListener');	
		this.setupPlayCommands();
	}

	/*
	this.watchResponseClickHandler = function(){
		instance.showArrows();
		isntance.setupReplayCommands();

		instance.bpPlayer.responseSource(instance.recordedFilename);
		instance.bpPlayer.setState(instance.bpPlayerStates.PLAY_STATE);

		instance.bpPlayer.seek(false);
	};
	*/

	/*
	//This shouldn't be necessary because YUI JS should be linked at the end of DOM's body, but just in case
	Y.on("domready", function() {
		Y.one('#recordingEndOptions').hide();
		Y.one('#localecombo').on('change', instance.subtitleLanguageChangeHandler());
		Y.one('#startRecordingBtn').on('click', instance.startRecordingClickHandler());
		Y.one('#watchExerciseAndResponseBtn').on('click', instance.watchResponseClickHandler());
		Y.one('#recordAgainBtn').on('click', instance.recordAgainClickHandler());
		//Y.one('#abortRecordingBtn').on('click', instance.abortRecordingClickHandler());
		//Y.one('#saveResponseBtn').on('click', instance.saveResponseClickHandler());
	});
	*/

	$(document).ready(function() {
		$('#id_startStopRecordingBtn').click(function(){ instance.startStopRecordingClickHandler(); });
		//$('#id_viewRecordingExerciseBtn').click(function() { instance.viewRecordingExerciseClickHandler(); });
		$('#id_viewRecordingBtn').click(function() { instance.viewRecordingClickHandler(); });
		$('#id_viewExerciseBtn').click(function() { instance.viewExerciseClickHandler(); });
	});
}


function services(){
	this.protocol = 'http://';
	this.host = bpConfig.apiHost;
	this.endpoint = bpConfig.apiEndpoint;
	this.lastRandomizer = '';
	this.statToken = 'myMusicFightsAgainstTheSystemThatTeachesToLiveAndDie'; //Bob Marley's Quote
	this.commToken = '';
	this.authToken = '';
	this.token = '';

	//This variable will be accesible in the callback and points to the right scope
	var instance = this;
	
	/**
	 * The way callback should be passed is uncertain maybe we should pass it as a String and then use eval() to fetch the actual function. Also since this function
	 * is to be nested inside a class we must prepend the class instance name, in this case should be something like "services.theFunction"
	 */
	this.send = function(secured,method,parameters,callback){
		this.protocol = secured ? 'https://' : 'http://';
		var qs = this.protocol + this.host + '/' + this.endpoint + '?' + method;
		var data = {};
		var cb = callback;
		data.method = method;
		if(parameters != null)
			data.parameters = parameters;
		if(cb == null){
			cb = instance.onServiceSuccess; 		
		}
		this.token = this.generateToken(method);
		data.header = {"token":this.token,"session":bpConfig.sessionID,"uuid":bpConfig.uuid};
		// Fix for Internet Explorer 'No Transport' error. This error is apparently caused by an cross-domain request attempt.
		// With the following statetement we force jQuery to support cross-origin resource sharing by default. Solution found at:
		// http://blueonionsoftware.com/blog.aspx?p=03aff202-4198-4606-b9d6-686fd13697ee
		$.support.cors = true;
		$.ajax({
			type: "POST",
			url: qs,
			data: data,
			success: cb,
			dataType: "json",
			//error: function(error){
			//	instance.onServiceError(error);
			//},
			error: function (xhr, status, errorThrown){
				instance.onServiceError(xhr,status,errorThrown);
			},
			xhrFields: {
				withCredentials: true
			},
			crossDomain: true
			
			});	

	};
	
	this.getCommunicationToken = function(){
		method = 'getCommunicationToken';
		this.protocol = 'http://';
		var qs = this.protocol + this.host + this.endpoint + '?' + method;
		
		var data = {};
		data.method = method;
		data.parameters = {'secretKey': hex_md5(bpConfig.sessionID)};
		data.header = {"session":bpConfig.sessionID,"uuid":bpConfig.uuid};
		
	    $.post(qs, data, bpServices.onCommunicationTokenSuccess, "json").error(function(error){ instance.onServiceError(error); });
	};
	
	this.onCommunicationTokenSuccess = function(data){
		//The request to the server was successful, now we should check if the response is right or not
		//Retrieve the communicationToken and store it for future use
		instance.commToken = data.response;
		onCommunicationReady();
	};
	
	this.onServiceSuccess = function(success){
		//Do sth with this data;
	};

	this.onServiceError = function(xhr, status, errorThrown){
		//Display an error message noticing the user that the request to the server was not successful.
		var errorObj = $.parseJSON(xhr.responseText);
		if(!errorObj)
			errorObj = { "response": { "message": errorThrown} };
		console.log("Request error: " + errorObj.response.message);
	};
	
	this.createRandomSalt = function(){
		var randomizer = '';
		var charsGenerated = 0;
		while (charsGenerated < 6){
			randomizer = randomizer + (Math.floor(Math.random() * 16)).toString(16);
			charsGenerated++;
		}
		return randomizer !== this.lastRandomizer ? (randomizer) : (createRandomSalt());
	};

	this.generateToken = function (method){
		var salt = this.createRandomSalt();
		var t = hex_sha1(method + ":" + this.commToken + ":" + this.statToken + ":" + salt);
		var s = salt + t;
		//console.log('Method:' + method + ', CommToken: ' + this.commToken + ', StatToken: ' + this.statToken + ', Salt: '+salt);
		return s;
	};	
}

