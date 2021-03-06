<?php
/**
 * Solwin Infotech
 * Solwin Advanced Product Video Extension
 *
 * @category   Solwin
 * @package    Solwin_ProductVideo
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
namespace Solwin\ProductVideo\Model\ResourceModel\Video;

class Collection
extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * ID Field Name
     * 
     * @var string
     */
    protected $_idFieldName = 'video_id';

    /**
     * Event prefix
     * 
     * @var string
     */
    protected $_eventPrefix = 'solwin_productvideo_video_collection';

    /**
     * Event object
     * 
     * @var string
     */
    protected $_eventObject = 'video_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
                'Solwin\ProductVideo\Model\Video',
                'Solwin\ProductVideo\Model\ResourceModel\Video'
                );
        $this->_map['fields']['video_id'] = 'main_table.video_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
        
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);
        return $countSelect;
    }
    /**
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
    protected function _toOptionArray(
        $valueField = 'video_id', 
        $labelField = 'title', 
        $additional = []
    ) {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }
    /**
     * Add field filter to collection
     *
     * @param string|array $field
     * @param null|string|array $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition, false);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add store filter to collection
     * @param array|int|\Magento\Store\Model\Store  $store
     * @param boolean $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {   
        if (!$this->getFlag('store_filter_added')) {
            if ($store instanceof \Magento\Store\Model\Store) {
                $store = [$store->getId()];
            }

            if (!is_array($store)) {
                $store = [$store];
            }

            if ($withAdmin) {
                $store[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            }
            $this->addFilter('store', ['in' => $store], 'public');
        }
        return $this;
    }
    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable('solwin_productvideo_video_store')],
                'main_table.video_id = store_table.video_id',
                []
            )->group(
                'main_table.video_id'
            );
        }
        parent::_renderFiltersBefore();
    }
    
}