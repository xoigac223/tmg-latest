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
namespace Blackbird\ContentManager\Model\ResourceModel;

use Magento\Framework\Validator\Exception as ValidatorException;
use Blackbird\ContentManager\Model\Content as ContentModel;

/**
 * Content Resource Model
 */
class Content extends AbstractResource
{
    /**
     * @var \Magento\Framework\Validator\Factory
     */
    protected $_validatorFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;
    
    /**
     * Available stores
     * 
     * @var array
     */
    protected $_availableStores = [];
    
    /**
     * @var string
     */
    protected $_entityStoreTable = '';

    /**
     * @var string
     */
    protected $_entityMainTable = '';

    /**
     * @var array
     */
    protected $_eavValueTables = [];
    
    /**
     * @param \Magento\Eav\Model\Entity\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Blackbird\ContentManager\Model\Factory $modelFactory
     * @param \Magento\Framework\Validator\Factory $validatorFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param array $data
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Blackbird\ContentManager\Model\Factory $modelFactory,
        \Magento\Framework\Validator\Factory $validatorFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $data = []
    ) {
        parent::__construct($context, $storeManager, $modelFactory, $data);
        $this->_validatorFactory = $validatorFactory;
        $this->_dateTime = $dateTime;
    }
    
    /**
     * Ressource initialization
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setType(ContentModel::ENTITY);
    }
    
    /**
     * Retrieve content entity default attributes
     *
     * @todo to fix
     * @return string[]
     */
    protected function _getDefaultAttributes()
    {
        return [
            'entity_id',
            //'entity_type_id',
            'ct_id',
            'created_at',
            'updated_at'
        ];
    }
    
    /**
     * Check content before saving
     *
     * @param \Magento\Framework\DataObject $content
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _beforeSave(\Magento\Framework\DataObject $content)
    {
        /** @var \Blackbird\ContentManager\Model\Content $content */
        if ($content->getStoreId() === null) {
            $content->setStore($this->_storeManager->getStore());
        }
        if (!$content->getCtId()) {
            throw new ValidatorException(__('Please link to a content type ID (attribute ct_id is required).'));
        }

        return parent::_beforeSave($content);
    }
    
    /**
     * Save relation entity_id/store_id
     * 
     * @param \Magento\Framework\DataObject $content
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\DataObject $content)
    {
        $savedStores = $this->lookupStoreIds($content->getId());
        $updateStore = [$content->getStoreId()];
        
        $data = array_diff($updateStore, $savedStores);
        
        if (isset($data[0])) {
            $updateStore = [
                'entity_id' => $content->getId(),
                'store_id' => $content->getStoreId(),
            ];
            
            $this->getConnection()->insert($this->getEntityStoreTable(), $updateStore);
        }
        
        return parent::_afterSave($content);
    }

    /**
     * Process content data before deleting
     * 
     * @param \Magento\Framework\DataObject $content
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\DataObject $content)
    {
        $where = ['entity_id = (?)' => (int) $content->getId()];
        
        $this->getConnection()->delete($this->getEntityStoreTable(), $where);
        
        return parent::_beforeDelete($content);
    }
    
    /**
     * Get store ids to which specified item is assigned
     * 
     * @param int $contentId
     * @return array
     */
    public function lookupStoreIds($contentId)
    {
        $select = $this->getConnection()->select()
            ->from($this->getEntityStoreTable(), 'store_id')
            ->where('entity_id = ?', (int)$contentId);
        
        return $this->getConnection()->fetchCol($select);
    }
    
    /**
     * Get entity store table name
     * 
     * @return string
     */
    public function getEntityStoreTable()
    {
        if (empty($this->_entityStoreTable)) {
            $this->_entityStoreTable = $this->getTable('blackbird_contenttype_entity_store');
        }
        
        return $this->_entityStoreTable;
    }
    
    /**
     * Get main table name
     *
     * @return string
     */
    public function getMainTable()
    {
        if (empty($this->_entityMainTable)) {
            $this->_entityMainTable = $this->getTable('blackbird_contenttype_entity');
        }
        
        return $this->_entityMainTable;
    }
    
    /**
     * Delete an attribute of an entity by store id
     * 
     * @param int $entityId
     * @param int $storeId
     * @param array $attributeIds
     */
    public function deleteAttributesByStore($entityId, $storeId, array $attributeIds)
    {
        $where = [
            'entity_id = ?' => (int) $entityId,
            'store_id = ?' => (int) $storeId
        ];
        
        // Delete current store link
        $this->getConnection()->delete($this->getEntityStoreTable(), $where);
        
        // Delete attributes linked to the current store id
        $where['attribute_id IN (?)'] = $attributeIds;
        
        foreach ($this->getEavValueTables() as $table) {
            $this->getConnection()->delete($table, $where);
        }
    }
    
    /**
     * Retrieves all available stores for a content
     * 
     * @param int $contentId
     * @return array
     */
    public function getAvailableStores($contentId)
    {
        if (!isset($this->_availableStores[$contentId])) {
            $this->_availableStores[$contentId] = [];
            $storeIds = $this->lookupStoreIds($contentId);
            
            if (count($storeIds) > 0) {
                foreach ($storeIds as $storeId) {
                    $store = $this->_storeManager->getStore($storeId);
                    $this->_availableStores[$contentId][$store->getId()] = $store;
                }
            }
        }
        
        return $this->_availableStores[$contentId];
    }
    
    /**
     * Check if a store exists for a given content
     * 
     * @param int $contentId
     * @param int $storeId
     * @return boolean
     */
    public function existsForStore($contentId, $storeId)
    {
        $availableStores = $this->getAvailableStores($contentId);
        
        return (isset($availableStores[$storeId]));
    }
    
    /**
     * @return array
     */
    protected function getEavValueTables()
    {
        if (empty($this->_eavValueTables)) {
            $tables = [];
            
            foreach ($this->getBasicEavTypes() as $type) {
                $tables[] = $this->getTable('blackbird_contenttype_entity_' . $type);
            }
            
            $this->_eavValueTables = $tables;
        }
        
        return $this->_eavValueTables;
    }
    
    /**
     * @return array
     */
    protected function getBasicEavTypes()
    {
        return [
            'datetime',
            'decimal',
            'int',
            'text',
            'varchar'
        ];
    }
    
}
