<?php

    namespace Sminnee\WorkflowMax\Connector;

    use Datetime;

    use Sminnee\WorkflowMax\ApiClient;
    use Sminnee\WorkflowMax\Model\Cost;

    /**
     * A sub-client responsible for accessing cost
     */
    class CostConnector extends TypeConnector
    {
        protected $connector;

        function __construct(ApiClient $connector) {
            $this->connector = $connector;
        }

        /**
         * Returns a cost byId cost number.
         *
         * @return Sminnee\WorkflowMax\Model\Cost
         */
        function byId($cost) {
            return new Cost($this->connector, $this->connector->apiCall(
                "cost.api/get/$cost",
                function($result) { return $result['Cost']; }
            ));
        }


        /**
         * @param $stubData
         *
         * @return mixed
         */
        function byStub($stubData) {
            return $this->byId($stubData['ID'])->populate($stubData);
        }

        /**
         * @param int $page
         *
         * @return mixed
         */
        function all($page = 1) {
            return $this->listFromApiCall($this->connector->apiCall('cost.api/list?page='.$page, function($result) {
                return isset($result['Costs']['Cost']) ? $result['Costs']['Cost'] : [];
            }));
        }
    }
