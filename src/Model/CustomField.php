<?php

namespace Sminnee\WorkflowMax\Model;

use Sminnee\WorkflowMax\ApiCall;

/**
 * Represents a single CustomField
 */
class CustomField
{
    use ModelBase;

    function processData($data)
    {

        $data['Value'] = $data[$data['Type']];


        return $data;
    }
}
