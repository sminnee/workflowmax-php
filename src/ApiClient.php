<?php

namespace Sminnee\WorkflowMax;

use GuzzleHttp\Client as Guzzle;

/**
 * The WorkflowMax API connector
 */
class ApiClient
{

    private $params;
    private $fetcher;
    private $goutte;

    public function __construct($params) {
        $this->params = $params;

        $this->fetcher = new Guzzle([
            'base_uri' => 'https://api.workflowmax.com/',
            'timeout'  => 30.0,
        ]);
    }

    /**
     * Get a goutte client logged into this WFM account
     * @return \Goutte\Client
     */
    public function goutte()
    {
        if (!$this->goutte) {
            $this->goutte = new \Goutte\Client();
            $login = new \SilverStripe\WorkflowMax\Scraper\LoginHandler($this->goutte);

            foreach (['account', 'username', 'password'] as $required) {
                if (empty($this->params[$required])) {
                    throw new \LogicException("Parameter '$required' is required");
                }
            }

            list($success, $message) = $login->login(
                $this->params['account'],
                [
                    'username' => $this->params['username'], 'password' => $this->params['password']
                ]
            );

            if (!$success) {
                throw new \LogicException("Couldn't log in: " . $message);
            }
        }
    }

    /**
     * @return Sminnee\WorkflowMax\Connector\JobConnector
     */
    public function job() {
        return new Connector\JobConnector($this);
    }

    /**
     * @return Sminnee\WorkflowMax\Connector\TimesheetConnector
     */
    public function timesheet() {
        return new Connector\TimesheetConnector($this);
    }

    /**
     * @return Sminnee\WorkflowMax\Connector\StaffConnector
     */
    public function staff() {
        return new Connector\StaffConnector($this);
    }

    /**
     * @return Sminnee\WorkflowMax\Connector\ClientConnector
     */
    public function client() {
        return new Connector\ClientConnector($this);
    }


    public function report() {
        return new Connector\ReportConnector($this);
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
