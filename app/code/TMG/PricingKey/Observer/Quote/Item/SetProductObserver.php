<?php

namespace TMG\PricingKey\Observer\Quote\Item;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Quote\Model\Quote\Item as QuoteItem;


use TMG\PricingKey\Helper\PricingKey as PricingKeyHelper;

class SetProductObserver implements ObserverInterface
{
    protected $pricingKeyHelper;
    protected $productRepository;
    
    public function __construct(
        PricingKeyHelper $pricingKeyHelper,
        ProductRepository $productRepository
    )
    {
        $this->pricingKeyHelper = $pricingKeyHelper;
        $this->productRepository = $productRepository;
    }
    
    public function execute(Observer $observer)
    {
        /* @var $quoteItem QuoteItem */
        $quoteItem = $observer->getQuoteItem();
        /* @var $product Product */
        $product = $observer->getProduct();
        $this->pricingKeyHelper->setItemProductData($quoteItem,$product);
    }
    
}