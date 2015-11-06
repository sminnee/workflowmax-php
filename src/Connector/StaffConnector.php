<?php

namespace Sminnee\WorkflowMax\Connector;

use Datetime;

use Sminnee\WorkflowMax\Connector;
use Sminnee\WorkflowMax\Model\Staff;

/**
 * A sub-client responsible for accessing job
 */
class StaffConnector
{

    protected $connector;

    function __construct(Connector $connector) {
        $this->connector = $connector;
    }

    /**
     * Returns a job by job number.
     *
     * @return Sminnee\WorkflowMax\Model\Client
     */
    function byId($id) {
        return new Staff($this->connector, $this->connector->apiCall(
            "client.api/get/$id",
            function($result) { return $result['Staff']; }
        ));
    }

    function byStub($stubData) {
        return $this->byId($stubData['ID'])->populate($stubData);
    }

}
