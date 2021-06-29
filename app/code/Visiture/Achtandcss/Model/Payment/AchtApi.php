<?php

namespace Visiture\Achtandcss\Model\Payment;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use TMG\Base\Model\Soap\Client;
use TMG\Customer\Helper\Customer as CustomerHelper;


class AchtApi extends Client
{
    /**
     * @var CustomerHelper
     */
    protected $customerHelper;
    
    const WSDL_URL = "payment/acht/wsdl_url";
    
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
    
    public function doAchtCaptureRequest($data)
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
            $data['encryptedAccount'] = $encryptAccount;
            $data['customerEmail'] = $customer->getEmail();
        } else {
            throw new LocalizedException(__('Customer not logged in.'));
        }
        
        $data['svcUser'] = $this->getApiUser();
        $data['svcPassword'] = $this->getApiPass();
        
        $method = 'getACHTransactionAuthorization';
        
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
        if (empty($rawResponse->getACHTransactionAuthorizationResult) || !$rawResponse->getACHTransactionAuthorizationResult->Success) {
            throw new LocalizedException(__('API Service Error.'));
        }
        return $rawResponse->getACHTransactionAuthorizationResult;
    }
}