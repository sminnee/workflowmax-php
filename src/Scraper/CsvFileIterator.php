<?php

namespace Sminnee\WorkflowMax\Scraper;

class CsvFileIterator implements \IteratorAggregate
{
    protected $filename;
    protected $deleteOnCompletion;

    function __construct($filename, $deleteOnCompletion = false)
    {
        $this->filename = $filename;
        $this->deleteOnCompletion = $deleteOnCompletion;
    }

    function getIterator()
    {
        $fh = fopen($this->filename, 'r');

        $header = fgetcsv($fh);
        while (($row = fgetcsv($fh)) !== false) {
            yield array_combine($header, $row);
        }

        fclose($fh);

        if ($this->deleteOnCompletion) {
            unlink($this->filename);
        }
    }
}
