<?php
/**
 * Copyright © 2015 Themagnet. All rights reserved.
 */

namespace Themagnet\Zoomcatalog\Model;

class Zoomcatalog extends \Magento\Framework\Model\AbstractModel 
{
	const API_URL = 'https://api.zoomcatalog.com/';

	public function __construct(
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Encryption\EncryptorInterface $encription,
		\Themagnet\Zoomcatalog\Helper\Data $helperData,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		array $data = []
	) {
		parent::__construct(
			$context,
			$registry
		);
		$this->_scopeConfig = $scopeConfig;
		$this->_helperData = $helperData;
		$this->_encryption = $encription;
		$this->_objectManager = $objectManager;
	}

	public function getCustomerSession()
    {
        $customerSession = $this->_objectManager->create('Magento\Customer\Model\SessionFactory')->create();
        return $customerSession;     
    }

    public function getCsutomerEmail(){
        $this->_customerSession = $this->getCustomerSession();
        $email = '';
        if($this->_customerSession->isLoggedIn()){
            $email = $this->_customerSession->getCustomer()->getEmail();
            
        }
        return $email;
    }

	public function getAuthorizeToken() {

		$apiurl = $this->_scopeConfig->getValue('zoomcatalog/general/apiurl', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
		$username = $this->_scopeConfig->getValue('zoomcatalog/general/client_id', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
		$password = $this->_encryption->decrypt($this->_scopeConfig->getValue('zoomcatalog/general/client_secret', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE));

		$param['grant_type'] = 'authorization_code';
		$param['client_id'] = $username;
		$param['client_secret'] = $password;
		if($this->getCsutomerEmail() != ''){
		//	$param['for_username'] = $this->getCsutomerEmail();
		}
		

		$requestString = json_encode($param);

		$headers = array(
			'Content-Type: application/json',
			'Cache-Control: no-cache',
		);

		$response = $this->_helperData->makeApiRequest($apiurl, $requestString, 'POST', $headers);

		return $response;

	}

	public function getApiCall() 
	{
		$apiurl = $this->_scopeConfig->getValue('zoomcatalog/general/api_services_call', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
		$apiurl = ($apiurl != '')?$apiurl:'catalogs';
		return $apiurl;
	}

	public function getApiUrl() 
	{
		$apiurl = $this->getApiCall();
		return self::API_URL.$apiurl;
	}

	public function getCollection() {
		try {
			$token = $this->getAuthorizeToken();
			if (isset($token['access_token'])) {
				$headers = array(
					'Authorization: ' . $token['access_token'],
					'Content-Type: application/json',
					'Cache-Control: no-cache',
				);
				$urlAuth = $this->getApiUrl();
				if($this->getCsutomerEmail() != ''){
					$urlAuth = $urlAuth.'?for_username='.rawurlencode($this->getCsutomerEmail());
				}
				
				//echo $urlAuth; 
				$response = $this->_helperData->makeApiRequest($urlAuth, null, 'GET', $headers);
				return $response;
			}
		} catch (\Exception $e) {
			throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
		}
	}

	public function getCatalogPageTitle() {
		$CatalogPageTitle = $this->_scopeConfig->getValue('zoomcatalog/general/Catalog_page_title', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
		return $CatalogPageTitle;
	}

	public function getCatalogPageSubTitle() {
		$CatalogPageSubTitle = $this->_scopeConfig->getValue('zoomcatalog/general/Catalog_page_sub_title', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
		return $CatalogPageSubTitle;
	}

}
