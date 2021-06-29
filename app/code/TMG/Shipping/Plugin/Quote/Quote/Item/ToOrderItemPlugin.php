<?php

namespace TMG\Shipping\Plugin\Quote\Quote\Item;

use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use TMG\Shipping\Helper\Config as ConfigHelper;

class ToOrderItemPlugin
{
    /**
     * @var ConfigHelper
     */
    protected $configHelper;
    
    /**
     * ToOrderItemPlugin constructor.
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ConfigHelper $configHelper
    )
    {
        $this->configHelper = $configHelper;
    }
    
    public function aroundConvert(ToOrderItem $subject, \Closure $proceed, AbstractItem $item, $additional = [])
    {
        /** @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = $proceed($item, $additional);
        foreach ($this->configHelper->getItemAttributes() as $attributeCode) {
            $orderItem->setData($attributeCode, $item->getData($attributeCode));
        }
        return $orderItem;
    }
    
}