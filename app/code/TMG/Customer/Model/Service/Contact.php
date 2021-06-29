<?php

namespace TMG\Customer\Model\Service;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State as AppState;
use Psr\Log\LoggerInterface;
use TMG\Base\Model\Soap\Client;
use TMG\Customer\Exception\ContactServiceException;

class Contact extends Client
{
    protected $xmlConfigPathSoapWsdlUrl = 'tmg_customer/contact_service/wsdl_url';
    
    
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        AppState $state,
        CacheInterface $cache,
        array $data = []
    )
    {
        parent::__construct($scopeConfig, $logger, $state, $cache, $data);
    }
    
    
    /**
     * @param $email
     * @param $encryptAccount
     * @return mixed
     * @throws \Exception
     */
    public function doGetContactDataRequest($email,$encryptAccount)
    {
        $data = [
            'svcUser' => $this->getApiUser(),
            'svcPassword' => $this->getApiPass(),
            'req' => [
                'Email' => $email,
                'EncryptAccount' => $encryptAccount
            ],
        ];
        
        $method = 'getContactData';
        
        // Cache Implementation
        $result = $this->parseGetContactDataRequest($this->call($method,$data));
        
        return $result;
    }
    
    public function parseGetContactDataRequest($rawResponse)
    {
        if(empty($rawResponse->getContactDataResult)) {
            throw new ContactServiceException(__('API Service Error.'));
        }
    
        if(!$rawResponse->getContactDataResult->Success){
            $message = !empty($rawResponse->getContactDataResult->ErrorMessage)
                ? $rawResponse->getContactDataResult->ErrorMessage
                : 'Invalid Contact Data';
            throw new \Exception($message);
        }
        $result = (array)$rawResponse->getContactDataResult;
        
        // Removing Unused Stuff
        if(isset($result['CatalogCounts'])) {
            unset($result['CatalogCounts']);
        }
        
        // Addresses Format
        if(isset($result['Addresses']) && $addressObj = $result['Addresses']->Address) {
            $addresses = [];
            $addressesArray = is_object($addressObj) ? [$addressObj] : $addressObj;
            foreach ($addressesArray as $addressItem) {
                $addresses[] = (array)$addressItem;
            }
            $result['Addresses'] = $addresses;
        }
        return $result;
    }
    
    public function doUpdateContactDataRequest($data)
    {
        $data = [
            'svcUser' => $this->getApiUser(),
            'svcPassword' => $this->getApiPass(),
            'req' => $data
        ];
    
        $method = 'updateContactData';
        
        $result = $this->parseUpdateContactDataRequest($this->call($method,$data));
        
        return $result;
    }
    
    public function parseUpdateContactDataRequest($rawResponse)
    {
        if(empty($rawResponse->updateContactDataResult)) {
            throw new ContactServiceException(__('API Service Error.'));
        }
    
        if(!$rawResponse->updateContactDataResult->Success){
            $message = !empty($rawResponse->updateContactDataResult->ErrorMessage)
                ? $rawResponse->updateContactDataResult->ErrorMessage
                : 'Invalid Login Data';
            throw new \Exception($message);
        }
        $result = (array)$rawResponse->updateContactDataResult->Success;
        return $result;
    }
    
    
    
}