<?php

namespace Sminnee\WorkflowMax\Model;

use Sminnee\WorkflowMax\ApiCall;
use Datetime;

/**
 * Represents a single timesheet entry
 */
class Timesheet
{
    use ModelBase;

    function processData($data) {

        if(isset($data['Job'])) {
            $data['Job'] = $this->connector
                ->job()
                ->byStub($data['Job']);
        }

        if(isset($data['Staff'])) {
            $data['Staff'] = $this->connector
                ->staff()
                ->byStub($data['Staff']);
        }

        // TODO: Task
        if(isset($data['Date'])) {
            $data['Date'] = new Datetime($data['Date']);
        }
        if(isset($data['Start'])) {
            $data['Start'] = new Datetime($data['Start']);
        }
        if(isset($data['End'])) {
            $data['End'] = new Datetime($data['End']);
        }

        return $data;
    }
}
