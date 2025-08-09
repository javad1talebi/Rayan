#!/usr/bin/php -q
<?php
require('phpagi.php');
error_reporting(E_ALL);
$agi = new AGI();

/* Database Settings */
$db_host = '127.0.0.1';
$db_user = 'root';
$db_password = '123';

/* Caller Info */
$calleridNumber = $agi->get_variable("CALLERID(num)"); $calleridNumber = $calleridNumber['data'];
$calleridName = $agi->get_variable("CALLERID(name)"); $calleridName = $calleridName['data'];
$uniqueid = $agi->get_variable("UNIQUEID"); $uniqueid = $uniqueid['data'];
$agentVar = $agi->get_variable("AGENT");
$agentNumber = isset($agentVar['data']) ? $agentVar['data'] : '';
$agi->Verbose("AGENT FROM DIALPLAN: " . $agentNumber);

/* Get Queue Number from AGI arguments */
$surveyLocation = isset($argv[1]) ? $argv[1] : '';
$agi->Verbose('QUEUE NUMBER: ' . $surveyLocation);

/* Connect to DB */
$con = mysql_connect($db_host, $db_user, $db_password);
if (!$con) {
    $agi->Verbose('DB NOT CONNECTED');
    $agi->stream_file('custom/goodbye');
    die();
}
mysql_select_db("asterisk", $con);

/* Defaults */
$surveyAudio = 'custom/survey-prompt';
$outOfRange = 'custom/outofrange';
$outOfRangeBye = 'custom/outofrange-bye';
$lowScoreWarning = 'custom/low-score-warning';
$highScoreThankyou = 'custom/high-score-thankyou';
$submitMessage = 'custom/sabt';

$maxInvalid = 3;
$minScoreRecord = 'disabled';
$maxScoreRecord = 'disabled';
$operatorIdentity = 'disabled';

/* Load from survey_property if exists */
$res = mysql_query("SELECT * FROM survey_property WHERE queue = '" . mysql_real_escape_string($surveyLocation) . "' AND status = 'active' LIMIT 1");
if ($row = mysql_fetch_assoc($res)) {
    if (!empty($row['audio_file'])) $surveyAudio = $row['audio_file'];
    if (!empty($row['max_invalid'])) $maxInvalid = (int)$row['max_invalid'];
    if (!empty($row['min_score_record'])) $minScoreRecord = $row['min_score_record'];
    if (!empty($row['max_score_record'])) $maxScoreRecord = $row['max_score_record'];
    if (!empty($row['operator_identity'])) $operatorIdentity = $row['operator_identity'];
}

/* Get Survey Value */
$surveyValue = -1;
$attempts = 0;
while ($attempts < $maxInvalid) {
    $temp = $agi->get_data($surveyAudio, 12000, 1);
    $surveyValue = $temp['result'];

    if ($surveyValue >= 1 && $surveyValue <= 5) {
        break;
    }

    $attempts++;
    if ($attempts >= $maxInvalid) {
        $agi->stream_file($outOfRangeBye);
        $agi->Hangup();
        die();
    }

    $agi->stream_file($outOfRange);
}

/* ثبت فوری امتیاز در دیتابیس */
$insertQuery = "
    INSERT INTO survey (
        date_time, survey_value, agent_number, caller_number, caller_name,
        uniqueid, survey_location, complaint_record_path
    ) VALUES (
        now(), '$surveyValue', '" . mysql_real_escape_string($agentNumber) . "',
        '" . mysql_real_escape_string($calleridNumber) . "',
        '" . mysql_real_escape_string($calleridName) . "',
        '" . mysql_real_escape_string($uniqueid) . "',
        '" . mysql_real_escape_string($surveyLocation) . "',
        NULL
    )";
$agi->Verbose("INSERT QUERY: " . $insertQuery);
mysql_query($insertQuery);

/* Prepare for Recording if Needed */
$recordingPath = 'NULL';
$recordingDir = "/var/lib/asterisk/sounds/complaints";

if ($surveyValue == $minScoreRecord || $surveyValue == $maxScoreRecord) {
    $filePrefix = $surveyValue == $minScoreRecord ? 'complaint_' : 'thanks_';
    $agi->stream_file($surveyValue == $minScoreRecord ? $lowScoreWarning : $highScoreThankyou);

    $recordingFile = $recordingDir . "/" . $filePrefix . $uniqueid;
    $recordingFullPath = $recordingFile . ".wav";

    $agi->Verbose("Recording to: " . $recordingFullPath);
    $recordResult = $agi->exec("Record", "$recordingFullPath,60,60,k");
    $agi->Verbose("Record result: " . json_encode($recordResult));

    if (file_exists($recordingFullPath)) {
        $recordingPath = "'" . mysql_real_escape_string($recordingFullPath) . "'";

        /* آپدیت مسیر فایل ضبط */
        $updateQuery = "
            UPDATE survey SET complaint_record_path = $recordingPath
            WHERE uniqueid = '" . mysql_real_escape_string($uniqueid) . "'
            LIMIT 1
        ";
        $agi->Verbose("UPDATE QUERY: " . $updateQuery);
        mysql_query($updateQuery);
    } else {
        $agi->Verbose("Recording file not found");
    }
}

/* Set Optional Variables */
$agi->set_variable("CDR(accountcode)", "SURVEY-" . $surveyLocation . "-" . $agentNumber);
$agi->set_variable("CDR(userfield)", $surveyValue);

/* Final Thank You Message */
$agi->exec('Playback', $submitMessage);
$agi->Hangup();

mysql_close($con);
?>
