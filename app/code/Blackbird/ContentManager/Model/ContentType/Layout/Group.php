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
namespace Blackbird\ContentManager\Model\ContentType\Layout;

use Blackbird\ContentManager\Model\ContentType\Layout\Group as LayoutGroup;
use Blackbird\ContentManager\Model\ContentType\Layout\Block as LayoutBlock;
use Blackbird\ContentManager\Model\ContentType\Layout\Field as LayoutField;
use Blackbird\ContentManager\Model\ResourceModel\ContentType\Layout\Group as ResourceGroup;

class Group extends \Blackbird\ContentManager\Model\ContentType\Layout\AbstractModel
    implements \Blackbird\ContentManager\Api\Data\ContentType\Layout\GroupInterface
{
    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\ContentType\Layout\Block\CollectionFactory
     */
    protected $_layoutBlockCollectionFactory;
    
    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\ContentType\Layout\Field\CollectionFactory
     */
    protected $_layoutFieldCollectionFactory;
    
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Blackbird\ContentManager\Model\ResourceModel\ContentType\Layout\Block\CollectionFactory $layoutBlockCollectionFactory
     * @param \Blackbird\ContentManager\Model\ResourceModel\ContentType\Layout\Field\CollectionFactory $layoutFieldCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Blackbird\ContentManager\Model\ResourceModel\ContentType\Layout\Block\CollectionFactory $layoutBlockCollectionFactory,
        \Blackbird\ContentManager\Model\ResourceModel\ContentType\Layout\Field\CollectionFactory $layoutFieldCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_layoutBlockCollectionFactory = $layoutBlockCollectionFactory;
        $this->_layoutFieldCollectionFactory = $layoutFieldCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
    
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceGroup::class);
        $this->setIdFieldName(self::ID);
        $this->setType('group');
    }
    
    /**
     * Object after load processing. Implemented as public interface for supporting objects after load in collections
     *
     * @return $this
     */
    public function afterLoad()
    {
        parent::_afterLoad();
        
        $this->loadChildren();
        
        return $this;
    }
    
    /**
     * Set the children
     * 
     * @param array $children
     * @return $this
     */
    public function setChildren(array $children)
    {
        $this->setData('children', $children);
        
        return $this;
    }
    
    /**
     * Return all children
     * 
     * @return array
     */
    public function getChildren()
    {
        $children = [];

        if ($this->hasData('children') && is_array($this->getData('children'))) {
            $children = $this->getData('children');
        }

        return $children;
    }
    
    /**
     * Load and set the children of a group
     * 
     * @return $this
     */
    public function loadChildren()
    {
        $children = [];
        $collection = null;
        
        // Load block children collection
        $collection = $this->_layoutBlockCollectionFactory->create()
                ->addFieldToFilter(LayoutBlock::PARENT_ID, $this->getId())
                ->setOrder(LayoutBlock::SORT_ORDER);
        foreach ($collection as $block) {
            $children[$block->getSortOrder()] = $block;
        }
        
        // Load field children collection
        $collection = $this->_layoutFieldCollectionFactory->create()
                ->addFieldToFilter(LayoutField::PARENT_ID, $this->getId())
                ->setOrder(LayoutField::SORT_ORDER);
        foreach ($collection as $field) {
            $children[$field->getSortOrder()] = $field;
        }
        
        // Load group children collection
        $collection = $this->getCollection()
                ->addFieldToFilter(LayoutGroup::PARENT_ID, $this->getId())
                ->setOrder(LayoutGroup::SORT_ORDER);
        foreach ($collection as $group) {
            $children[$group->getSortOrder()] = $group;
        }
        
        ksort($children);
        $this->setChildren($children);
        
        return $this;
    }
    
    /**
     * Processing object before delete data
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeDelete()
    {
        parent::beforeDelete();
        
        // Delete the children
        $this->deleteChildren();
        
        return $this;
    }
    
    /**
     * Delete all children
     * 
     * @return $this
     */
    protected function deleteChildren()
    {
        foreach ($this->getChildren() as $child) {
            $child->delete();
        }
        
        return $this;
    }

}
