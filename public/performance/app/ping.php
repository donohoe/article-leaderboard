<?php
/*
	ping.php

	This gets a list of the test URLS from the spreadsheet
	It specifies and starts new tests at WebPageTest.org
	Tells WPT where to send results upon completion.
*/
require_once "config.php";

if (!isset($_REQUEST["key"]) || $_REQUEST["key"] !== $config["key"] ) {
	print "<p>Game Over. Insert another coin. Try a different key.</p>";
	exit;
}

$location    = $config["location"];
$callbackURL = $config["callbackURL"];
$apiKey      = $config["api-key"];

$tests = getSheet( $config["sheets"]["range"] );

Print "<p><b>Ping...</b></p>";

foreach ($tests as $test) {
	$label = $test["source"];
	$url   = $test["url"];

	if (!empty($url)) {
/*
	https://twitter.com/patmeenan/status/864290033951485952
*/
		$requestURL = implode("", array(
			"https://www.webpagetest.org/runtest.php",
			"?runs=1",
			"&fvonly=1",
			"&mobile=1",
			"&timeline=1",
			"&video=1",
			"&location=$location",
			"&label=$label",
			"&url=$url",
			"&k=$apiKey",
			"&pingback=$callbackURL",
			"&f=json"
		));

		$tmp = file_get_contents($requestURL);
		print "<p>Requesting test: <b>" . $test["label"] . "</b> <a href=\"" . $requestURL . "\">Retest</a></p>";
	} else {
		print "<p>Invalid URL for <b>" . $test["label"] . "</b>: <small>$requestURL</small></p>";
		print_r($test);
	}
}
$tmp = 0;

