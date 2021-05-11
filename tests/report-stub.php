<?php

require(__DIR__ . "/../vendor/autoload.php");

use Sminnee\WorkflowMax\ApiClient;

$client = new ApiClient([
    'username' => getenv('WFM_USERNAME'),
    'password' => getenv('WFM_PASSWORD'),
    'totp_secret' => getenv('WFM_TOTP_SECRET'),
    'xero_login' => true,
]);

$reportID = getenv('WFM_REPORT_ID');
$reportName = getenv('WFM_REPORT_NAME');

if ($reportID) {
    $report = $client->report()->byID($reportID);

    foreach ($report as $record) {
        var_dump($record);
    }

} elseif ($reportName) {
    $report = $client->report()->byName($reportName);

    foreach ($report as $record) {
        var_dump($record);
    }

} else {
    var_dump($client->report()->list());
}
