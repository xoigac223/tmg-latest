<?php

namespace Biztech\Productdesigner\Observer;

use Magento\Framework\Event\ObserverInterface;

class salesConvert implements ObserverInterface {

    protected $_request;
    protected $cart;

    public function __construct(

    \Magento\Framework\App\Request\Http $request,
            \Magento\Checkout\Model\Cart $cart

    ) {

        $this->_request = $request;
        $this->cart = $cart;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {

        

        $orderItems = $observer->getOrder()->getAllItems();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customCart = $objectManager->create('\Magento\Checkout\Model\Cart');
        $carts = $customCart->getQuote();
        $i = 0;
        foreach ($carts->getAllItems() as $item) {
            if ($additionalOptions = $item->getOptionByCode('additional_options')) {
                $options = $orderItems[$i]->getProductOptions();
                $options['additional_options'] = unserialize($additionalOptions->getValue());
                $orderItems[$i]->setProductOptions($options);
            }
            $i++;
        }

    }

}
