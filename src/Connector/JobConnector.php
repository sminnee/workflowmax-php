<?php

namespace Sminnee\WorkflowMax\Connector;

use Datetime;

use Sminnee\WorkflowMax\ApiClient;
use Sminnee\WorkflowMax\Model\Job;
use Sminnee\WorkflowMax\Model\JobList;

/**
 * A sub-client responsible for accessing job
 */
class JobConnector
{

    protected $connector;

    function __construct(ApiClient $connector) {
        $this->connector = $connector;
    }

    /**
     * Returns a job byId job number.
     *
     * @return Sminnee\WorkflowMax\Model\Job
     */
    function byId($job) {
        return new Job($this->connector, $this->connector->apiCall(
            "job.api/get/$job",
            function($result) { return $result['Job']; }
        ));
    }

    function byStub($stubData) {
        return $this->byId($stubData['ID'])->populate($stubData);
    }

    /**
     * Returns a list of jobs in a date range.
     *
     * @param Datetime $start The date at the start of the date range
     * @param Datetime $end The date at the end of the date range
     * @return Sminnee\WorkflowMax\Model\JobList
     */
    function byDateRange(Datetime $start, Datetime $end) {
        return new JobList();
    }
}
