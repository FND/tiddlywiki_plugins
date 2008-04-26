//{{{
var password = modes["password"]; // DEBUG: example
delete modes["password"]; // DEBUG: example
modes["password"] = "..."; // DEBUG: example
modes["password"] = value;  // DEBUG: example

loadSlices = function(){
	var modelines = store.getTiddlerText(this.title).split("\n");
	for (i=1; i<contents.length; i++){
		var modeparts = modelines[i].split("|");
		modes[modeparts[1]] = modeparts[2];
	}
};

saveSlices = function(modes) { // modes = data in JSON, loaded by the previous function and modified by the plugin
	var rows = [];
	for (var n in modes){
		rows.push["|" +n+ "|" + modes[n] + "|"];
	}
	var table = rows.join("\n");
	// save tiddler.table is your new tiddler text
};

function addMode(name, val) {
	modes[name] = val;
	saveSlices(modes);
}

function removeMode(name) {
	delete modes[name];
	saveSlices(modes);
}

//}}}