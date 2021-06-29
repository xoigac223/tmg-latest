<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCT_TABS
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\Producttabsslider\Model\ResourceModel\ProductTabsValueInt;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'attribute_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Itoris\Producttabsslider\Model\ProductTabsValueInt', 'Itoris\Producttabsslider\Model\ResourceModel\ProductTabsValueInt');
    }
    public function addItem(\Magento\Framework\DataObject $item)
    {
        $itemId = $this->_getItemId($item);

        if ($itemId !== null) {
            if (isset($this->_items[$itemId])) {
                $count =  count($this->_items);
                $this->_items[($count+1)] = $item;

            }else {
                $this->_items[$itemId] = $item;
            }
        } else {
            $this->_addItem($item);
        }
        return $this;
    }
}