<?php

namespace Sminnee\WorkflowMax\Connector;

use Datetime;

use Sminnee\WorkflowMax\ApiClient;
use Sminnee\WorkflowMax\Model\Client;
use Sminnee\WorkflowMax\Model\JobList;

/**
 * A sub-client responsible for accessing job
 */
class ClientConnector
{

    protected $client;

    function __construct(ApiClient $connector) {
        $this->connector = $connector;
    }

    /**
     * Returns a job by job number.
     *
     * @return Sminnee\WorkflowMax\Model\Client
     */
    function byId($id) {
        return new Client($this->connector, $this->connector->apiCall(
            "client.api/get/$id",
            function($result) { return $result['Client']; }
        ));
    }

    function byStub($stubData) {
        return $this->byId($stubData['ID'])->populate($stubData);
    }
}
