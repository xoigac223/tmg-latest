<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */

namespace Ubertheme\UbMegaMenu\Model\ResourceModel;

use Ubertheme\UbMegaMenu\Model\Item\Image as ImageModel;

/**
 * UB Mega Menu Item mysql resource
 */
class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * image model
     *
     * @var \Ubertheme\UbMegaMenu\Model\Item\Image
     */
    protected $imageModel;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param ImageModel $imageModel
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        ImageModel $imageModel,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->imageModel = $imageModel;
        $this->dateTime = $dateTime;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ubmegamenu_item', 'item_id');
    }

    /**
     * Process menu item data before deleting
     *
     * @param \Ubertheme\UbMegaMenu\Model\Item $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $id = (int)$object->getId();

        //check has child items of this item
        $sql = "SELECT item_id, icon_image FROM `{$this->getMainTable()}` WHERE path LIKE '{$id}/%'";
        $childItems = $this->getConnection()->fetchAll($sql);
        if (sizeof($childItems)) {
            $childIds = [];
            foreach ($childItems as $item) {
                $childIds[] = $item['item_id'];
                //delete related icon image if it has
                if ($item['icon_image']) {
                    $imagePath = $this->imageModel->getBaseDir().$item['icon_image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
            }

            //delete all child menu items
            if ($childIds) {
                $childIds = implode(',', $childIds);
                $sql = "DELETE FROM `{$this->getMainTable()}` WHERE item_id IN ({$childIds})";
                $this->getConnection()->query($sql);
            }

        }

        //delete related icon image of current menu item if it has
        $image = $object->getIconImage();
        if ($image) {
            $imagePath = $this->imageModel->getBaseDir().$image;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        return parent::_beforeDelete($object);
    }

    /**
     * Process menu item data before saving
     *
     * @param \Ubertheme\UbMegaMenu\Model\Item $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        //validate some data if needed here...
        return parent::_beforeSave($object);
    }

    /**
     * After save a menu item function
     *
     * @param \Ubertheme\UbMegaMenu\Model\Item $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $menuId = (int)$object->getId();

        $parentIds = [];
        $this->getAllParentIds($menuId, $parentIds);
        $parentIds = array_reverse($parentIds);

        //get level
        $level = sizeof($parentIds);
        $level = ($level) ? ++$level : 1;

        //get path
        $path = ($level > 1) ? implode('/', $parentIds)."/{$menuId}" : "{$menuId}";

        //update
        $query = "UPDATE `{$this->getMainTable()}` SET `path` = '{$path}', `level` = {$level} WHERE item_id = {$menuId}";
        $this->getConnection()->query($query);

        return parent::_afterSave($object);
    }

    /**
     * Recursive get parent menu item ids
     *
     * @param $menuItemId
     * @param $parentIds
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllParentIds($menuItemId, &$parentIds)
    {
        /* @var  \Magento\Framework\DB\Adapter\AdapterInterface $connection*/
        $query = "SELECT parent_id FROM `{$this->getMainTable()}` WHERE item_id = {$menuItemId}";
        $parentId = $this->getConnection()->fetchOne($query);
        if ($parentId) {
            $parentIds[] = $parentId;
            $this->getAllParentIds($parentId, $parentIds);
        }
        return true;
    }

    /**
     * Load an object
     *
     * @param \Ubertheme\UbMegaMenu\Model\Item $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        return parent::load($object, $value, $field);
    }

    /**
     * Perform operations after object load
     *
     * @param \Ubertheme\UbMegaMenu\Model\Item $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Ubertheme\UbMegaMenu\Model\Item $object
     * @return \Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        return $select;
    }

    /**
     * Retrieves menu item title from DB by passed id.
     *
     * @param string $id
     * @return string|false
     */
    public function getMenuItemTitleById($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(), 'title')->where('item_id = :item_id');
        $binds = ['item_id' => (int)$id];
        return $connection->fetchOne($select, $binds);
    }
    
    /**
     * Retrieves available menu group.
     * @return array
     */
    public function getMenuGroupOptions()
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getTable('ubmegamenu_group'), ['group_id', 'title']);
        return $connection->fetchAll($select);
    }

    public function setPkAutoIncrement($value = true){
        $this->_isPkAutoIncrement = $value;
    }
}
