<?php

namespace TMG\Shipping\Helper;

use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use TMG\PricingKey\Helper\PricingKey as PricingKeyHelper;

class Config extends AbstractHelper
{
    const ITEM_ATTRIBUTE_SHIPPING_RATE  = 'tmg_shipping_rate';
    const ITEM_ATTRIBUTE_PRICE_KEY      = 'tmg_price_key';
    const ITEM_ATTRIBUTE_ITEM_CODE      = 'tmg_item_code';
    
    /**
     * @var ProductRepository
     */
    protected $productRepository;
    
    /**
     * @var array
     */
    protected $loadedProducts = [];
    
    /**
     * @var array
     */
    protected $itemAttributes = [
        self::ITEM_ATTRIBUTE_ITEM_CODE,
        self::ITEM_ATTRIBUTE_SHIPPING_RATE,
    ];
    
    /**
     * @var PricingKeyHelper
     */
    protected $pricingKeyHelper;
    
    
    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        PricingKeyHelper $pricingKeyHelper
    ){
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->pricingKeyHelper = $pricingKeyHelper;
    }
    
    /**
     * @return array
     */
    public function getItemAttributes()
    {
        return $this->itemAttributes;
    }
    
//    public function getItemPricingKey($item)
//    {
//        return $this->defaultPriceKey;
//    }
    
    public function logData($data)
    {
        $this->_logger->alert($data);
    }
    
    
    /**
     * @param $id
     * @return Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct($id)
    {
        if(!isset($this->loadedProducts[$id])) {
            $this->loadedProducts[$id] = $this->productRepository->getById($id);
        }
        return $this->loadedProducts[$id];
    }
    
    public function shouldSkipAction()
    {
        if($this->_getRequest()->getActionName() == 'load'
            && $this->_getRequest()->getModuleName() == 'customer') {
            return true;
        };
        return false;
    }
    
    /**
     * @param QuoteItem $item
     * @param Product $product
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setItemProductData(QuoteItem $item,Product $product)
    {
        if($this->shouldSkipAction()) {
            return;
        }
        if($parentId = $this->_getRequest()->getParam('product')) {
//            if($parentId == $product->getId()) {
//                return;
//            }
            // Item Code
            $parent = $this->getProduct($parentId);
            $item->setData(self::ITEM_ATTRIBUTE_ITEM_CODE, $parent->getSku());
        }
        return $this;
    }
    
    
    
    
}