<?php

namespace Sminnee\WorkflowMax\Model;

use Sminnee\WorkflowMax\ApiCall;
use Sminnee\WorkflowMax\Connector;

/**
 * Basis of API models, fetching data, etc
 */
trait ModelBase
{

    /**
     * Transform simple single/API values into more useful objects
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    abstract function processData($data);

    /**
     * Internal record of previously fetched data
     * @var array
     */
    protected $data = [];

    /**
     * The WFM client object
     * @var Sminnee\WorkflowMax\Client
     */
    protected $client;

    /**
     * The API call to fetch data from
     * @var Sminnee\WorkflowMax\ApiCall
     */
    protected $apiCall;

    protected $apiCalled = false;

    /**
     * Create a new model
     * @param Client  $client  The WFM client object
     * @param ApiCall $apiCall The API call to execute to get this object's data
     * @param array   $data    Data that has already been fetched
     */
    function __construct(Connector $connector, ApiCall $apiCall, array $data = []) {
        $this->connector = $connector;
        $this->apiCall = $apiCall;
        if ($data) {
            $this->data = $this->processData($data);
        }
    }

    function __get($param) {
        if(!isset($this->data[$param]) && !$this->apiCalled) {
            $this->apiCalled = true;
            $this->data = array_merge(
                $this->data,
                $this->processData($this->apiCall->data())
            );
        }

        return $this->data[$param];
    }

    function data() {
        return $this->data;
    }

    function populate($data) {
        return new static($this->connector, $this->apiCall, array_merge($this->data, $data));
    }
}
