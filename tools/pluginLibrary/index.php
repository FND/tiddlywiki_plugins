<?php

include "dbq.php";

// initialize debugging variables
$t0 = time();
$log = array();

// start processing
echo "<pre>"; // DEBUG
processRepositories();
echo "</pre>"; // DEBUG

// output debugging info
$t1 = time();
echo "Runtime: " . ($t1 - $t0) . " seconds\n"; // DEBUG
print_r($log);


/*
** repository handling
*/

function processRepositories() {
	$repositories = getRepositories();
	print_r($repositories);
	foreach($repositories as $repo) {
		$contents = file_get_contents($repo["URI"]); // DEBUG: missing error handling?
		if($repo["type"] == "TiddlyWiki") // TidldyWiki document
			processTiddlyWiki($contents);
		elseif($repo["type"] == "SVN") // Subversion directory
			echo $repo["type"] . "\n"; // DEBUG: to be implemented
		elseif($repo["type"] == "file") // JavaScript file
			echo $repo["type"] . "\n"; // DEBUG: to be implemented
		else
			addLog("ERROR: failed to process repository " . $repo->url); // DEBUG: error report
	}
}

function getRepositories() {
	$sql = new dbq();
	$sql->connectToDB();
	$repositories = $sql->queryDB("SELECT * FROM `repositories`");
	return $repositories;
}

/*
** tiddler retrieval
*/

function processTiddlyWiki($str) {
	$str = str_replace("xmlns=", "ns=", $str); // workaround for default-namespace bug
	$xml = @new SimpleXMLElement($str); // DEBUG: errors for HTML entities (CDATA issue!?); suppressing errors hacky?!
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
		// retrieve tiddler text -- DEBUG: strip leading and trailing whitespace?
		if(!$oldStoreFormat) // v2.2+
			$t->text = strval($tiddler->pre);
		else
			$t->text = strval($tiddler);
		// retrieve slices
		$t->slices = getSlices($t->text);
		// process plugin
		processPlugin($t);
	}
}

function getSlices($text) {
	$pattern = "/(?:[\'\/]*~?([\.\w]+)[\'\/]*\:[\'\/]*\s*(.*?)\s*$)|(?:\|[\'\/]*~?([\.\w]+)\:?[\'\/]*\|\s*(.*?)\s*\|)/m"; // RegEx origin: TiddlyWiki core
	$slices = new stdClass;
	preg_match_all($pattern, $text, $matches);
	$m = $matches[0];
	if($m) {
		for($i = 0; $i < count($m); $i++) { // DEBUG: use lowercase labels?
			if($matches[1][$i]) // colon notation
				$slices->$matches[1][$i] = $matches[2][$i];
			else // table notation
				$slices->$matches[3][$i] = $matches[4][$i];
		}
	}
	return $slices;
}

/*
** tiddler integration
*/

function processPlugin($tiddler) {
	print_r($tiddler); // DEBUG
}

/*
** debugging
*/

function addLog($text) {
	global $log;
	$timestamp = date("Y-m-d H:i:s");
	array_push($log, $timestamp . " " . $text);
}

?>
