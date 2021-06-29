<?php
/**
 * Blackbird ContentManager Module
 *
 * NOTICE OF LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 * @category        Blackbird
 * @package         Blackbird_ContentManager
 * @copyright       Copyright (c) 2017 Blackbird (https://black.bird.eu)
 * @author          Blackbird Team
 * @license         https://www.advancedcontentmanager.com/license/
 */
namespace Blackbird\ContentManager\Model\ResourceModel\Content;

use Magento\Store\Model\Store;
use Blackbird\ContentManager\Model\Content;
use Blackbird\ContentManager\Model\ContentType;

/**
 * Content Resource Model Collection
 */
class Collection extends \Blackbird\ContentManager\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Blackbird\ContentManager\Model\Content::class, \Blackbird\ContentManager\Model\ResourceModel\Content::class);
    }
    
    /**
     * Get SQL for get record count
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $select->resetJoinLeft();

        return $select;
    }
    
    /**
     * Add store availability filter. Include availability content for store website
     *
     * @param null|string|bool|int|Store $store
     * @return $this
     */
    public function addStoreFilter($store = null)
    {
        if ($store === null) {
            $store = $this->getStoreId();
        }
        $store = $this->_storeManager->getStore($store);

        if ($store->getId() != Store::DEFAULT_STORE_ID) {
            $this->setStoreId($store);
        }

        return $this;
    }

    /**
     * Add a content type identifier to the filter
     *
     * @param string|int|array|ContentType $contentType identifier, id or ContentType object
     * @return $this
     */
    public function addContentTypeFilter($contentType)
    {
        $contentTypes = is_array($contentType) ? $contentType : [$contentType];
        $isIdentifier = false;
        $ctFilters = [];

        foreach ($contentTypes as $contentType) {
            if ($contentType instanceof ContentType) {
                $filter = (int)$contentType->getId();
            } elseif (is_numeric($contentType)) {
                $filter = (int)$contentType;
            } else {
                $filter = (string)$contentType;
                $isIdentifier = true;
            }

            $ctFilters[] = $filter;
        }

        if ($isIdentifier) {
            $this->getSelect()->joinLeft(
                ['contenttype' => $this->getTable('blackbird_contenttype')],
                'contenttype.ct_id = e.ct_id', ''
            )->where('contenttype.identifier IN (?)', $ctFilters);
        } else {
            $this->addAttributeToFilter(Content::CT_ID, $ctFilters);
        }
        
        return $this;
    }
        
    /**
     * Reset left join
     *
     * @param int $limit
     * @param int $offset
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = parent::_getAllIdsSelect($limit, $offset);
        $idsSelect->resetJoinLeft();
        return $idsSelect;
    }
}
