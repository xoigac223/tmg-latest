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
namespace Blackbird\ContentManager\Model\Config\Source\ContentType;

use Blackbird\ContentManager\Api\Data\ContentType\CustomFieldInterface;

class CustomFields implements \Magento\Framework\Option\ArrayInterface
{   
    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\ContentType\CustomField\CollectionFactory
     */
    protected $_customFieldCollectionFactory;
    
    /**
     * @param \Blackbird\ContentManager\Model\ResourceModel\ContentType\CustomField\CollectionFactory $customFieldCollectionFactory
     */
    public function __construct(
        \Blackbird\ContentManager\Model\ResourceModel\ContentType\CustomField\CollectionFactory $customFieldCollectionFactory
    ) {
        $this->_customFieldCollectionFactory = $customFieldCollectionFactory;
    }
    
    /**
     * @inheritdoc
     */
    public function toOptionArray($withStaticAttribute = true)
    {
        $collection = $this->_customFieldCollectionFactory->create()->addTitleToResult();
        $return = [];

        //todo refactor + improve
        if ($withStaticAttribute) {
            $return[] = [
                'value' => 'title',
                'label' => __('Page Title (All Content Types)'),
            ];
            $return[] = [
                'value' => 'created_at',
                'label' => __('Created At (All Content Types)'),
            ];
            $return[] = [
                'value' => 'updated_at',
                'label' => __('Updated At (All Content Types)'),
            ];
        }

        foreach ($collection as $customField) {
            $return[] = [
                'value' => $customField->getIdentifier(),
                'label' => $customField->getTitle() . ' [' . $customField->getType() . ']'
            ];
        }
        
        return $return;
    }

    /**
     * @param int $contentTypeId
     * @return array
     */
    public function toOptionArrayByContentType($contentTypeId)
    {
        $return = [];
        $collection = $this->_customFieldCollectionFactory->create()
            ->addFieldToFilter(CustomFieldInterface::CT_ID, $contentTypeId)
            ->addTitleToResult()
            ->addOrder(CustomFieldInterface::SORT_ORDER, 'asc')
            ->addOrder(CustomFieldInterface::FIELDSET_ID, 'asc');
        
        foreach ($collection as $customField) {
            $return[] = ['value' => $customField->getId(), 'label' => $customField->getTitle()];
        }
        
        return $return;
    }
    
    /**
     * @param int $contentTypeId
     * @return array
     */
    public function toArray($contentTypeId = null)
    {
        $return = [];
        
        $collection = $this->_customFieldCollectionFactory->create()
            ->addTitleToResult()
            ->setOrder(CustomFieldInterface::SORT_ORDER, 'asc')
            ->setOrder(CustomFieldInterface::FIELDSET_ID, 'asc');
        
        if (is_numeric($contentTypeId)) {
            $collection->addFieldToFilter(CustomFieldInterface::CT_ID, $contentTypeId);
        }
        
        foreach ($collection as $customField) {
            $return[] = [
                'value' => $customField->getId(),
                'label' => $customField->getTitle(),
                'identifier' => $customField->getIdentifier(),
                'type' => $customField->getType()
            ];
        }
        
        return $return;
    }
    
    /**
     * Retrieve all identifiers
     * 
     * @param array|string $identifier
     * @param int $excludeFieldId
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCustomFieldsByIdentifiers($identifier = [], $excludeFieldId = null)
    {
        $collection = $this->_customFieldCollectionFactory->create()
            ->addFieldToFilter('identifier', $identifier);
        
        if (is_numeric($excludeFieldId)) {
            $collection = $collection->addFieldToFilter('option_id', ['neq' => $excludeFieldId]);
        }
        
        return $collection;
    }
    
}
