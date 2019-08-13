<?php

    namespace Sminnee\WorkflowMax\Model;

    use Datetime;
    use Sminnee\WorkflowMax\ApiCall;

    /**
     * Represents a single quote
     *
     * @property-read string $ID
     * @property-read string $Type
     * @property-read string $State
     * @property-read Datetime $Date
     * @property-read Datetime $ValidDate
     * @property-read string $Budget
     * @property-read string $OptionExplanation
     * @property-read string $LeadID
     * @property-read string $EstimatedCost
     * @property-read string $EstimatesCostTax
     * @property-read string $EstimatedCostIncludingTax
     * @property-read string $Amount
     * @property-read string $AmountTax
     * @property-read string $AmountIncludingTax
    *
     * @property-read Sminnee\WorkflowMax\Model\Client $Client
    * @property-read Sminnee\WorkflowMax\Model\Contact $Contact
    * @property-read array $Tasks
        * @property-read array $Costs
        * @property-read array $Options
        */
    class Quote
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

            if(isset($data['ValidDate'])) {
                $data['ValidDate'] = new Datetime($data['ValidDate']);
            }

            return $data;
        }
    }
