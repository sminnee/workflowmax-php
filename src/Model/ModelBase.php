<?php

namespace Sminnee\WorkflowMax\Model;

use Datetime;
use Sminnee\WorkflowMax\ApiCall;
use Sminnee\WorkflowMax\ApiClient;

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
     * @param ApiClient  $connector  The WFM client object
     * @param ApiCall $apiCall The API call to execute to get this object's data
     * @param array   $data    Data that has already been fetched
     */
    function __construct(ApiClient $connector, ApiCall $apiCall, array $data = []) {
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

        if (isset($this->data[$param]))
            return $this->data[$param];
        else
            return false;
    }

    /**
     * Return a one-line summary of this boject
     * @return [type] [description]
     */
    function oneLine() {
        return get_class($this) . ' #' . $this->data['ID'];
    }

    /**
     * Return a paragraph summary of this boject
     * @return [type] [description]
     */
    function paragraph() {
        $summary = get_class($this) . "\n";
        foreach($this->data as $k => $v) {
            $summary .= " - $k: ";
            if($v instanceof Datetime) {
                $summary .= $v->format('Y-m-d H:i:s') . "\n";
            } elseif(is_object($v)) {
                $summary .= $v->oneLine() . "\n";
            } elseif(is_array($v)) {
                $summary .= var_export($v, true) . "\n";
            } else {
                $summary .= (string)$v . "\n";
            }
        }
        return $summary;
    }

    function populate($data) {
        return new static($this->connector, $this->apiCall, array_merge($this->data, $data));
    }
}
