<?php
namespace Sminnee\WorkflowMax\Model;
use Datetime;
use Sminnee\WorkflowMax\ApiCall;
/**
 * Represents a single invoice
 *
 * @property-read string $ID
 * @property-read string $Type
 * @property-read string $Status
 * @property-read string $JobText
 * @property-read Datetime $Date
 * @property-read Datetime $DueDate
 * @property-read string $Amount
 * @property-read string $AmountTax
 * @property-read string $AmountIncludingTax
 * @property-read string $AmountPaid
 * @property-read string $AmountOutstanding
 *
 * @property-read Sminnee\WorkflowMax\Model\Client $Client
 * @property-read Sminnee\WorkflowMax\Model\Contact $Contact
 * @property-read array $Jobs
 * @property-read array $Tasks
 * @property-read array $Costs
 */
class Invoice
{
    use ModelBase;
    /**
     * @param $data
     *
     * @return mixed
     * @throws \Exception
     */
    function processData($data) {
        if(isset($data['Client'])) {
            $data['Client'] = $this->connector
                ->client()
                ->byStub($data['Client']);
        }
        if(isset($data['Contact'])) {
            $data['Contact'] = $this->connector
                ->contact()
                ->byStub($data['Contact']);
        }
        if(isset($data['Date'])) {
            $data['Date'] = new Datetime($data['Date']);
        }
        if(isset($data['DueDate'])) {
            $data['DueDate'] = new Datetime($data['DueDate']);
        }
        return $data;
    }
}
