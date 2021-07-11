<?php

    namespace Sminnee\WorkflowMax\Model;


    /**
     * Represents a single cost
     *
     * @property-read string $ID
     * @property-read string $Description
     * @property-read string $Cost
     * @property-read string $Note
     * @property-read string $UnitCost
     * @property-read string $UnitPrice
     * @property-read $Supplier
     */
    class Cost
    {
        use ModelBase;


        function processData($data) {

            return $data;
        }
    }
