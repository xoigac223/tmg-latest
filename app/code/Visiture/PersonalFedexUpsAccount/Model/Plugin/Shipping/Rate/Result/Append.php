<?php
namespace Visiture\PersonalFedexUpsAccount\Model\Plugin\Shipping\Rate\Result;

class Append
{
    const PRODUCT_CHARGE         = "carriers/pfedexups/product3partyfeeattr";

    const EXCLUDED_BRAND_OPTIONS = "carriers/pfedexups/productbrandoptionstoexclude";

    /**
     * @var \Magento\Checkout\Model\Session|\Magento\Backend\Model\Session\Quote
    */
    protected $session;
   /**
   * @var \Magento\Framework\App\Config\ScopeConfigInterface
   */
   protected $scopeConfig;

   protected $_storeManager;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
    */
    protected $_product;
   
    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Backend\Model\Session\Quote $backendQuoteSession
     * @param \Magento\Framework\App\State $state
     * @internal param Session $session
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession,
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $product        
    ) {
        if ($state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $this->session          = $backendQuoteSession;
        } else {
            $this->session          = $checkoutSession;
        }
        $this->scopeConfig          = $scopeConfig;
        $this->_product             = $product;
        $this->_storeManager        = $storeManager;
    }

    /**
     * Validate each shipping method before append.
     * Apply the rules action if validation was successful.
     * Can mark some rules as disabled. The disabled rules will be removed in the class
     * @see MageWorx\ShippingRules\Model\Plugin\Shipping\Rate\Result\GetAllRates
     * by checking the value of this mark in the rate object.
     *
     * NOTE: If you have some problems with the rules and the shipping methods, start debugging from here.
     *
     * @param \Magento\Shipping\Model\Rate\Result $subject
     * @param \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult|\Magento\Shipping\Model\Rate\Result $result
     * @return array
     */
    public function beforeAppend($subject, $result)
    {
        if (!$result instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
            return [$result];
        }
        $methodCode      = $result->getCarrier();

        if($this->checkItemsValidation()){
            $filtableMethods = ['ups','fedex'];
            if(!in_array($methodCode,$filtableMethods)){
                return [$result];
            }
            $result->setIsDisabled(true);
        } else {
            $filtableMethods = ['tmgshipping','pfedexups'];
            if(in_array($methodCode,$filtableMethods)){
                return [$result];
            }
            $result->setIsDisabled(true);            
        }
        return [$result];
    }

    protected function getProduct3rdPartyChargeAttr()
    {
        return $this->scopeConfig->getValue(self::PRODUCT_CHARGE,\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
    }

    protected function getExcludedOption()
    {
        return $this->scopeConfig->getValue(self::EXCLUDED_BRAND_OPTIONS,\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
    }

    protected function checkItemsValidation()
    {
        if($this->getExcludedOption()){
            $excludedOptions = explode(",",$this->getExcludedOption());
            foreach ($this->session->getQuote()->getAllVisibleItems() as $key => $item) {
                try{
                    $product = $this->_product->getById($item->getProductId());
                    if(in_array($product->getData(\Visiture\PersonalFedexUpsAccount\Model\Config\Source\ExcludedOptions::ATTR_NAME), $excludedOptions))
                        return false;
                } catch(\Exception $e){
                }
            }
        }
        return true;
    }
    protected function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}