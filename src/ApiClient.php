<?php

namespace Sminnee\WorkflowMax;

use GuzzleHttp\Client as Guzzle;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * The WorkflowMax API connector
 */
class ApiClient
{

    /**
     * @var
     */
    private $params;

    /**
     * @var
     */
    private $goutte;

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * ApiClient constructor.
     *
     * @param $params
     */
    public function __construct($params)
    {
        $this->params = $params;

        $this->client = HttpClient::create();
    }

    public function httpClient()
    {
        return $this->client;
    }

    /**
     * Get a HttpBrowser client logged into this WFM account
     * @return HttpBrowser
     */
    public function goutte()
    {
        if (!$this->goutte) {

            $this->goutte = new HttpBrowser($this->client);
            $this->goutte->setServerParameters(['HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.102 Safari/537.36']);

            if (!empty($this->params['xero_login'])) {
                $login = new Scraper\XeroLoginHandler($this->goutte);
            } else {
                $login = new Scraper\LoginHandler($this->goutte);
            }

            foreach (['username', 'password'] as $required) {
                if (empty($this->params[$required])) {
                    throw new \LogicException("Parameter '$required' is required");
                }
            }

            list($success, $message) = $login->login([
                'username' => $this->params['username'], 
                'password' => $this->params['password'],
                'totp_secret' => $this->params['totp_secret'],
            ]);

            if (!$success) {
                throw new \LogicException("Couldn't log in: " . $message);
            }
        }

        return $this->goutte;
    }

    /**
     * @return Sminnee\WorkflowMax\Connector\JobConnector
     */
    public function job()
    {
        return new Connector\JobConnector($this);
    }

    /**
     * @return Sminnee\WorkflowMax\Connector\TimesheetConnector
     */
    public function timesheet()
    {
        return new Connector\TimesheetConnector($this);
    }

    /**
     * @return Sminnee\WorkflowMax\Connector\StaffConnector
     */
    public function staff()
    {
        return new Connector\StaffConnector($this);
    }

    /**
     * @return Sminnee\WorkflowMax\Connector\ClientConnector
     */
    public function client()
    {
        return new Connector\ClientConnector($this);
    }

     /**
     * @return Sminnee\WorkflowMax\Connector\CustomFieldConnector
     */
    public function customField()
    {
        return new Connector\CustomFieldConnector($this);
    }


    /**
     * @return \Sminnee\WorkflowMax\Connector\ReportConnector
     */
    public function report()
    {
        return new Connector\ReportConnector($this);
    }


    /**
     * @return \Sminnee\WorkflowMax\Connector\QuoteConnector
     */
    public function quote()
    {
        return new Connector\QuoteConnector($this);
    }

    /**
     * @return \Sminnee\WorkflowMax\Connector\ContactConnector
     */
    public function contact()
    {
        return new Connector\ContactConnector($this);
    }

    /**
     * @return \Sminnee\WorkflowMax\Connector\InvoiceConnector
     */
    public function invoice()
    {
        return new Connector\InvoiceConnector($this);
    }

    /**
     * @return \Sminnee\WorkflowMax\Connector\CostConnector
     */
    public function cost()
    {
        return new Connector\CostConnector($this);
    }

    /**
     * Make an API call
     * @param string $url The relative URL (e.g. 'jobs.api/list')
     * @return Sminnee\WorkflowMax\ApiCall The API call
     */
    public function apiCall($url, callable $dataProcessor)
    {
        $paramJoiner = (strpos($url, '?') === false) ? '?' : '&';

        $fullUrl = $url . $paramJoiner
            . 'apiKey=' . urlencode($this->params['api_key'])
            . '&accountKey='  .urlencode($this->params['account_key']);

        return new ApiCall($fullUrl, $this->fetcher, $dataProcessor);
    }
}
