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
namespace Blackbird\ContentManager\Model\ContentType;

use Blackbird\ContentManager\Api\Data\ContentType\CustomFieldInterface;
use Blackbird\ContentManager\Model\ResourceModel\ContentType\CustomFieldset as ResourceCustomFieldset;

/**
 * Custom Fieldset Model
 * @method int getId() Get Id of Custom Fields
 */
class CustomFieldset extends \Blackbird\ContentManager\Model\AbstractModel 
    implements \Blackbird\ContentManager\Api\Data\ContentType\CustomFieldsetInterface
{
    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\ContentType\CustomField\CollectionFactory
     */
    protected $_customFieldCollectionFactory;
    
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Blackbird\ContentManager\Model\ResourceModel\ContentType\CustomField\CollectionFactory $customFieldCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Blackbird\ContentManager\Model\ResourceModel\ContentType\CustomField\CollectionFactory $customFieldCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_customFieldCollectionFactory = $customFieldCollectionFactory;
    }
    
    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceCustomFieldset::class);
        $this->setIdFieldName(self::ID);
    }
    
    /**
     * Returns all custom fields of the custom fieldset
     * 
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCustomFieldCollection()
    {
        $collection = $this->_customFieldCollectionFactory->create()
                        ->addFieldToFilter(CustomFieldInterface::FIELDSET_ID, $this->getId())
                        ->addTitleToResult()
                        ->setOrder(CustomFieldInterface::SORT_ORDER, 'asc')
                        ->setOrder(CustomFieldInterface::FIELDSET_ID, 'asc');
        return $collection;
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
        
        // Delete custom fields
        $this->deleteCustomFields();
        
        return $this;
    }
    
    /**
     * Delete all custom fields of the custom fieldset
     */
    protected function deleteCustomFields()
    {
        $customFields = $this->getCustomFieldCollection();

        foreach ($customFields as $customField) {
            $customField->delete();
        }
    }
    
}
