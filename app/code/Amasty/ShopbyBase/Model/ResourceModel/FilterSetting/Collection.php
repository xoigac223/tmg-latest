<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model\ResourceModel\FilterSetting;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Collection protected constructor
     */
    protected function _construct()
    {
        $this->_init(
            \Amasty\ShopbyBase\Model\FilterSetting::class,
            \Amasty\ShopbyBase\Model\ResourceModel\FilterSetting::class
        );
    }
}
