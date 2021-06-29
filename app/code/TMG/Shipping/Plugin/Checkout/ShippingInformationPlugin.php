<?php

namespace TMG\Shipping\Plugin\Checkout;

use Magento\Checkout\Model\ShippingInformation;
use TMG\Shipping\Model\Api\FreightEstimates;

class ShippingInformationPlugin
{
    /**
     * @var FreightEstimates
     */
    protected $freightEstimates;
    
    public function __construct(
        FreightEstimates $freightEstimates
    )
    {
        $this->freightEstimates = $freightEstimates;
    }
    
    /**
     * @param ShippingInformation $subject
     * @param callable $proceed
     * @param $code
     * @return mixed
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \TMG\Shipping\Model\NoRatesException
     */
    public function aroundSetShippingMethodCode(ShippingInformation $subject, callable $proceed, $code)
    {
        // Regular Stuff
        $result = $proceed($code);
        
        if($this->freightEstimates->isInternalCode($code)) {
            // Add Data
            $this->freightEstimates->setShippingRateToItems($code);
        }
        
        return $result;
    }
    
}