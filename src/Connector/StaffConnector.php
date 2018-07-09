<?php

namespace Sminnee\WorkflowMax\Connector;

use Datetime;

use Sminnee\WorkflowMax\ApiClient;
use Sminnee\WorkflowMax\Model\Staff;

/**
 * A sub-client responsible for accessing job
 */
class StaffConnector extends TypeConnector
{

    protected $connector;

    function __construct(ApiClient $connector) {
        $this->connector = $connector;
    }

    /**
     * Returns a job by job number.
     *
     * @return Sminnee\WorkflowMax\Model\Client
     */
    function byId($id) {
        return new Staff($this->connector, $this->connector->apiCall(
            "staff.api/get/$id",
            function($result) { return $result['Staff']; }
        ));
    }

    function all() {
        return $this->listFromApiCall($this->connector->apiCall(
            'staff.api/list',
            function($result) { return $result['StaffList']['Staff']; }
        ));
    }

    function byStub($stubData) {
        return $this->byId($stubData['ID'])->populate($stubData);
    }

}
