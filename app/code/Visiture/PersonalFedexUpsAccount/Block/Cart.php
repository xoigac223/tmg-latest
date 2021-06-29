<?php

namespace Visiture\PersonalFedexUpsAccount\Block;

use Magento\Customer\Model\Context;

class Cart extends \Magento\Checkout\Block\Cart\AbstractCart
{
    const ALLOW_CUSTOMER = "carriers/pfedexups/allow3partychargeattr";
    const PRODUCT_CHARGE = "carriers/pfedexups/product3partyfeeattr";
    const EXCLUDED_BRAND_OPTIONS = "carriers/pfedexups/productbrandoptionstoexclude";
    const ACCOUNT_NUMBER = "personal_ac_number";
    const ACCOUNT_TYPE   = "personal_ac_type";
    const FORM_ACTION = "visiture/customer/setAccountDetail";
    const SHIPPING_MESSAGE = "carriers/pfedexups/shippingmessage";

    //Account Type
    const ACCOUNT_TYPE_FEDEX   = "FedEx";
    const ACCOUNT_TYPE_UPS   = "UPS";

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var String
     */
    protected $_msg;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_product;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession $checkoutSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $product
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $product,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->_isScopePrivate = true;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_product = $product;
        $this->_storeManager = $context->getStoreManager();
        $this->_msg = '';
    }

    protected function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    protected function getCustomerCheckAttr()
    {
        return $this->_scopeConfig->getValue(self::ALLOW_CUSTOMER,\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
    }

    protected function getProduct3rdPartyChargeAttr()
    {
        return $this->_scopeConfig->getValue(self::PRODUCT_CHARGE,\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
    }

    protected function getExcludedOption()
    {
        return $this->_scopeConfig->getValue(self::EXCLUDED_BRAND_OPTIONS,\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
    }

    protected function checkItemsValidation()
    {
        if($this->getExcludedOption()){
            $excludedOptions = explode(",",$this->getExcludedOption());
            foreach ($this->getQuote()->getAllVisibleItems() as $key => $item) {
                try{
                    $product = $this->_product->getById($item->getProductId());
                    if(in_array($product->getData(\Visiture\PersonalFedexUpsAccount\Model\Config\Source\ExcludedOptions::ATTR_NAME), $excludedOptions))
                        return false;
                }catch(\Exception $e){}
            }
        }

        return true;
    }

    public function getFormAction()
    {
        return $this->getUrl(self::FORM_ACTION);
    }

    public function getCustomMessage()
    {
        return $this->_msg;
    }

    public function isAllowChargeFreight3rdpartyHandling()
    {
        if(!$this->_customerSession->isLoggedIn())
            return false;

        if($this->getCustomerCheckAttr() && $this->getProduct3rdPartyChargeAttr()){
            if($this->_customerSession->getCustomer()->getData($this->getCustomerCheckAttr())){
                if($this->checkItemsValidation())
                    return true;
            }
        }
        else{
            $this->_msg = __("3rd party shipping not configured. Contect to store manager.");
        }
        return false;
    }

    public function getCustomerAccountNumber()
    {
        return $this->_customerSession->getCustomer()->getData();
    }

    public function getAccountNumber()
    {
        return $this->getQuote()->getData(self::ACCOUNT_NUMBER);
    }

    public function getAccountType()
    {
        return $this->getQuote()->getData(self::ACCOUNT_TYPE);
    }

    public function getShippingCode()
    {
        return \Visiture\PersonalFedexUpsAccount\Model\Carrier\Personalfedexupsaccount::CODE;
    }

    public function getAccountTypes()
    {
        return [self::ACCOUNT_TYPE_FEDEX,self::ACCOUNT_TYPE_UPS];
    }

    public function getshippingMessage()
    {
        $msg = $this->_scopeConfig->getValue(self::SHIPPING_MESSAGE,\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
        return $msg?$msg:__("Personal Shipping A/c Information");
    }
}
