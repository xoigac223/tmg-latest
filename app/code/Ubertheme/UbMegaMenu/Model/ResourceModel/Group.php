<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 *
 */

namespace Ubertheme\UbMegaMenu\Model\ResourceModel;

/**
 * UB Mega Menu Group mysql resource
 */
class Group extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Store model
     *
     * @var null|\Magento\Store\Model\Store
     */
    protected $_store = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
        $this->dateTime = $dateTime;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ubmegamenu_group', 'group_id');
    }

    /**
     * Process group data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        //we will delete all menu items of this menu group first
        $itemIds = $this->lookupItemIds((int)$object->getId());
        if ($itemIds AND is_array($itemIds)){
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $model = $om->create('Ubertheme\UbMegaMenu\Model\Item');
            foreach ($itemIds as $id) {
                $model->load($id);
                $model->delete();
            }
        }
        /*$condition = ['group_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getMainTable(), $condition);*/

        return parent::_beforeDelete($object);
    }

    /**
     * Process group data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$this->isValidIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The menu key contains capital letters or disallowed symbols.')
            );
        }

        if ($this->isNumericIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The menu key cannot be made of only numbers.')
            );
        }
        
        //check exists identifier
        if ($this->isExistsIdentifier($object)){
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The menu key was used by selected store views.')
            );
        }

        return parent::_beforeSave($object);
    }

    /**
     * Assign group to store views
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();

        $table = $this->getTable('ubmegamenu_group_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = ['group_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = ['group_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);
    }

    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'identifier';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Perform operations after object load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $storeIds = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $storeIds);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Ubertheme\UbMegaMenu\Model\Slide $object
     * @return \Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $storeIds = [\Magento\Store\Model\Store::DEFAULT_STORE_ID, (int)$object->getStoreId()];
            $select->join(
                ['gs' => $this->getTable('ubmegamenu_group_store')],
                $this->getMainTable() . '.group_id = gs.group_id',
                []
            )->where(
                'is_active = ?',
                1
            )->where(
                'gs.store_id IN (?)',
                $storeIds
            )->order(
                'gs.store_id DESC'
            )->limit(
                1
            );
        }

        return $select;
    }

    /**
     * Retrieve load select with filter by identifier, store and activity
     *
     * @param string $identifier
     * @param int|array $store
     * @param int $isActive
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadByIdentifierSelect($identifier, $store, $isActive = null)
    {
        $select = $this->getConnection()->select()->from(
            ['mg' => $this->getMainTable()]
        )->join(
            ['gs' => $this->getTable('ubmegamenu_group_store')],
            'mg.group_id = gs.group_id',
            []
        )->where(
            'mg.identifier = ?',
            $identifier
        )->where(
            'gs.store_id IN (?)',
            $store
        );

        if (!is_null($isActive)) {
            $select->where('sl.is_active = ?', $isActive);
        }

        return $select;
    }

    /**
     *  Check whether group identifier is numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isNumericIdentifier(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     *  Check whether group identifier is valid
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isValidIdentifier(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
    }
    
    public function isExistsIdentifier(\Magento\Framework\Model\AbstractModel $object)
    {
        $storeIds = (array)$object->getStores();
        array_push($storeIds, \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        
        $select = $this->getConnection()->select()->from(
            ['mg' => $this->getMainTable()]
        )->join(
            ['gs' => $this->getTable('ubmegamenu_group_store')],
            'mg.group_id = gs.group_id',
            []
        )->where(
            'mg.identifier = ?',
            $object->getData('identifier')
        )->where(
            'gs.store_id IN (?)',
            $storeIds
        );

        //if is edit
        if ($object->getData('group_id')) {
            $select->where('mg.group_id != ?', $object->getData('group_id'));
        }
        
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('mg.group_id')->order('gs.store_id DESC')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Check if menu group identifier exist for specific store
     * return group id if menu group exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID, $storeId];
        $select = $this->_getLoadByIdentifierSelect($identifier, $stores, 1);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('mg.group_id')->order('gs.store_id DESC')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Retrieves group title from DB by passed identifier.
     *
     * @param string $identifier
     * @return string|false
     */
    public function getGroupTitleByIdentifier($identifier)
    {
        $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
        if ($this->_store) {
            $stores[] = (int)$this->getStore()->getId();
        }

        $select = $this->_getLoadByIdentifierSelect($identifier, $stores);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('mg.title')->order('gs.store_id DESC')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }
    
    /**
     * Retrieves group id from DB by passed identifier.
     *
     * @param string $identifier
     * @return string|false
     */
    public function getGroupIdByIdentifier($identifier)
    {
        $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
        $stores[] = (int)$this->getStore()->getId();
        if ($this->_store) {
            $stores[] = (int)$this->getStore()->getId();
        }

        $select = $this->_getLoadByIdentifierSelect($identifier, $stores);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('mg.group_id')->order('gs.store_id DESC')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Retrieves group title from DB by passed id.
     *
     * @param string $id
     * @return string|false
     */
    public function getGroupTitleById($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(), 'title')->where('group_id = :group_id');

        $binds = ['group_id' => (int)$id];

        return $connection->fetchOne($select, $binds);
    }

    /**
     * Retrieves group identifier from DB by passed id.
     *
     * @param string $id
     * @return string|false
     */
    public function getGroupIdentifierById($id)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable(), 'identifier')->where('group_id = :group_id');

        $binds = ['group_id' => (int)$id];

        return $connection->fetchOne($select, $binds);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $groupId
     * @return array
     */
    public function lookupStoreIds($groupId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('ubmegamenu_group_store'),
            'store_id'
        )->where(
            'group_id = :group_id'
        );

        $binds = [':group_id' => (int)$groupId];

        return $connection->fetchCol($select, $binds);
    }
    
    /**
     * Get menu item ids to which specified item is assigned
     *
     * @param int $groupId
     * @return array
     */
    public function lookupItemIds($groupId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('ubmegamenu_item'),
            'item_id'
        )->where(
            'group_id = ?',
            (int)$groupId
        );

        return $connection->fetchCol($select);
    }

    /**
     * Set store model
     *
     * @param \Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->_store);
    }

    /**
     * Retrieves available options menu group.
     * @return array
     */
    public function getOptions()
    {
        $connection = $this->getConnection();
        
        $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
        if ($this->_store) {
            $stores[] = (int)$this->getStore()->getId();
        }
        
        $select = $connection->select()->from(
            ['mg' => $this->getMainTable()],
            ['group_id', 'title']
        )->join(
            ['gs' => $this->getTable('ubmegamenu_group_store')],
            'mg.group_id = gs.group_id',
            []
        )->where(
            'gs.store_id IN (?)',
            $stores
        );
        
        return $connection->fetchAll($select);
    }
    
    public function getMenuItems($groupId){
        
        $connection = $this->getConnection();
        
        $select = $connection->select()->from(
            ['mi' => $this->getTable('ubmegamenu_item')]
        )->where(
            'mi.group_id = ?',
            (int)$groupId
        )->where(
            'mi.is_active = ?',
            \Ubertheme\UbMegaMenu\Model\Item::STATUS_ENABLED
        )->order('mi.sort_order ASC');
        
        return $connection->fetchAll($select);
    }

    public function setPkAutoIncrement($value = true){
        $this->_isPkAutoIncrement = $value;
    }
}
