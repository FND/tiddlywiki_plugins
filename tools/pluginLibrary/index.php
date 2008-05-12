<?php

require "dbq.php";

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
print_r($log); // DEBUG


/*
** repository handling
*/

function processRepositories() {
	global $currentRepository;
	$repositories = getRepositories();
	//print_r($repositories); // DEBUG
	foreach($repositories as $repo) {
		// DEBUG: set all of this repo's plugins availability to false
		$contents = file_get_contents($repo["URI"]); // DEBUG: missing error handling?
		// set current repository
		$currentRepository = new stdClass;
		$currentRepository->URI = $repo["URI"];
		$currentRepository->ID = $repo["ID"];
		// document type handling
		if($repo["type"] == "TiddlyWiki") // TidldyWiki document
			processTiddlyWiki($contents);
		elseif($repo["type"] == "SVN") // Subversion directory
			echo $repo["type"] . "\n"; // DEBUG: to be implemented
		elseif($repo["type"] == "file") // JavaScript file
			echo $repo["type"] . "\n"; // DEBUG: to be implemented
		else
			addLog("ERROR: failed to process repository " . $repo->url);
		$currentRepository = null; // DEBUG: obsolete?
	}
}

function getRepositories() {
	$sql = new dbq();
	$sql->connectToDB();
	$repositories = $sql->queryDB("SELECT * FROM repositories");
	//print_r($repositories); // DEBUG
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
	global $currentRepository;
	// DEBUG: use of strval() for SimpleXML value retrieval hacky!?
	$filter = "//div[@id='storeArea']/div[contains(@tags, 'systemConfig')]";
	$tiddlers = $xml->xpath($filter);
	foreach($tiddlers as $tiddler) {
		// initialize tiddler object -- DEBUG: correct? required?
		$t = new stdClass;
		$t->fields = new stdClass;
		// set repository
		$t->repository = $currentRepository->URI;
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
		if(isset($t->slices->name))
			$t->title = $t->slices->name;
		$source = $t->slices->source;
		if(!$source || $source && !(strpos($source, $currentRepository->URI) === false)) // DEBUG: www handling (e.g. http://foo.bar = http://www.foo.bar)
			storePlugin($t);
		else
			addLog("skipped tiddler " . $t->title . " in repository #" . $currentRepository->ID);
	}
}

function getSlices($text) {
	$pattern = "/(?:[\'\/]*~?([\.\w]+)[\'\/]*\:[\'\/]*\s*(.*?)\s*$)|(?:\|[\'\/]*~?([\.\w]+)\:?[\'\/]*\|\s*(.*?)\s*\|)/m"; // RegEx origin: TiddlyWiki core
	$slices = new stdClass;
	preg_match_all($pattern, $text, $matches);
	$m = $matches[0];
	if($m) {
		for($i = 0; $i < count($m); $i++) {
			if($matches[1][$i]) // colon notation
				$slices->{strtolower($matches[1][$i])} = $matches[2][$i];
			else // table notation
				$slices->{strtolower($matches[3][$i])} = $matches[4][$i];
		}
	}
	return $slices;
}

/*
** tiddler integration
*/

function storePlugin($tiddler) {
	global $currentRepository;
	$pluginID = pluginExists($currentRepository->ID, $tiddler->title);
	if($pluginID)
		updatePlugin($tiddler, $pluginID);
	else
		addPlugin($tiddler);
}

function addPlugin($tiddler) {
	global $currentRepository;
	echo "adding plugin " . $tiddler->title . "\n"; // DEBUG
	$query = "INSERT INTO pluginLibrary.plugins ("
		. "ID,"
		. "repository_ID,"
		. "available,"
		. "title,"
		. "text,"
		. "created,"
		. "modified,"
		. "modifier,"
		. "updated,"
		. "documentation,"
		. "views,"
		. "annotation"
		. ") "
		. "VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')";
	$query = sprintf($query,
		"NULL", // auto-increment
		$currentRepository->ID,
		1,
		$tiddler->title,
		$tiddler->text,
		$tiddler->created, // DEBUG: date format conversion
		$tiddler->modified, // DEBUG: date format conversion
		$tiddler->modifier,
		date("Y-m-d H:i:s"), // DEBUG: to do
		$tiddler->documentation, // DEBUG: to do
		0,
		"NULL"
	);
	$sql = new dbq();
	$sql->connectToDB();
	$out = $sql->insertDB($query);
	echo "\n\n\n\n"; // DEBUG
	print_r($out); // DEBUG
	echo "\n\n\n\n"; // DEBUG
}

function updatePlugin($tiddler, $pluginID) {
	global $currentRepository;
	echo "updating plugin " . $tiddler->title; // DEBUG
	$query = "UPDATE pluginLibrary.plugins SET "
		. "repository_ID = '%s',"
		. "available = '%s',"
		. "title = '%s',"
		. "text = '%s',"
		. "created = '%s',"
		. "modified = '%s',"
		. "modifier = '%s',"
		. "updated = '%s',"
		. "documentation = '%s'"
		. "WHERE plugins.ID = '%s' LIMIT 1";
	$query = sprintf($query,
		$currentRepository->ID,
		1,
		$tiddler->title,
		$tiddler->text,
		$tiddler->created, // DEBUG: date format conversion
		$tiddler->modified, // DEBUG: date format conversion
		$tiddler->modifier,
		date("Y-m-d H:i:s"), // DEBUG: to do
		$tiddler->documentation, // DEBUG: to do
		$pluginID
	);
	$sql = new dbq();
	$sql->connectToDB();
	$out = $sql->insertDB($query);
	echo "\n\n\n\n"; // DEBUG
	print_r($out); // DEBUG
	echo "\n\n\n\n"; // DEBUG
}

function pluginExists($repoID, $pluginName) {
	$sql = new dbq();
	$sql->connectToDB();
	echo $repoID . "\n" . $pluginName . "\n\n";
	$results = $sql->queryDB("SELECT * FROM plugins
		WHERE repository_ID = '$repoID' AND title = '$pluginName'");
	if(sizeof($results) > 0)
		return $results[0]["ID"];
	else
		return false;
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
