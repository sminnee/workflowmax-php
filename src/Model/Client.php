<?php

namespace Sminnee\WorkflowMax\Model;

use Sminnee\WorkflowMax\ApiCall;
use Sminnee\WorkflowMax\Model\Traits\HasCustomFields;

/**
 * Represents a single client
 */
class Client
{

    use ModelBase;
    use HasCustomFields;

    public function customFieldConnectorMethodName()
    {
        return 'forClient';
    }
    
    function processData($data) {
        return $data;
    }

}
