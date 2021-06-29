<?php

namespace TMG\CustomerPricing\Plugin\Itoris\DynamicProductOptions\Model\Rewrite\Option\Type;

use Itoris\DynamicProductOptions\Model\Rewrite\Option\Type\Select;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;
use TMG\CustomerPricing\Helper\CustomerPricing;
use TMG\PricingKey\Helper\PricingKey;

class SelectPlugin
{
    const REGISTRY_NS_COLOR_CHARGE = 'tmg_customer_pricing_color_charge';
    
    /**
     * @var CustomerPricing
     */
    protected $customerPricingHelper;
    
    /**
     * @var PricingKey
     */
    protected $pricingKeyHelper;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * @var ProductRepository
     */
    protected $productRepository;
    
    /**
     * @var array
     */
    protected $customerPricing = [];
    
    /**
     * @var Product
     */
    protected $product;
    
    /**
     * @var Registry
     */
    protected $registry;
    
    
    public function __construct(
        PricingKey $pricingKeyHelper,
        CustomerPricing $customerPricingHelper,
        LoggerInterface $logger,
        ProductRepository $productRepository,
        Registry $registry
    )
    {
        $this->pricingKeyHelper = $pricingKeyHelper;
        $this->customerPricingHelper = $customerPricingHelper;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
    }
    
    /**
     * @param $id
     * @return \Magento\Catalog\Api\Data\ProductInterface|Product|mixed
     */
    public function getProduct($id)
    {
        if(!$this->product || $this->product->getId() != $id) {
            try {
                $this->product = $this->productRepository->getById($id);
            } catch (NoSuchEntityException $e) {}
        }
        return $this->product;
    }
    
    /******************************************************************************************************************/
    
    /**
     * @param $productId
     * @return mixed
     */
    public function getCustomerPricing($productId)
    {
        if(!isset($this->customerPricing[$productId])) {
            $this->customerPricing[$productId] = $this->customerPricingHelper->getProductCustomerPricing($this->getProduct($productId));
        }
        return $this->customerPricing[$productId];
    }
    
    /**
     * @param $productId
     * @return bool
     */
    public function hasCustomerPricing($productId)
    {
        // Check Logged in
        // Check Customer Table
        return (bool)$this->getCustomerPricing($productId);
    }
    
    /******************************************************************************************************************/
    
    public function setColorChargePrice($price)
    {
//        $this->logger->info(':: SET COLOR CHARGE :: PRICE :: ' . $price);
        if(!is_null($this->registry->registry(self::REGISTRY_NS_COLOR_CHARGE))) {
            $this->registry->unregister(self::REGISTRY_NS_COLOR_CHARGE);
        }
        $this->registry->register(self::REGISTRY_NS_COLOR_CHARGE,$price);
        return $this;
    }
    
    public function getColorChargePrice()
    {
        $result = null;
        if(!is_null($this->registry->registry(self::REGISTRY_NS_COLOR_CHARGE))) {
            $result = $this->registry->registry(self::REGISTRY_NS_COLOR_CHARGE);
//            $this->registry->unregister(self::REGISTRY_NS_COLOR_CHARGE);
        }
//        $this->logger->info(':: GET COLOR CHARGE :: RESULT' . $result);
        return $result;
    }
    
    /**
     * @param $tierPrices
     * @param $qty
     * @return null
     */
    public function getTierPrice($tierPrices,$qty)
    {
        krsort($tierPrices);
//        $this->logger->info(__METHOD__);
        foreach ($tierPrices as $tierQty => $data) {
            if($qty >= $tierQty) {
//                $this->logger->info(print_r([$data,$tierQty,$qty],true));
                $this->setColorChargePrice($data['color_price']);
                return $data['item_price'];
            }
        }
        return null;
    }

    public function getCustomerPricingPriceFor($productId,$option,$valueId,$qty)
    {
        $customerPricing = $this->getCustomerPricing($productId);
        $itemLabel = $option->getValueById($valueId)->getTitle();
        $price = null;
        switch (true) {
            case $this->pricingKeyHelper->isTmgBuyOptionOption($option):
//                $this->logger->info(' :: :: isTmgBuyOptionOption');
                if($this->pricingKeyHelper->isOrderSampleOptionValueLabel($itemLabel)
                    && isset($customerPricing[PricingKey::ORDER_SAMPLE_PRICING_KEY])) {
                    $price = $this->getTierPrice(PricingKey::ORDER_SAMPLE_PRICING_KEY,$qty);
                }
//                $this->logger->info(' :: PRICE :: ' . $price);
                break;
            case $this->pricingKeyHelper->isTmgPrintMethodOption($option):
//                $this->logger->info(' :: :: isTmgPrintMethodOption');
                $pricingKey = $this->pricingKeyHelper->getPricingKeyByLabel($this->getProduct($productId),$itemLabel);
                if($pricingKey && isset($customerPricing[$pricingKey])) {
                    $price =  $this->getTierPrice($customerPricing[$pricingKey],$qty);
                }
//                $this->logger->info(' :: PRICE :: ' . $price);
                break;
            case $this->pricingKeyHelper->isTmgColorChargeOption($option):
                $price = $this->getColorChargePrice();
//                $this->logger->info(' :: :: isTmgColorChargeOption');
//                $this->logger->info(' :: PRICE :: ' . $price);
                break;
            default:
//                $this->logger->info(' :: :: NO ES NADA');
                break;
        }
        return $price;
    }
    
    public function aroundGetTierPriceByQty(Select $subject,callable $proceed, $valueId, $qty, $price, $price_type)
    {
        $result = $proceed($valueId, $qty, $price, $price_type);
        $productId = $subject->getOption()->getProduct()->getId();
        if($this->hasCustomerPricing($productId)); {
            $origPrice = $result[0];
            $result[0] = $this->getCustomerPricingPriceFor($productId,$subject->getOption(),$valueId,$qty) ?: $origPrice;
        }
        
//        $data = print_r([
//            'param_value_id' => $valueId,
//            'param_qty' => $qty,
//            'param_price' => $price,
//            'param_price_type' => $price_type,
//            'product_id' => $productId,
//            'option_title' => $subject->getOption()->getTitle(),
//            'value_title' => $subject->getOption()->getValueById($valueId)->getTitle(),
//            '$result' => $result,
//        ],true);
//        $this->logger->warning($data);
//        $this->logger->warning(' - - - - - - - - - ');
        
        return $result;
    }
}