<?php

namespace Sminnee\WorkflowMax\Model;

use Sminnee\WorkflowMax\ApiCall;
use Datetime;

/**
 * Represents a single job
 *
 * @property-read string $ID
 * @property-read string $Name
 * @property-read string $Description
 * @property-read string $State
 * @property-read string $Type
 * @property-read Datetime $StartDate
 * @property-read Datetime $DueDate
 * @property-read Sminnee\WorkflowMax\Model\Client $Client
 * @property-read Sminnee\WorkflowMax\Model\Contact $Contact
 * @property-read Sminnee\WorkflowMax\Model\Staff $Manager
 */
class Job
{
    use ModelBase;

    function processData($data) {

        if(isset($data['Client'])) {
            $data['Client'] = $this->connector
                ->client()
                ->byStub($data['Client']);
        }

        if(isset($data['Manager'])) {
            $data['Manager'] = $this->connector
                ->staff()
                ->byStub($data['Manager']);
        }

        if(isset($data['StartDate'])) {
            $data['StartDate'] = new Datetime($data['StartDate']);
        }

        if(isset($data['DueDate'])) {
            $data['DueDate'] = new Datetime($data['DueDate']);
        }

        return $data;
    }
}
