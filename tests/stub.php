<?php

require(__DIR__ . "/../vendor/autoload.php");

use Sminnee\WorkflowMax\Connector;
use Sminnee\WorkflowMax\IterMiner\IterMiner;

$client = new Connector([
	'api_key' => getenv('WFM_API_KEY') ,
	'account_key' => getenv('WFM_ACCOUNT_KEY'),
]);

$from = (new Datetime())->sub(new DateInterval('P2D'));
$to = (new Datetime())->sub(new DateInterval('P1D'));

$times = $client->timesheet()->byDay($to);

$grouped = iter\reduce(
    function($acc, $val) {
        if(empty($acc[$val->Staff->Name])) {
            $acc[$val->Staff->Name] = 0.0;
        }
        $acc[$val->Staff->Name] += $val->Minutes/60;
        return $acc;
    },
    $times
);

print_r($grouped);
