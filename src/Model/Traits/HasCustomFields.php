<?php

namespace Sminnee\WorkflowMax\Model\Traits;

trait HasCustomFields
{
    protected $hasResolvedCustomFields = false;
    protected $customFields = [];

    abstract function customFieldConnectorMethodName();

    public function customField($fieldName)
    {
        if (! $this->hasResolvedCustomFields) {
            $this->resolveCustomFields();
        }

        return $this->customFields[$fieldName] ?? null;
    }

    protected function resolveCustomFields()
    {
        $customFields = $this->connector->customField()->{$this->customFieldConnectorMethodName()}($this);

        foreach ($customFields as $customField) {
            if ($customField->Type != "") {
                $this->customFields[$customField->Name] = $customField->{$customField->Type};
            }

        }

        $this->hasResolvedCustomFields = true;
    }
}
