<?php

namespace Themagnet\Productimport\Model\Import\ErrorProcessing;

class ProcessingErrorAggregator extends \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator
{
    

    public function updateError($errorCode, $errorLevel, $rowNumber, $columnName, $errorMessage, $errorDescription)
    {
        if (isset($this->items['rows'][$rowNumber])) {
            $newError = $this->errorFactory->create();
            $newError->init($errorCode, $errorLevel, $rowNumber, $columnName, $errorMessage, $errorDescription);
            $this->items['rows'][$rowNumber][0] = $newError;
            $this->items['codes'][$errorCode][0] = $newError;
            $this->items['messages'][$errorMessage][0] = $newError;
            return $this;
           
        }

        return $this;
    }
}
