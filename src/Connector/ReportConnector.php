<?php

namespace Sminnee\WorkflowMax\Connector;

use Datetime;

use Sminnee\WorkflowMax\ApiClient;
use Sminnee\WorkflowMax\Scraper\ReportFetcher;
use Sminnee\WorkflowMax\Model\Job;
use Sminnee\WorkflowMax\Model\JobList;

/**
 * A sub-client responsible for accessing job
 */
class ReportConnector
{

    protected $connector;
    protected $fetcher;

    public function __construct(ApiClient $connector)
    {
        $this->connector = $connector;
    }

    public function fetcher()
    {
        if (!$this->fetcher) {
            $this->fetcher = new ReportFetcher($this->connector->goutte());
        }
        return $this->fetcher;
    }

    /**
     * Returns a report by the given ID
     *
     * @return iterator
     */
    public function byId($reportID)
    {
        return $this->fetcher()->getReport($reportID);
    }
}
