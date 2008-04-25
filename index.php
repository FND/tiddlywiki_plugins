<?php

echo "<pre>";
processRepositories();
echo "</pre>";

/*
** repository handling
*/

function processRepositories() {
	//$repositories = array("http://www.tiddlywiki.com/coreplugins.html"); // DEBUG: to be read from database
	$repositories = array("dummyStore.html"); // DEBUG
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
	$xml = new SimpleXMLElement($str);
	$version = getVersion($xml);
	if(floatval($version[0] . "." . $version[1]) < 2.2)
		getPluginTiddlers($xml, true);
	else
		getPluginTiddlers($xml, false);
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

function getPluginTiddlers($xml, $oldStoreFormat = false) {
	$filter = "//div[@id='storeArea']/div[contains(@tags, 'systemConfig')]";
	if(!$oldStoreFormat) // v2.2+
		$filter .= "/pre";
	$tiddlers = $xml->xpath($filter);
	// DEBUG: also retrieve tiddler fields (DIV attributes)
	foreach($tiddlers as $tiddler) {
		$t = new stdClass; // DEBUG: correct?
		$t->text = $tiddler;
		processTiddler($t);
	}
}

/*
** tiddler integration
*/

function processTiddler($tiddler) {
		print_r($tiddler); // DEBUG
}
?>
