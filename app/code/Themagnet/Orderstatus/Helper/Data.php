<?php
namespace Themagnet\Orderstatus\Helper;

use Magento\Framework\App\ObjectManager;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function getEnableModule()
    {
        return $this->scopeConfig->getValue('orderstatus/general/enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSvcUser()
    {
        //return 'obinvapi';
        return $this->scopeConfig->getValue('orderstatus/general/svc_user',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getWsdlUrl()
    {
        //return 'https://api.themagnetgroup.com/MagOrderStatus/MagOrderStatusService.svc?wsdl';
        return $this->scopeConfig->getValue('orderstatus/general/wsdl_url',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getSvcPassword()
    {
        //return 'ap10b1nv';
        return $this->scopeConfig->getValue('orderstatus/general/svc_password',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getAccount()
    {
        //return 'A+29Glftimg=';
        return $this->scopeConfig->getValue('orderstatus/general/svc_account',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getReq()
    {
        return array('QueryType'=>2,'ReferenceNumber'=>'MG0927620');
    }
}   