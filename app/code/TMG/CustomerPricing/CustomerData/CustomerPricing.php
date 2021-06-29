<?php
namespace TMG\CustomerPricing\CustomerData;

class CustomerPricing implements \Magento\Customer\CustomerData\SectionSourceInterface
{
    public function getSectionData()
    {
        return [];
    }
}