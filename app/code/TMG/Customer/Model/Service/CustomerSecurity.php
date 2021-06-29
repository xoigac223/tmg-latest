<?php

namespace TMG\Customer\Model\Service;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use TMG\Base\Model\Soap\Client;


use TMG\Customer\Exception\CustomerSecurityServiceException;

class CustomerSecurity extends Client
{
    protected $xmlConfigPathSoapWsdlUrl = 'tmg_customer/customer_security_service/wsdl_url';
    
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
     * @return bool|null
     * @throws CustomerSecurityServiceException
     */
    public function doValidAccountEmailRequest($email)
    {
        $data = [
            'svcUser' => $this->getApiUser(),
            'svcPassword' => $this->getApiPass(),
            'email' => $email
        ];
        
        $method = 'validAccountEmail';
    
        // Cache Implementation
        $cacheKey = $this->getCacheRequestKey($method,$data);

        if(!$result = $this->getCacheRequest($cacheKey)) {
            // Load & Save to cache
            $result = $this->parseValidAccountEmailRequest($this->call($method,$data));
            $this->saveCacheRequest($cacheKey,$result);
        }
        
        return $result;
    }
    
    /**
     * @param $rawResponse
     * @return bool
     * @throws CustomerSecurityServiceException
     */
    public function parseValidAccountEmailRequest($rawResponse)
    {
        if(empty($rawResponse->validAccountEmailResult) || !$rawResponse->validAccountEmailResult->Success) {
            throw new CustomerSecurityServiceException(__('API Service Error.'));
        }
        return (bool)$rawResponse->validAccountEmailResult->IsValidAccountEmail;
    }
    
    
    public function doLookupEmailRequest($email)
    {
        $data = [
            'svcUser' => $this->getApiUser(),
            'svcPassword' => $this->getApiPass(),
            'email' => $email,
        ];
    
        $method = 'lookupEmail';
    
        // Load & Save to cache
        $result = $this->parseLookupEmailRequest($this->call($method,$data));
        
        return $result;
    }
    
    /**
     * @param $rawResponse
     * @return bool
     * @throws CustomerSecurityServiceException
     */
    public function parseLookupEmailRequest($rawResponse)
    {
        if(empty($rawResponse->lookupEmailResult) || !$rawResponse->lookupEmailResult->Success) {
            throw new CustomerSecurityServiceException(__('API Service Error.'));
        }
        return (bool)$rawResponse->lookupEmailResult->EmailFound;
    }
    
    
    public function doLookupAccountRequest($email,$accountType,$accountId)
    {
        $data = [
            'svcUser' => $this->getApiUser(),
            'svcPassword' => $this->getApiPass(),
            'request' => [
                'IDType' => $accountType,
                'Account' => $accountId,
                'User' => $email,
            ]
        ];
    
        $method = 'lookupAccount';
    
        // Cache Implementation
        $cacheKey = $this->getCacheRequestKey($method,$data);
    
        if(!$result = $this->getCacheRequest($cacheKey)) {
            // Load & Save to cache
            $result = $this->parseLookupAccountRequest($this->call($method,$data));
            $this->saveCacheRequest($cacheKey,$result);
        }
        return $result;
    }
    
    /**
     * @param $rawResponse
     * @return array
     * @throws CustomerSecurityServiceException
     */
    public function parseLookupAccountRequest($rawResponse)
    {
        if(empty($rawResponse->lookupAccountResult) || !$rawResponse->lookupAccountResult->Success) {
            throw new CustomerSecurityServiceException(__('API Service Error.'));
        }
        if(!$rawResponse->lookupAccountResult->AccountID){
            throw new \Exception('Record does not match.');
        }
        return (array)$rawResponse->lookupAccountResult;
    }
    
    /**
     * @param $email
     * @param $pass
     * @return mixed
     * @throws CustomerSecurityServiceException
     */
    public function doCustomerLoginRequest($email,$pass)
    {
        $data = [
            'svcUser' => $this->getApiUser(),
            'svcPassword' => $this->getApiPass(),
            'request' => [
                'Password' => $pass,
                'User' => $email,
            ]
        ];
        
        $method = 'customerLogin';
    
        // Cache Implementation
        $result = $this->parseCustomerLoginRequest($this->call($method,$data));
        
        return $result;
    }
    
