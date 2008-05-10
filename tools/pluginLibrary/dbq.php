<?php

class dbq {
	function connectToDB($host = "localhost", $user = "root", $pass = "", $db = "pluginLibrary") {
		$conn = mysql_connect($host, $user, $pass)
			or die(mysql_error())
			or die("Could not connect: " . mysql_error());
		//echo "connected to MySQL server " . $conn . "\n"; // DEBUG'd
		$db = mysql_select_db($db, $conn)
			or die(mysql_error())
			or die("Could not connect to " . $db . " " . mysql_error());
		//echo "connected to database " . $db . "\n"; // DEBUG'd
		return $db;
	}

	function queryDB($query) {
		$results = mysql_query($query)
			or die("SQL Error: " . $query . " " . mysql_error());
		$rows = array();
		while($row = mysql_fetch_array($results, MYSQL_ASSOC)) { // DEBUG: use fetch_object instead
			array_push($rows, $row);
		}
		return $rows;
	}

	function insertDB($query) {
		$result = mysql_query($query)
			or die("SQL Error: " . $query . " " . mysql_error());
		return $result;
	}

	function updateFieldValue($table, $field, $value, $selector, $match) { // DEBUG: unused
		$result = mysql_query("UPDATE `$table` SET `$field` = '$value' WHERE `$selector` = $match")
			or die("SQL Error: " . mysql_error());
		return $result;
	}
}

?>

<?php

$sql = new dbq();
$sql->connectToDB();
$out = $sql->updateFieldValue("plugins", "modifier", "foo", "ID", "4");
print_r($out); // DEBUG

?>

<?php

$query = <<<EOT
INSERT INTO `pluginLibrary`.`plugins` (
	`ID` ,
	`name` ,
	`repository_ID` ,
	`available` ,
	`title` ,
	`text` ,
	`created` ,
	`modified` ,
	`modifier` ,
	`updated` ,
	`documentation` ,
	`views` ,
	`annotation`
)
VALUES (
	NULL ,
	'SamplePlugin',
	'1',
	'1',
	'SamplePlugin',
	'|''''Name:''''|SamplePlugin|
foo bar baz
lorem ipsum dolor sit amet',
	'2008-05-10',
	'2008-05-10',
	'FND',
	'2008-05-10',
	'lorem ipsum dolor sit amet',
	'0',
	NULL
);
EOT;

$query_update = <<<EOT
UPDATE `pluginLibrary`.`plugins` SET
	`name` = 'Foo',
	`available` = '0',
	`title` = 'foo',
	`text` = 'foo',
	`created` = '2008-05-11',
	`modified` = '2008-05-11',
	`modifier` = 'bar',
	`updated` = '2008-05-11',
	`documentation` = 'foo'
	WHERE `plugins`.`ID` = 4 LIMIT 1 ;
EOT;

$sql = new dbq();
$sql->connectToDB();
$out = $sql->insertDB($query);
print_r($out); // DEBUG

?>

<!--
<?php

echo "<pre>"; // DEBUG
$sql = new dbq();
$sql->connectToDB();
$out = $sql->queryDB("SELECT * FROM `repositories`");
print_r($out);
echo "</pre>"; // DEBUG

?>
-->
