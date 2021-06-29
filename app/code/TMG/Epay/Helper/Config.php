<?php

namespace TMG\Epay\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use TMG\Customer\Helper\Customer as CustomerHelper;

class Config extends AbstractHelper
{
    const XML_CONFIG_SSO_URL = 'tmg_epay/sso/url';
    const XML_CONFIG_SSO_STORE_ID = 'tmg_epay/sso/store_id';
    const XML_CONFIG_SSO_CUSTOMER_TYPE = 'tmg_epay/sso/customer_type';
    
    /**
     * @var CustomerHelper
     */
    protected $customerHelper;
    
    public function __construct(
        Context $context,
        CustomerHelper $customerHelper
    ){
        $this->customerHelper = $customerHelper;
        parent::__construct($context);
    }
    
    public function getConfig($path, $store = null)
    {
        return $this->scopeConfig->getValue($path,ScopeConfigInterface::SCOPE_TYPE_DEFAULT,$store);
    }
    
    public function getSsoUrl($store = null)
    {
        return $this->getConfig(self::XML_CONFIG_SSO_URL,$store);
    }
    
    public function getSsoCustomerType($store = null)
    {
        return $this->getConfig(self::XML_CONFIG_SSO_CUSTOMER_TYPE,$store);
    }
    
    public function getSsoStoreId($store = null)
    {
        return $this->getConfig(self::XML_CONFIG_SSO_STORE_ID,$store);
    }
    
    public function getCustomerSSOLinkUrl()
    {
        if(null ==  $this->customerHelper->getEncryptAccount()) {
            return null;
        }
        return __($this->getSsoUrl() . '?a1=%1&var3=%2&var4=%3'
            , urlencode($this->customerHelper->getEncryptAccount())
            , $this->getSsoStoreId()
            , $this->getSsoCustomerType()
        );
    }
    
    
    
    
    
}