<?php

    namespace Sminnee\WorkflowMax\Connector;

    use Sminnee\WorkflowMax\ApiClient;
    use Sminnee\WorkflowMax\Model\Quote;

    /**
     * A sub-client responsible for accessing quote
     */
    class QuoteConnector extends TypeConnector
    {
        /**
         * @var \Sminnee\WorkflowMax\ApiClient
         */
        protected $connector;

        /**
         * QuoteConnector constructor.
         *
         * @param \Sminnee\WorkflowMax\ApiClient $connector
         */
        function __construct(ApiClient $connector) {
            $this->connector = $connector;
        }

        /**
         * Returns a quote byId quote number.
         *
         * @return Sminnee\WorkflowMax\Model\Quote
         */
        function byId($quote) {
            return new Quote($this->connector, $this->connector->apiCall(
                "quote.api/get/$quote",
                function($result) {
                    return $result['Quote'];
                }
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
         * @return mixed
         */
        function current() {
            return $this->listFromApiCall($this->connector->apiCall('quote.api/current', function($result) {
                return isset($result['Quotes']['Quote']) ? $result['Quotes']['Quote'] : [];
            }));
        }
    }
