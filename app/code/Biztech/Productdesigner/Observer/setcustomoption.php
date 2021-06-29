<?php

namespace Biztech\Productdesigner\Observer;

use Magento\Framework\Event\ObserverInterface;

class setcustomoption implements ObserverInterface {

    protected $_request;

    public function __construct(
    \Magento\Framework\App\Request\Http $request
    ) {

        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $action = $this->_request->getFullActionName();
         if ($action == 'catalog_product_view') {
             $product = $observer->getProduct();
             $product->setHasOptions(1);
         }
       
    }

}
