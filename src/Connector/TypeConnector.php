<?php

namespace Sminnee\WorkflowMax\Connector;

use Sminnee\WorkflowMax\ApiCall;
use iter;

/**
 * Base class for all type-specific sub-connectors
 */
class TypeConnector
{
    /**
     * Return a list of items from the given ApiCall
     * @param  ApiCall $apiCall [description]
     * @return [type]           [description]
     */
    function listFromApiCall(ApiCall $apiCall) {
        $self = $this;
        return iter\map(function($record) use ($self) {
            return $self->byStub($record);
        }, $apiCall);
    }
}
