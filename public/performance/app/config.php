<?php

date_default_timezone_set('America/New_York');

$currentScript = basename($_SERVER["SCRIPT_FILENAME"], '.php');

$config = array(
	"debug"          => false,

/*  A unique key so that only you know how to update the spreadsheet and run the scripts */

	"key"            => "4875KJHGKJHG76547", /* CHANGE THIS!!! DO NOT USE DEFAUL VALUE */

	"location"       => "Dulles_MotoG4:Chrome.3G", // "Dulles:Chrome.LTE",

/*
	Your WebPageTest API key
	https://www.webpagetest.org/getkey.php
*/
	"api-key"        => "WEBPAGETEST-ORG-API-KEY",

/*  The URL that WebPageTest will call and send the results back to */

	"callbackURL"    => "example.com/performance/app/listen.php",

/*  Setup a Google Form and note the ID */

	"form-id"        => "GOOGLE-FORM-ID",

/*  The spreadsheet that the form creates has its own ID */

	"spreadsheet-id" => "GOOGLE-SPREADSHEET-ID",

/*  Every sheet has its own id. You can see this appended in teh URL when you click on the various sheet tabs 

	You'll want to clone/save-a-copy of this Sheet to get teh forumale:
	https://docs.google.com/spreadsheets/d/1c1zhkdvWE0WvG84TT3Czekj0N-0sRUEBKO3c0Aeflxw/edit#gid=0
*/

	"sheets" => array(
		"rankings"     => "0",
		"info"         => "1822629289", /* Change these to match your worksheet */
		"range"        => "1767452737", /* Change these to match your worksheet */
		"pastrankings" => "1314759820"  /* Change these to match your worksheet */
	)
);

function validate_alphanumeric_underscore_id($str)  {
	return preg_match('/^[A-Za-z][A-Za-z0-9]*(?:_[A-Za-z0-9]+)*$/',$str);
}

$id = "";
if (isset($_REQUEST["id"])) {
	$id = validate_alphanumeric_underscore_id($_REQUEST["id"]);
}

if ($currentScript === "listen" && !empty($id)) {
	print "<p>Game Over. Insert another coin. Or provide a valid id.</p>";
	exit;
}

function getSheet($sheetId) {
	global $config;
	$sId = $config["spreadsheet-id"];
	$url = "https://docs.google.com/spreadsheets/d/$sId/pub?gid=$sheetId&single=true&output=csv&x=1";
	$csv = file_get_contents($url);
	$rows = explode(PHP_EOL, $csv);
	$header = array_shift($rows);

	$headerRow = str_getcsv($header);
	$header = array();
	foreach ($headerRow as $h) {
		$header[] = str_replace(" ", "-", strtolower($h));
	}
	$results = array();
	foreach ($rows as $row) {
		$csvRow = str_getcsv($row);
		$item = array();
		if (isset($csvRow[12]) && $csvRow[12] == "-") {
			continue;
		}
		for ($i=0; $i < count($csvRow); $i++) {
			$key = $header[$i];
			$val = $csvRow[$i];
			if (strpos($key, 'test') === false) {
				$item[$key] = $val;
			}
		}
		$results[] = $item;
	}
	return $results;
}

