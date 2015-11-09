<?php

namespace Sminnee\WorkflowMax;

use GuzzleHttp\Client as Guzzle;
use SimpleXMLElement;
use IteratorAggregate;

/**
 * Represents a call to an XML API.
 * Evaluated lazily
 */
class ApiCall implements IteratorAggregate
{

    protected $url;
    protected $fetcher;
    protected $dataProcessor;

    /**
     * Create a lazy-loaded API call
     * @param string $url The absolute URL to fetch
     * @param GuzzleHttp\Client $fetcher The API client to call
     */
    function __construct($url, Guzzle $fetcher, callable $dataProcessor) {
        $this->url = $url;
        $this->fetcher = $fetcher;
        $this->dataProcessor = $dataProcessor;
    }

    /**
     * Fetch the data from this API
     * @return array Array representation of the XML data
     */
    function data() {
        $result = $this->fetcher->get($this->url);

        if ($result->getStatusCode() == 200) {
            $xml = new SimpleXMLElement($result->getBody());

            // WorkflowMax API's standard for error reporting
            if((string)$xml->Status !== 'OK') {
                throw new \LogicException((string)$xml->ErrorDescription);
            }

            $array = json_decode(json_encode($xml), true);

            if($this->dataProcessor) {
                $dp = $this->dataProcessor;
                $array = $dp($array);
            }

            return $array;
        }

        throw new \LogicException('URL returned status code ' . $result->getStatusCode());
    }

    /**
     * Loop on each record of the result
     */
    function getIterator() {
        foreach($this->data() as $record) {
            yield $record;
        }
    }
}
