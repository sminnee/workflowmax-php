<?php

    namespace Sminnee\WorkflowMax\Connector;

    use Datetime;

    use Sminnee\WorkflowMax\ApiContact;
    use Sminnee\WorkflowMax\Model\Contact;
    use Sminnee\WorkflowMax\Model\JobList;

    /**
     * A sub-contact responsible for accessing job
     */
    class ContactConnector
    {

        /**
         * @var
         */
        protected $contact;

        /**
         * ContactConnector constructor.
         *
         * @param \Sminnee\WorkflowMax\ApiContact $connector
         */
        function __construct(ApiContact $connector) {
            $this->connector = $connector;
        }

        /**
         * Returns a job by job number.
         *
         * @return Sminnee\WorkflowMax\Model\Contact
         */
        function byId($id) {
            return new Contact($this->connector, $this->connector->apiCall(
                "client.api/contact/get/$id",
                function($result) { return $result['Contact']; }
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
    }
