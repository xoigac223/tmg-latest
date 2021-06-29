<?php

namespace Themagnet\Orderstatus\Model\Source;

class Querytype implements \Magento\Framework\Option\ArrayInterface
{
    const PURCHASE_ORDER_NUMBER = 1;
    const CUSTOMER_ORDER_NUMBER = 2;
    
    public function getOptionArray()
    {
        $options = [self::PURCHASE_ORDER_NUMBER => __('Purchase Order Number'), self::CUSTOMER_ORDER_NUMBER => __('Magnet’s customer order number')];
        return $options;
    }
    
    public function getAllOptions()
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);
        return $res;
    }
    
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }
     
    public function toOptionArray()
    {
        return $this->getOptions();
    }

}