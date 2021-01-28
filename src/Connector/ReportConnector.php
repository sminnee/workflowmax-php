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
            $this->fetcher = new ReportFetcher($this->connector->goutte(), $this->connector->httpClient());
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

    /**
     * Returns a report by the given name
     *
     * @return iterator
     */
    public function byName($reportName)
    {
        $id = $this->idForName($reportName);
        if ($id === null) {
            throw new \LogicException(sprintf('Report "%s" not found', $reportName));
        }
        return $this->byId($id);
    }

    /**
     * Returns the ID of the report with the given name
     */
    public function idForName($reportName): ?int
    {
        $mapper = array_flip($this->list());
        if (isset($mapper[$reportName])) {
            return (int)$mapper[$reportName];
        }

        return null;
    }

    /**
     * Returns a list of reports
     * @return array Map with report IDs as the key and names as the values
     */
    public function list()
    {
        return $this->fetcher()->getReportList();
    }
}
