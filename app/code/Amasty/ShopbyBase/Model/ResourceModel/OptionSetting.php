<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model\ResourceModel;

use \Magento\Store\Model\Store;

class OptionSetting extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * OptionSetting protected constructor
     */
    protected function _construct()
    {
        $this->_init('amasty_amshopby_option_setting', 'option_setting_id');
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getAllFeaturedOptionsArray($storeId)
    {
        $options = [];
        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->getMainTable()],
            [$this->getIdFieldName(), 'value', 'store_id', 'filter_code', 'is_featured']
        )->where(
            'store_id IN(?)',
            [Store::DEFAULT_STORE_ID, $storeId]
        );

        $result = $this->getConnection()->fetchAll($select);
        foreach ($result as $option) {
            $options[$option['filter_code']][$option['value']][$option['store_id']] = $option['is_featured'];
        }

        return $options;
    }
}
