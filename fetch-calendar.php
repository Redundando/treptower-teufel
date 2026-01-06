<?php
$handle = curl_init();
 
$url = "https://calendar.google.com/calendar/ical/jir9000gm5ds47l5qmok5er4h0%40group.calendar.google.com/public/basic.ics";
 
curl_setopt($handle, CURLOPT_URL, $url);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($handle);
curl_close($handle);
include('CalFileParser.php');

$cal = new CalFileParser();
$cal->set_output('json');
$c = $cal->parse($url);

echo ($c);
