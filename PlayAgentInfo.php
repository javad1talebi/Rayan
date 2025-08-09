#!/usr/bin/php -q
<?php

require('phpagi.php');
error_reporting(E_ALL);

$agi = new AGI();
$agi->answer();


$db = mysql_connect("localhost", "root", "123");
if (!$db) {
    $agi->verbose("MySQL connection failed.");
    exit(1);
}
mysql_select_db("asterisk", $db);
mysql_query("SET NAMES 'utf8'", $db);


$queueName = '';
$temp = $agi->get_variable("QUEUENUM");
if ($temp && isset($temp['data']) && $temp['data'] != '') {
    $queueName = $temp['data'];
    $agi->verbose("Queue name from QUEUENUM: " . $queueName);
} else {
    $agi->verbose("Queue name not found.");
    exit(0);
}


$identitySetting = 'disabled';
$sql = "SELECT operator_identity FROM survey_property WHERE queue = '" . mysql_real_escape_string($queueName) . "' AND status = 'active' LIMIT 1";
$res = mysql_query($sql, $db);
if ($res && mysql_num_rows($res) > 0) {
    $row = mysql_fetch_assoc($res);
    $identitySetting = $row['operator_identity'];
}
$agi->verbose("Operator Identity Setting: " . $identitySetting);


if ($identitySetting == 'disabled') {
    $agi->verbose("Identity is disabled for this queue.");
    exit(0);
}


$temp = $agi->get_variable("CONNECTEDLINE(num)");
$agentNumber = isset($temp['data']) ? $temp['data'] : '';
$agi->verbose("Agent Number: " . $agentNumber);


if ($identitySetting == 'name') {
    $soundFile = 'custom/' . $agentNumber;
    $agi->verbose("Playing: " . $soundFile);
    $agi->stream_file($soundFile, '0123456789*#');
} elseif ($identitySetting == 'number') {
    $agi->stream_file('custom/agent-number', '0123456789*#');
    $agi->say_number($agentNumber);
    $agi->stream_file('custom/befarmaiid', '0123456789*#');
}

?>
