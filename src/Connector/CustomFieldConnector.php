<?php

namespace Sminnee\WorkflowMax\Connector;

use Sminnee\WorkflowMax\ApiClient;
use Sminnee\WorkflowMax\Model\Job;
use Sminnee\WorkflowMax\Model\Client;
use Sminnee\WorkflowMax\Model\CustomField;

/**
 * A sub-client responsible for accessing job
 */
class CustomFieldConnector extends TypeConnector
{
    protected $connector;

    function __construct(ApiClient $connector)
    {
        $this->connector = $connector;
    }

    function forClient(Client $client)
    {
        $apiEndpoint = 'client.api/get/' . $client->ID  . '/customfield';

        return $this->getMultiple($apiEndpoint);
    }
    
    function forJob(Job $job)
    {
        $apiEndpoint = 'job.api/get/' . $job->ID  . '/customfield';

        return $this->getMultiple($apiEndpoint);
    }

    function getInstance() {
        return new CustomField($this->connector, $this->connector->apiCall(null, function($result) { return $result; }));
    }

    function byStub($stubData) {
        return $this->getInstance()->populate($stubData);
    }

    protected function getMultiple($apiEndpoint)
    {
        return $this->listFromApiCall($this->connector->apiCall($apiEndpoint, function($result) {
            if (isset($result['CustomFields']['CustomField'])) {
                if (array_key_exists('ID', $result['CustomFields']['CustomField'])) {
                    return [$result['CustomFields']['CustomField']];
                } else {
                    return $result['CustomFields']['CustomField'];
                }
            }
            return [];
        }));
    }
}
