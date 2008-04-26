<?php

echo "<pre>";
processRepositories();
echo "</pre>";

/*
** repository handling
*/

function processRepositories() {
	$repositories = array("dummyTiddlyWiki.html"/*, "http://www.tiddlywiki.com/coreplugins.html"*/); // DEBUG: to be read from database
	foreach($repositories as $repo) {
		$contents = file_get_contents($repo);
		// DEBUG: error handling
		// DEBUG: check type (TiddlyWiki vs. directory vs. file) 
		processTiddlyWiki($contents);
	}
}

/*
** tiddler retrieval
*/

function processTiddlyWiki($str) {
	$str = str_replace("xmlns=", "ns=", $str); // workaround for default-namespace bug
	$xml = new SimpleXMLElement($str); // DEBUG: errors for HTML entities (CDATA issue!?)
	$version = getVersion($xml);
	if(floatval($version[0] . "." . $version[1]) < 2.2)
		processPluginTiddlers($xml, true);
	else
		processPluginTiddlers($xml, false);
}

function getVersion($xml) {
	$version = $xml->xpath("/html/head/script");
	preg_match("/major: (\d), minor: (\d), revision: (\d)/", $version[0], $matches);
	$major = intval($matches[1]);
	$minor = intval($matches[2]);
	$revision = intval($matches[3]);
	if($major + $minor + $revision > 0) // DEBUG: dirty hack?
		return array($major, $minor, $revision);
	else
		return null;
}

function processPluginTiddlers($xml, $oldStoreFormat = false) {
	// DEBUG: use of strval() for SimpleXML value retrieval hacky!?
	$filter = "//div[@id='storeArea']/div[contains(@tags, 'systemConfig')]";
	$tiddlers = $xml->xpath($filter);
	foreach($tiddlers as $tiddler) {
		// initialize tiddler object -- DEBUG: correct? required?
		$t = new stdClass;
		$t->fields = new stdClass;
		// retrieve tiddler fields
		foreach($tiddler->attributes() as $field) {
			switch($field->getName()) {
				case "title":
					$t->title = strval($field);
					break;
				case "tags":
					$t->tags = strval($field);
					break;
				case "created":
					$t->created = strval($field);
					break;
				case "modified":
					$t->modified = strval($field);
					break;
				case "modifier":
					$t->modifier = strval($field);
					break;
				default: // extended fields
					$t->fields->{$field->getName()} = strval($field);
					break;
			}
		}
		// retrieve tiddler text
		if(!$oldStoreFormat) // v2.2+
			$t->text = strval($tiddler->pre);
		else
			$t->text = strval($tiddler);
		// retrieve slices
		getSlices($t);
		// process plugin
		processPlugin($t);
	}
}

function getSlices(&$tiddler) {
	// DEBUG: to do
}

/*
** tiddler integration
*/

function processPlugin($tiddler) {
	print_r($tiddler); // DEBUG
}
?>
