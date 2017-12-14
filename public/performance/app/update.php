<?php
/*
	update.php

	This gets data from the spreadsheet and updates various files, from JSON to RSS.
*/
require_once "config.php";

if (!isset($_REQUEST["key"]) || $_REQUEST["key"] !== $config["key"] ) {
	print "<p>Game Over. Insert another coin. Try a different key.</p>";
	exit;
}

function array_sort($array, $on, $order=SORT_ASC) {
	$new_array = array();
	$sortable_array = array();
	if (count($array) > 0) {
		foreach ($array as $k => $v) {
			if (is_array($v)) {
				foreach ($v as $k2 => $v2) {
					if ($k2 == $on) {
						$sortable_array[$k] = $v2;
					}
				}
			} else {
				$sortable_array[$k] = $v;
			}
		}
		switch ($order) {
			case SORT_ASC:
				asort($sortable_array);
				break;
			case SORT_DESC:
				arsort($sortable_array);
				break;
		}
		foreach ($sortable_array as $k => $v) {
			$new_array[] = $array[$k];
		}
	}
	return $new_array;
}

function getRankings() {

	global $config;
	$sheetRankId     = $config["sheets"]["rankings"];
	$sheetPastRankId = $config["sheets"]["pastrankings"];

	$sheetInfoId = $config["sheets"]["info"];

	$rankings     = getSheet( $sheetRankId );
	$pastRankings = getSheet( $sheetPastRankId );
	$info         = getSheet( $sheetInfoId );

	$rankItems = array();
	$counter = 0;

	foreach ($rankings as $item) {
		if (!empty($item["load-time"]) && !empty($item["speed-index"])) {
			if ($item["source"] === $pastRankings[$counter]["source"] ) {
				$item["change"] = array(
					"score"       => $pastRankings[$counter]["score"],
					"speed-index" => $pastRankings[$counter]["speed-index"],
					"load-time"   => $pastRankings[$counter]["load-time"]
				);
			}
			$rankItems[] = $item;
		}
		$counter++;
	}

	$rankItems = array_sort($rankItems, 'score', SORT_ASC);

	$response = array(
		"info" => $info[0],
		"rank" => $rankItems
	);

	$response["info"]["published"] = date('Y-m-d H:i:s');

	writeRSS($response);
	updateFile($response);

	return $response;
}

function writeRSS($response) {
	$filename = "../data/rankings";
	$items = $response["rank"];
	$itemsXML = "";

	foreach ($items as $item) {
		$itemsXML .= implode("\n", array(
			"<item>",
				"<title>" . $item["label"] . "</title>",
				"<description>",
				"The average load time is " . $item["load-time"] . " seconds, " . $item["requests"] . " requests and Speed Index at " . $item["speed-index"],
				"</description>",
				"<link>" . $item["link"] . "</link>",
			"</item>"
		));
	}

	$feed = implode("\n", array(
		"<rss version=\"2.0\">",
		"<channel>",
		"<title>Article Load Time Leaderboard</title>",
		"<description>",
		"There can be only one.",
		"</description>",
		"<link>http://example.com</link>",
		"<language>en-us</language>",
		"<generator>Hand-Coded</generator>",
		$itemsXML,
		"</channel>",
		"</rss>",
	));
	file_put_contents($filename . ".xml", $feed);
	print "<p><a href='$filename.xml' target='_blank'>RSS</a></p>";
}

function updateFile($data) {
	$filename = "../data/rankings";
	$json = json_encode($data);
	file_put_contents($filename . ".jsonp", "parseResponse(" . $json . ");");
	file_put_contents($filename . ".json", $json);
	file_put_contents($filename . ".js", "var response = " . $json . ";");

	print "<p><a href='$filename.jsonp' target='_blank'>JSONP</a></p>";
	print "<p><a href='$filename.json' target='_blank'>JSON</a></p>";
	print "<p><a href='$filename.js' target='_blank'>JS</a></p>";
}

print "<p>Updating local files.</p>";

$rankings = getRankings();

