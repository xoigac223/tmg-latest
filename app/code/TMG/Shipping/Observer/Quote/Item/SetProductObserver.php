<?php

namespace TMG\Shipping\Observer\Quote\Item;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Quote\Model\Quote\Item as QuoteItem;


use TMG\Shipping\Helper\Config as ConfigHelper;

class SetProductObserver implements ObserverInterface
{
    protected $configHelper;
    protected $productRepository;
    
    public function __construct(
        ConfigHelper $configHelper,
        ProductRepository $productRepository
    )
    {
        $this->configHelper = $configHelper;
        $this->productRepository = $productRepository;
    }
    
    public function execute(Observer $observer)
    {
        /* @var $quoteItem QuoteItem */
        $quoteItem = $observer->getQuoteItem();
        /* @var $product Product */
        $product = $observer->getProduct();
        $this->configHelper->setItemProductData($quoteItem,$product);
    }
    
}