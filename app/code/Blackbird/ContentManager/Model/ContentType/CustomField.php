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

use Blackbird\ContentManager\Model\ContentType;
use Blackbird\ContentManager\Model\ContentType\CustomField\Option;
use Blackbird\ContentManager\Model\ResourceModel\Eav\Attribute;
use Blackbird\ContentManager\Model\ResourceModel\ContentType\CustomField as ResourceCustomField;

/**
 * Custom Field Model
 * @method int getId() Get Id of Custom Fields
 */
class CustomField extends \Blackbird\ContentManager\Model\AbstractModel 
    implements \Blackbird\ContentManager\Api\Data\ContentType\CustomFieldInterface
{
    /**
     * @var ContentType
     */
    protected $contenttype;
    
    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\ContentType\CustomField\Option\CollectionFactory
     */
    protected $_optionCollectionFactory;
    
    /**
     * @var \Blackbird\ContentManager\Model\Factory
     */
    protected $_modelFactory;
    
    /**
     * @var \Blackbird\ContentManager\Model\Config\Source\ContentType\CustomFields\Type 
     */
    protected $_sourceFieldTypes;
    
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    protected $_attributeCollectionFactory;
    
    /**
     * @var \Blackbird\ContentManager\Model\Attribute
     */
    protected $_attribute = null;
    
    /**
     * @var \Magento\Eav\Model\Entity\Type 
     */
    protected $_eavEntityType = null;
    
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Blackbird\ContentManager\Model\Factory $modelFactory
     * @param \Blackbird\ContentManager\Model\Config\Source\ContentType\CustomFields\Type $sourceFieldTypes
     * @param \Blackbird\ContentManager\Model\ResourceModel\ContentType\CustomField\Option\CollectionFactory $optionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Blackbird\ContentManager\Model\Attribute $attribute
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Blackbird\ContentManager\Model\Factory $modelFactory,
        \Blackbird\ContentManager\Model\Config\Source\ContentType\CustomFields\Type $sourceFieldTypes,
        \Blackbird\ContentManager\Model\ResourceModel\ContentType\CustomField\Option\CollectionFactory $optionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $attributeCollectionFactory,
        \Blackbird\ContentManager\Model\Attribute $attribute,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_modelFactory = $modelFactory;
        $this->_sourceFieldTypes = $sourceFieldTypes;
        $this->_optionCollectionFactory = $optionCollectionFactory;
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
        $this->_attribute = $attribute;
    }
    
    /**
     * Construct
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceCustomField::class);
        $this->setIdFieldName(self::ID);
    }
    
    /**
     * @return string
     */
    public function getFieldsetId()
    {
        return $this->_getData(self::FIELDSET_ID);
    }
    
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_getData(self::TITLE);
    }
    
    /**
     * @return string
     */    
    public function getIdentifier()
    {
        return $this->_getData(self::IDENTIFIER);
    }
    
    /**
     * @return string
     */
    public function getSortOrder()
    {
        return $this->_getData(self::SORT_ORDER);
    }
    
    /**
     * @return string
     */
    public function getType()
    {
        return $this->_getData(self::TYPE);
    }
    
    /**
     * @return string
     */
    public function getNote()
    {
        return $this->_getData(self::NOTE);
    }
    
    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_getData(self::OPTIONS);
    }
    
    /**
     * Retrieve contenttype instance
     *
     * @todo check usage
     * @return ContentType
     */
    public function getContentType()
    {
        return $this->contenttype;
    }

    /**
     * Set contenttype instance
     *
     * @todo check usage
     * @param ContentType $contenttype
     * @return $this
     */
    public function setContentType(ContentType $contenttype)
    {
        $this->contenttype = $contenttype;
        return $this;
    }
    
    /**
     * Returns all option of the custom field
     * 
     * @return \Blackbird\ContentManager\Model\ResourceModel\ContentType\CustomField\Option\Collection
     */
    public function getOptionCollection()
    {
        $collection = $this->_optionCollectionFactory->create()
            ->addFieldToFilter(Option::FIELD_ID, $this->getId())
            ->addTitleToResult()
            ->setOrder(Option::SORT_ORDER, 'asc');

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
        
        // Delete options
        $this->deleteOptions();
        
        // Delete attributes
        $this->deleteEavAttribute();
        
        return $this;
    }
    
    /**
     * Processing object before save data
     *
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();
        
        // Save EAV Attribute
        $this->saveEavAttribute();
    }
    
    /**
     * Processing object after save data
     *
     * @return $this
     */
    public function afterSave()
    {
        parent::afterSave();
        
        // Save options
        $this->saveOptions();
        
        return $this;
    }
    
    /**
     * Delete the eav attribute of the custom field
     */
    protected function deleteEavAttribute()
    {
        $attribute = $this->getEavAttribute();
        
        if ($attribute) {
            // Delete image attributes
            if ($this->getType() === 'image') {
                $this->deleteImageFieldAttributes();
            }
            
            $attribute->delete();
        }
    }
    
    /**
     * Delete the image attributes
     */
    protected function deleteImageFieldAttributes()
    {
        $attributes = $this->_attributeCollectionFactory->create()
            ->addFieldToFilter('attribute_code', ['in' => [
                $this->getIdentifier() . '_orig',                               // get the original filename
                $this->getIdentifier() . '_alt',
                $this->getIdentifier() . '_url',
                $this->getIdentifier() . '_titl'
            ]])
            ->addFieldToFilter('entity_type_id', $this->getEavEntityType()->getEntityTypeId());

        foreach ($attributes as $attribute) {
            $attribute->delete();
        }
    }
    
    /**
     * Create or update the eav attribute
     */
    protected function saveEavAttribute()
    {
        if (!empty($this->getId()) && !empty($this->getAttributeId())) {
            $this->updateEavAttribute($this->getIsSearchable(), $this->getSearchWeight());
        } else {
            $attribute = $this->createEavAttribute(
                $this->getTitle(),
                $this->getIdentifier(),
                $this->getType(),
                $this->getIsSearchable(),
                $this->getSearchWeight()
            );
            
            $this->setAttributeId($attribute->getAttributeId());
        }
    }
    
    /**
     * Retrieve the entity type of Advanced Content Manager
     * 
     * @return \Magento\Eav\Model\Entity\Type
     */
    public function getEavEntityType()
    {
        if ($this->_eavEntityType === null) {
            $this->_eavEntityType = $this->_modelFactory->get('Magento\Eav\Model\Entity\Type')
            ->load(\Blackbird\ContentManager\Model\Content::ENTITY, 'entity_type_code');
        }
        
        return $this->_eavEntityType;
    }
    
    /**
     * Retrieve the linked attribute
     * 
     * @return \Blackbird\ContentManager\Model\Attribute
     */
    public function getEavAttribute()
    {
        if (!empty($this->getAttributeId())) {
            $this->_attribute->load($this->getAttributeId());
        }
        
        return $this->_attribute;
    }
    
    /**
     * Delete options for a select field
     */
    protected function deleteOptions()
    {
        foreach ($this->getOptionCollection() as $option) {
            $option->delete();
        }
    }
    
    /**
     * Save options for a field of type of select
     */
    protected function saveOptions()
    {
        $customFieldId = Option::FIELD_ID;
        
        if (!empty($this->getOptions()) && is_array($this->getOptions())) {
            
            foreach ($this->getOptions() as $option) {
                if (!is_array($option)) {
                    continue;
                }
                
                $optionModel = $this->_modelFactory->create(Option::class);
                $optionModel->setData($option)->setData($customFieldId, $this->getId());
                
                // Create new option
                if ($optionModel->getData(Option::ID) < '1') {
                    $optionModel->unsetData(Option::ID);
                }
                
                // Delete option if is no more or save
                if ($optionModel->getData('is_delete') == '1') {
                    $optionModel->delete();
                } else {
                    $optionModel->save();
                }
            }
        }
        
    }
    
    /**
     * Update an EAV Attribte
     * 
     * @param bool $isSearchable
     * @param int $attributeSearchWeight
     * @return Attribute
     */
    protected function updateEavAttribute($isSearchable, $attributeSearchWeight)
    {
        $attribute = $this->getEavAttribute();
        $attribute->setData('is_searchable', $isSearchable);
        $attribute->setData('search_weight', $attributeSearchWeight);
        $attribute->save();
        
        return $attribute;
    }

    /**
     * Create an EAV Attribute
     *
     * @param string $title
     * @param string $identifier
     * @param string $type
     * @param bool $isSearchable
     * @param null $searchWeight
     * @return \Magento\Framework\Model\AbstractModel
     */
    protected function createEavAttribute($title, $identifier, $type, $isSearchable = false, $searchWeight = null)
    {
        $contentAttribute = $this->_modelFactory->create(Attribute::class);
        $backendModel = null;
        
        // Specials backend models by type
        if (in_array($type, ['date', 'date_time'])) {
            $backendModel = \Blackbird\ContentManager\Model\Entity\Attribute\Backend\Datetime::class;
        }

        // Attribute definition
        $attribute = [
            'entity_type_id'    => $this->getEavEntityType()->getEntityTypeId(),
            'attribute_code'    => $identifier,
            'backend_model'     => $backendModel,
            'backend_type'      => $contentAttribute->getBackendTypeByInput($type),
            'frontend_input'    => 'text',
            'frontend_label'    => $title,
            'is_required'       => false,
            'is_user_defined'   => false,
            'is_global'         => 0,
            'is_searchable'     => $isSearchable,
            'search_weight'     => $searchWeight,
            'is_visible'        => true,
        ];
        
        $contentAttribute->setData($attribute);
        $contentAttribute->save();

        // If it's an image type, we add specific attributes
        if ($type === 'image') {
            $this->addImageAttributes($title, $identifier);
        }
        
        return $contentAttribute;
    }
    
    /**
     * Add attributes for image field type
     * 
     * @param string $title
     * @param string $identifier
     */
    protected function addImageAttributes($title, $identifier)
    {
        $attrs = [
            '_orig' => [
                'label' => __(' - Original Image'),
                'type' => ('image_original'),
            ],
            '_alt' => [
                'label' => __(' - Image ALT'),
                'type' => 'image_alt',
            ],
            '_url' => [
                'label' => __(' - Image URL'),
                'type' => 'img_url',
            ],
            '_titl' => [
                'label' => __(' - Image TITLE'),
                'type' => 'img_titl',
            ],
        ];
        
        foreach ($attrs as $code => $attr) {
            $this->createEavAttribute(
                $title . $attr['label'],
                $identifier . $code,
                $attr['type']
            );
        }
    }
    
}
