<?php

namespace Visiture\Achtandcss\Model\Payment;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use TMG\Base\Model\Soap\Client;
use TMG\Customer\Helper\Customer as CustomerHelper;


class CcApi extends Client
{
    /**
     * @var CustomerHelper
     */
    protected $customerHelper;
    
    const WSDL_URL = "payment/cc/wsdl_url";
    
    protected $xmlConfigPathSoapWsdlUrl = self::WSDL_URL;
    
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        AppState $state,
        CacheInterface $cache,
        CustomerHelper $customerHelper,
        array $data = []
    ){
        $this->customerHelper = $customerHelper;
        parent::__construct($scopeConfig, $logger, $state, $cache, $data);
    }
    
    
    public function doCcCaptureRequest($data)
    {
        
        if ($customer = $this->customerHelper->getCustomerById($data['encryptedAccount'])) {
            
            $encryptAccount = $this->customerHelper->getEncryptAccount($customer);
            // Update User
            if (!$encryptAccount) {
                $this->customerHelper->updateMagentoUserFromApiUser($customer);
            }
            $encryptAccount = $this->customerHelper->getEncryptAccount($customer);
            if (!$encryptAccount) {
                throw new LocalizedException(__('EncryptAccount not set for customer.'));
            }
            
        } else {
            throw new LocalizedException(__('Customer not logged in.'));
        }
        
        $data['svcUser'] = $this->getApiUser();
        $data['svcPassword'] = $this->getApiPass();
        
        $method = 'getCCTransactionAuthorization';
        
        // Cache Implementation
        $cacheKey = $this->getCacheRequestKey($method, $data);
        
        if (!$result = $this->getCacheRequest($cacheKey)) {
            // Load & Save to cache
            $result = $this->parseLookupCaptureRequest($this->call($method, $data));
            $this->saveCacheRequest($cacheKey, (array)$result);
        }
        return $result;
    }
    
    public function parseLookupCaptureRequest($rawResponse)
    {
        if (empty($rawResponse->getCCTransactionAuthorizationResult) || !$rawResponse->getCCTransactionAuthorizationResult->Success) {
            throw new LocalizedException(__('API Service Error.'));
        }
        return $rawResponse->getCCTransactionAuthorizationResult;
    }
}