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
		while($row = mysql_fetch_array($results, MYSQL_ASSOC)) // DEBUG: use fetch_object instead
			array_push($rows, $row);
		return $rows;
	}

	function insertDB($query) {
		$result = mysql_query($query)
			or die("SQL Error: " . $query . " " . mysql_error());
		return $result;
	}

	function updateFieldValue($table, $field, $value, $selector, $match) { // DEBUG: unused
		$result = mysql_query("UPDATE $table SET $field = '$value' WHERE $selector = $match")
			or die("SQL Error: " . mysql_error());
		return $result;
	}
}

?>
