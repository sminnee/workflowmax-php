<?php

require(__DIR__ . "/../vendor/autoload.php");

use Sminnee\WorkflowMax\ApiClient;

$client = new ApiClient([
    'username' => getenv('WFM_USERNAME'),
    'password' => getenv('WFM_PASSWORD'),
    'xero_login' => true,
]);

$reportID = getenv('WFM_REPORT_ID');

if ($reportID) {
    $report = $client->report()->byID($reportID);

    foreach ($report as $record) {
        var_dump($record);
    }
}
