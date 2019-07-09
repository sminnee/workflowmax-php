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

        $data['Type'] = "";
        if (isset($data['Date']))
            $data['Type'] = "Date";
        if (isset($data['Number']))
            $data['Type'] = "Number";
        if (isset($data['Decimal']))
            $data['Type'] = "Decimal";
        if (isset($data['Boolean']))
            $data['Type'] = "Boolean";
        if (isset($data['Text']))
            $data['Type'] = "Text";

        
        return $data;
    }
}
