<?php

namespace Sminnee\WorkflowMax\Connector;

use Datetime;
use iter;

use Sminnee\WorkflowMax\Connector;
use Sminnee\WorkflowMax\ApiCall;
use Sminnee\WorkflowMax\Model\Timesheet;
use Sminnee\WorkflowMax\Model\TimesheetList;

/**
 * A sub-client responsible for accessing job
 */
class TimesheetConnector extends TypeConnector
{

    protected $client;

    function __construct(Connector $connector) {
        $this->connector = $connector;
    }

    /**
     * Returns a job by job number.
     *
     * @return Sminnee\WorkflowMax\Model\Timesheet
     */
    function byId($id) {
        return new Timesheet($this->connector, $this->connector->apiCall(
            "time.api/get/$id",
            function($result) { return $result['Time']; }
        ));
    }

    /**
     * Returns timesheets for a given job
     *
     * @return Sminnee\WorkflowMax\Model\TimesheetList
     */
    function byJob($jobId) {
        return new TimesheetList($this->connector, $this->connector->apiCall(
            "time.api/job/$jobId",
            function($result) { return $result['Times']; }
        ));
    }

    function byStub($stubData) {
        return $this->byId($stubData['ID'])->populate($stubData);
    }


    /**
     * Returns all timesheet entries in a given date range
     *
     * @param Datetime $start The date at the start of the date range
     * @param Datetime $end The date at the end of the date range
     *
     * @return Sminnee\WorkflowMax\Model\TimesheetList
     */
    function byDateRange(Datetime $start, Datetime $end) {
        $params = 'from=' . urlencode($start->format('Ymd')) . '&to=' . urlencode($end->format('Ymd'));

        return $this->listFromApiCall($this->connector->apiCall(
            'time.api/list?' . $params,
            function($result) { return isset($result['Times']['Time']) ? $result['Times']['Time'] : []; }
        ));
    }

    /**
     * Return timesheet entries for a single day
     */
    function byDay($day) {
        return $this->byDateRange($day, $day);
    }
}
