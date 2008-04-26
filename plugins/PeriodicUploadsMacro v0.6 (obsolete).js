/***
|''Name''|PeriodicUploadsMacro|
|''Version''|0.6|
|''Status''|@@experimental@@|
|''Author''|FND|
|''Source''|[[FND's DevPad|http://devpad.tiddlyspot.com/#PeriodicUploadsMacro]]|
|''License''|[[Creative Commons Attribution-ShareAlike 3.0 License|http://creativecommons.org/licenses/by-sa/3.0/]]|
|''~CoreVersion''|2.1|
|''Type''|macro|
|''Requires''|UploadPlugin|
|''Overrides''|N/A|
|''Description''|periodically uploads the current TiddlyWiki document using the [[UploadPlugin|http://tiddlywiki.bidix.info/#UploadPlugin]]|
!Options
* automatically start periodic uploads: <<option txtPeriodicUpload>> (interval in seconds; leave blank to deactivate)
!Usage
{{{
<<periodicUpload [interval] [storeUrl] [toFilename] [backupDir] [uploadDir] [username]>>
}}}
!!Example
<<periodicUpload 300 http://www.domain.tld/store.cgi index.html . . username>>
!Revision History
!!v0.5 (2008-02-15)
* initial release
!!v0.6 (2008-02-21)
* added option to trigger periodic uploads on startup
!To Do
* backstage button to stop automatic uploads (required when triggered on startup)
* possible mismatch: button label says start even if timer is active already (e.g. when triggered on startup)
* warning when upload is triggered on startup
!Code
***/
//{{{
config.macros.periodicUpload = {
	optionsDesc: "automatically start periodic uploads (interval in seconds; leave blank to deactivate)",
	btnLabelStart: "Start",
	btnLabelStop: "Stop",
	btnTooltip: "toggle upload timer",
	btnClass: "button"
};

config.macros.periodicUpload.init = function() {
	config.optionsDesc.txtPeriodicUpload = this.optionsDesc;
	if(config.options.txtPeriodicUpload === undefined) {
		config.options.txtPeriodicUpload = "";
	} else if(config.options.txtPeriodicUpload > 0) {
		this.interval = config.options.txtPeriodicUpload * 1000;
		setTimeout("config.macros.periodicUpload.timerToggle()", this.interval);
	}
};

config.macros.periodicUpload.handler = function(place, macroName, params, wikifier, paramString, tiddler) {
	this.interval = params.shift() * 1000;
	this.params = params;
	createTiddlyButton(place, this.btnLabelStart, this.btnTooltip, this.timerToggle, this.btnClass);
};

config.macros.periodicUpload.timerToggle = function() {
	var obj = config.macros.periodicUpload;
	if(!obj.timer) {
		this.innerHTML = obj.btnLabelStop; // DEBUG: use different property!?
		obj.timer = window.setInterval("config.macros.periodicUpload.action()", obj.interval);
	} else {
		window.clearInterval(obj.timer);
		obj.timer = null;
		this.innerHTML = obj.btnLabelStart; // DEBUG: use different property!?
	}
};

config.macros.periodicUpload.action = function() {
	config.macros.upload.action(this.params);
};
//}}}