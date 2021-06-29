<?php

namespace TMG\CustomerPricing\Model\Service;

use TMG\Base\Model\Soap\Client;
use TMG\CustomerPricing\Exception\CustomerPricingServiceException;

class CustomerPricing extends Client
{
    
    protected $xmlConfigPathSoapWsdlUrl = 'tmg_customer_pricing/customer_pricing_service/wsdl_url';
    
    public function doGetCustomerItemPricingRequest($data)
    {
        $data['svcUser'] = $this->getApiUser();
        $data['svcPassword'] = $this->getApiPass();
        
        $method = 'getCustomerItemPricing';
        
        // Cache Implementation
        $cacheKey = $this->getCacheRequestKey($method,$data);
    
        if(!$result = $this->getCacheRequest($cacheKey)) {
            // Load & Save to cache
            $result = $this->parseGetCustomerItemPricingRequest($this->call($method,$data));
            $this->saveCacheRequest($cacheKey,$result);
        }
    
        return $result;
        
    }
    
    /**
     * @param $rawResponse
     * @return array
     * @throws \Exception
     */
    public function parseGetCustomerItemPricingRequest($rawResponse)
    {
        $response = [];
        
        if(empty($rawResponse->getCustomerItemPricingResult)) {
            $error = __('Unknown Service Error');
            throw new CustomerPricingServiceException($error);
        }
    
        if(!$rawResponse->getCustomerItemPricingResult->Success) {
            $error = __($rawResponse->getCustomerItemPricingResult->ErrorMessage);
            throw new CustomerPricingServiceException($error);
        }
        if($rawResponse->getCustomerItemPricingResult->CustomerItemPricingMatrix){
            foreach((array)$rawResponse->getCustomerItemPricingResult->CustomerItemPricingMatrix->CustomerItemQuantityPrice as $customerItemQuantityPrice) {
                $response[] = (array)$customerItemQuantityPrice;
            }
        }
        
        return $response;
        
    }
    
}