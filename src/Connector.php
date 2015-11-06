<?php

namespace Sminnee\WorkflowMax;

use GuzzleHttp\Client as Guzzle;

/**
 * The WorkflowMax API connector
 */
class Connector
{

    function __construct($params) {
        $this->params = $params;

        $this->fetcher = new Guzzle([
            'base_uri' => 'https://api.workflowmax.com/',
            'timeout'  => 30.0,
        ]);
    }

    /**
     * @return Sminnee\WorkflowMax\Connector\JobConnector
     */
    function job() {
        return new Connector\JobConnector($this);
    }

    /**
     * @return Sminnee\WorkflowMax\Connector\TimesheetConnector
     */
    function timesheet() {
        return new Connector\TimesheetConnector($this);
    }

    /**
     * @return Sminnee\WorkflowMax\Connector\StaffConnector
     */
    function staff() {
        return new Connector\StaffConnector($this);
    }

    /**
     * @return Sminnee\WorkflowMax\Connector\ClientConnector
     */
    function client() {
        return new Connector\ClientConnector($this);
    }

    /**
     * Make an API call
     * @param string $url The relative URL (e.g. 'jobs.api/list')
     * @return Sminnee\WorkflowMax\ApiCall The API call
     */
    function apiCall($url, callable $dataProcessor) {
        $paramJoiner = (strpos($url, '?') === false) ? '?' : '&';

        $fullUrl = $url . $paramJoiner
            . 'apiKey=' . urlencode($this->params['api_key'])
            . '&accountKey='  .urlencode($this->params['account_key']);

        return new ApiCall($fullUrl, $this->fetcher, $dataProcessor);
    }
}
