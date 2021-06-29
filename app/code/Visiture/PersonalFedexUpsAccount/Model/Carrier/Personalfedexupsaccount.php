<?php


namespace Visiture\PersonalFedexUpsAccount\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class Personalfedexupsaccount extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    const CODE = 'pfedexups';

    protected $_code = self::CODE;

    protected $_isFixed = true;

    protected $_rateResultFactory;

    protected $_rateMethodFactory;

    protected $_customerSession;

    protected $_checkoutSession;

    protected $_productRepo;


    protected $_quote;

    protected $_total;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepo,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_productRepo = $productRepo;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    protected function getQuote()
    {
        if (null === $this->_quote) {
            $this->_quote = $this->_checkoutSession->getQuote();
        }
        return $this->_quote;
    }

    protected function getCustomerCheckAttr()
    {
        return $this->getConfigData("allow3partychargeattr");
    }

    protected function getProduct3rdPartyChargeAttr()
    {
        return $this->getConfigData("product3partyfeeattr");
    }

    protected function getExcludedOption()
    {
        return $this->getConfigData("productbrandoptionstoexclude");
    }

    protected function checkItemsValidation()
    {
        if($this->getExcludedOption()){
            $excludedOptions = explode(",",$this->getExcludedOption());
            foreach ($this->getQuote()->getAllVisibleItems() as $key => $item) {
                try{
                    $product = $this->_productRepo->getById($item->getProductId());
                    if(in_array($product->getData(\Visiture\PersonalFedexUpsAccount\Model\Config\Source\ExcludedOptions::ATTR_NAME), $excludedOptions))
                        return false;
                }catch(\Exception $e){}
            }
        }

        return true;
    }

    protected function getTotalCharge()
    {
        if(!isset($this->_total))
        {
            $feeAttr = $this->getProduct3rdPartyChargeAttr();
            $DefaultCharge = $this->getConfigData('price');
            foreach ($this->getQuote()->getAllVisibleItems() as $key => $item) {
                try{
                    $product = $this->_productRepo->getById($item->getProductId());
                    $this->_total += $product->getData($feeAttr)?$product->getData($feeAttr):$DefaultCharge;
                }catch(\Exception $e){
                }
            }
        }
        return $this->_total;
    }

    protected function isAllowChargeFreight3rdpartyHandling()
    {
        if(!$this->_customerSession->isLoggedIn())
            return false;

        if($this->getCustomerCheckAttr() && $this->getProduct3rdPartyChargeAttr()){
            if($this->_customerSession->getCustomer()->getData($this->getCustomerCheckAttr())){
                if($this->checkItemsValidation())
                    return true;
            }
        }
        return false;
    }

    public function isActive()
    {
        $active = $this->getConfigData('active');

        if($this->isAllowChargeFreight3rdpartyHandling())
            return $active == 1 || $active == 'true';
        else
            return false;
    }

    /**
     * {@inheritdoc}
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->isActive()) {
            return false;
        }

        $shippingPrice = $this->getTotalCharge();

        $result = $this->_rateResultFactory->create();

        if ($shippingPrice !== false) {
            $method = $this->_rateMethodFactory->create();

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($this->_code);
            $method->setMethodTitle($this->getConfigData('name'));

            if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
                $shippingPrice = '0.00';
            }

            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);

            $result->append($method);
        }

        return $result;
    }

    /**
     * getAllowedMethods
     *
     * @param array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