    /**
     * @param $rawResponse
     * @return mixed
     * @throws CustomerSecurityServiceException
     */
    public function parseCustomerLoginRequest($rawResponse)
    {
        if(empty($rawResponse->customerLoginResult)) {
            throw new CustomerSecurityServiceException(__('API Service Error.'));
        }
        if(!$rawResponse->customerLoginResult->Success){
            $message = !empty($rawResponse->customerLoginResult->ErrorMessage)
                ? $rawResponse->customerLoginResult->ErrorMessage
                : 'Invalid Login Data';
            throw new \Exception($message);
        }
        $result = (array)$rawResponse->customerLoginResult;
       
        if(isset($result['SalesTaxRates']) && isset($result['SalesTaxRates']->SalesTaxRate)) {
            $rates = [];
            foreach ($result['SalesTaxRates']->SalesTaxRate as $salesRate) {
                $rates[] = (array)$salesRate;
            }
            $result['SalesTaxRates'] = $rates;
        }
        
        return $result;
    }
    
    
    /**
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function doCreateLoginRequest($data)
    {
        $data = [
            'svcUser' => $this->getApiUser(),
            'svcPassword' => $this->getApiPass(),
            'request' => $data,
        ];
        
        $method = 'createLogin';
        
        // Cache Implementation
        $result = $this->parseCreateLoginRequest($this->call($method,$data));
        
        return $result;
    }
    
    /**
     * @param $rawResponse
     * @return array
     * @throws CustomerSecurityServiceException
     */
    public function parseCreateLoginRequest($rawResponse)
    {
        if(empty($rawResponse->createLoginResult)) {
            throw new CustomerSecurityServiceException(__('API Service Error.'));
        }
        
        if(!$rawResponse->createLoginResult->LoginVerified){
            $message = !empty($rawResponse->createLoginResult->ErrorMessage)
                ? $rawResponse->createLoginResult->ErrorMessage
                : 'Invalid Login Data';
            throw new \Exception($message);
        }
        $result = (array)$rawResponse->createLoginResult;
        return $result;
    }
    
    /**
     * @param $data
     * @return array
     * @throws CustomerSecurityServiceException
     */
    public function doChangePwdRequest($data)
    {
        $data = [
            'svcUser' => $this->getApiUser(),
            'svcPassword' => $this->getApiPass(),
            'request' => $data,
        ];
    
        $method = 'changePwd';
        $result = $this->parseChangePwdRequest($this->call($method,$data));
        return $result;
    }
    
    /**
     * @param $rawResponse
     * @return array
     * @throws CustomerSecurityServiceException
     */
    public function parseChangePwdRequest($rawResponse)
    {
        if(empty($rawResponse->changePwdResult)) {
            throw new CustomerSecurityServiceException(__('API Service Error.'));
        }
        $result = (array)$rawResponse->changePwdResult;
        return $result;
    }
    
    
    /**
     * @param $email
     * @return bool
     * @throws CustomerSecurityServiceException
     */
    public function doResetPwdRequestRequest($email)
    {
        $data = [
            'svcUser' => $this->getApiUser(),
            'svcPassword' => $this->getApiPass(),
            'request' => [
                'User' => $email
            ]
        ];
    
        $method = 'resetPwdRequest';
        $result = $this->parseResetPwdRequestRequest($this->call($method,$data));
        return $result;
    }
    
    /**
     * @param $rawResponse
     * @return bool
     * @throws CustomerSecurityServiceException
     */
    public function parseResetPwdRequestRequest($rawResponse)
    {
        if(empty($rawResponse->resetPwdRequestResult)) {
            throw new CustomerSecurityServiceException(__('API Service Error.'));
        }
        $result = (bool)$rawResponse->resetPwdRequestResult->Success;
        return $result;
    }
    
    /**
     * @param $requestId
     * @return mixed
     * @throws LocalizedException
     */
    public function doCheckPwdRequest($requestId)
    {
        $data = [
            'svcUser' => $this->getApiUser(),
            'svcPassword' => $this->getApiPass(),
            'requestID' => $requestId
        ];
        $method = 'checkPwdRequest';
        $result = $this->parseCheckPwdRequest($this->call($method,$data));
        return $result;
    }
    
    /**
     * @param $rawResponse
     * @return bool
     * @throws CustomerSecurityServiceException
     * @throws LocalizedException
     */
    public function parseCheckPwdRequest($rawResponse)
    {
        if(empty($rawResponse->checkPwdRequestResult)) {
            throw new CustomerSecurityServiceException(__('API Service Error.'));
        }
        if(!$rawResponse->checkPwdRequestResult->Success) {
            throw new LocalizedException(__($rawResponse->checkPwdRequestResult->ErrorMessage));
        }
        $result = (bool)$rawResponse->checkPwdRequestResult->UserID;
        return $result;
    }
    
    
    /**
     * @param $requestId
     * @param $password
     * @return bool
     * @throws CustomerSecurityServiceException
     * @throws LocalizedException
     */
    public function doResetPwdRequest($requestId,$password)
    {
        $data = [
            'svcUser' => $this->getApiUser(),
            'svcPassword' => $this->getApiPass(),
            'request' => [
                'Password' => $password,
                'RequestID' => $requestId
            ]
        ];
        
        $method = 'resetPwd';
        $result = $this->parseResetPwdRequest($this->call($method,$data));
        return $result;
    }
    
    /**
     * @param $rawResponse
     * @return bool
     * @throws CustomerSecurityServiceException
     * @throws LocalizedException
     */
    public function parseResetPwdRequest($rawResponse)
    {
        if(empty($rawResponse->resetPwdResult)) {
            throw new CustomerSecurityServiceException(__('API Service Error.'));
        }
        if(!$rawResponse->resetPwdResult->Success) {
            throw new LocalizedException(__($rawResponse->resetPwdResult->ErrorMessage));
        }
        $result = (bool)$rawResponse->resetPwdResult->Success;
        return $result;
    }
    
    
}