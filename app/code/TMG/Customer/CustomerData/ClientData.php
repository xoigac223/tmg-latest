<?php

namespace TMG\Customer\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

class ClientData implements SectionSourceInterface
{
    
    /**
     * Get data
     *
     * @return array
     */
    public function getSectionData()
    {
        return [
            'test_01' => 'Hola',
            'test_02' => 'Pepe',
        ];
    }
}