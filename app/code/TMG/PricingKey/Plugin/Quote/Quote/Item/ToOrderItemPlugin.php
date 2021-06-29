<?php

namespace TMG\PricingKey\Plugin\Quote\Quote\Item;

use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use TMG\PricingKey\Helper\PricingKey as PricingKeyHelper;

class ToOrderItemPlugin
{
    public function aroundConvert(ToOrderItem $subject, \Closure $proceed, AbstractItem $orderItem, $additional = [])
    {
        /** @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem->setData(PricingKeyHelper::ITEM_ATTRIBUTE_PRICING_KEY, $orderItem->getData(PricingKeyHelper::ITEM_ATTRIBUTE_PRICING_KEY));
        $orderItem = $proceed($orderItem, $additional);
        return $orderItem;
    }
}