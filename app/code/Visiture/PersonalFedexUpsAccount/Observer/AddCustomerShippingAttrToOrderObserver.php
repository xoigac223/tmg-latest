<?php

namespace Visiture\PersonalFedexUpsAccount\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddCustomerShippingAttrToOrderObserver implements ObserverInterface
{
    public function execute(EventObserver $observer)
    {
        $order = $observer->getOrder();
        $quote = $observer->getQuote();

        if ($order->getShippingMethod() != $this->getMethodCode()) {
            return $this;
        }

        $order->setpersonal_ac_number($quote->getpersonal_ac_number());
        $order->setpersonal_ac_type($quote->getpersonal_ac_type());

        return $this;
    }

    protected function getMethodCode()
    {
    	return \Visiture\PersonalFedexUpsAccount\Model\Carrier\Personalfedexupsaccount::CODE."_".\Visiture\PersonalFedexUpsAccount\Model\Carrier\Personalfedexupsaccount::CODE;
    }
}
