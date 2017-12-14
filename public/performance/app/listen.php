<html>
<body>
<?php
/*
	listen.php

	This gets results for each test from WPT.org and updates the Google Spreadsheet.
*/
require_once "config.php";

$id = "";
if (isset($_REQUEST["id"])) {
	$id = validate_alphanumeric_underscore_id( $_REQUEST["id"] );
}

if (empty($id)) {
	print "<p>Game Over. Insert another coin.</p>";
	exit;
}

$wpt_url = "https://www.webpagetest.org/jsonResult.php?test=" . $id;

print "<p>Running check for test " . $id . "</p>";
print "<p>Test URL: " . $wpt_url . "</p>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $wpt_url);
$response = curl_exec($ch);
curl_close($ch);

$wpt_result    = json_decode($response, true);
$wpt_data      = $wpt_result["data"];
$avgFirstView  = $wpt_result["data"]["average"]["firstView"];

$flagTest   = 0;
$flagIgnore = 0;

$label  = $wpt_data["label"];
if (strpos($label, 'test') !== false) {
	$flagTest   = 1;
	$flagIgnore = 1;
}

$epoch = $wpt_data["completed"];
$dt = new DateTime("@$epoch");  // convert UNIX timestamp to PHP DateTime
$dt->format('Y-m-d H:i:s'); // output = 2017-01-01 00:00:00

$site_results = array(
	"date"         => $dt->format('Y-m-d H:i:s'),
	"url"          => $wpt_data["url"],
	"label"        => $wpt_data["label"],
	"wpt_url"      => $wpt_data["summary"],
	"wpt_id"       => $wpt_data["id"],
	"description"  => $wpt_data["from"],
	"connectivity" => $wpt_data["connectivity"], /* Not in form */

	"load_time"    => $avgFirstView["loadTime"], // s
	"first_byte"   => $avgFirstView["TTFB"],     // ms
	"start_render" => $avgFirstView["render"],   // ms
	"speed_index"  => $avgFirstView["SpeedIndex"],
	"interactive"  => $avgFirstView["TimeToInteractive"], // ms
	"time_fl"      => $avgFirstView["fullyLoaded"],
	"requests_fl"  => $avgFirstView["requests"],
	"bytes_in_fl"  => $avgFirstView["bytesIn"],
	"cost"         => "",
	"notes"        => "",
	"visually_complete" => $avgFirstView["visualComplete"], // ms
	"test"         => $flagTest,
	"ignore"       => $flagIgnore,
);

$agent = "";
if (strpos($site_results["description"], 'Nexus') !== false) {
	$agent = "Nexus 5";
}
if (strpos($site_results["description"], 'iPhone') !== false) {
	$agent = "iPhone";
}
if (strpos($site_results["description"], 'Motorola') !== false) {
	$agent = "Motorola";
}

/*
	Every Google Form has its own map that you'll need to update
*/
$data = array(
	"entry.117124524"  => $site_results["date"],         // Date
	"entry.1974110002" => $site_results["url"],          // URL
	"entry.185560251"  => $site_results["label"],        // Label
	"entry.852751534"  => $agent,                        // Agent
	"entry.564395144"  => $site_results["wpt_url"],      // WPT URL
	"entry.316255432"  => $site_results["wpt_id"],       // WPT ID
	"entry.1963073653" => $site_results["load_time"],    // Load Time
	"entry.1024049146" => $site_results["first_byte"],   // First Byte
	"entry.107898377"  => $site_results["start_render"], // Start Render
	"entry.1136817324" => $site_results["speed_index"],  // Speed Index
	"entry.4630770"    => $site_results["interactive"],  // Interactive
	"entry.1811968350" => 0,                             // Time
	"entry.708257906"  => 0,                             // Bytes In
	"entry.2090934205" => $site_results["time_fl"],      // Time FL
	"entry.231594900"  => $site_results["requests_fl"],  // Requests FL
	"entry.1857696547" => $site_results["bytes_in_fl"],  // Bytes In FL
	"entry.1265583726" => "-",                           // Cost
	"entry.295018767"  => $site_results["visually_complete"],  // Visually Complete
	"entry.1286378919" => "",                            // Notes
	"entry.1102938160" => $site_results["test"],         // Test
	"entry.912759408"  => $site_results["ignore"]        // Ignore
);

$curl = curl_init("https://docs.google.com/forms/d/e/" . $config["form-id"] . "/formResponse");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

$result = curl_exec($curl);
$err    = curl_errno($curl) . "-" .curl_error($curl);
curl_close($curl);

print "<textarea style='width: 90%; max-width: 600px; height: 600px;'>";
print_r($site_results);
print "</textarea>";

include "update.php";

