<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Model\Source;

/**
 * Class SliderSort
 * @package Amasty\ShopbyBrand\Model\Source
 * @author Evgeni Obukhovsky
 */
class SliderSort implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'name', 'label' => __('Name')], ['value' => 'position', 'label' => __('Position')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ['name' => __('Name'), 'position' => __('Position')];
    }
}
