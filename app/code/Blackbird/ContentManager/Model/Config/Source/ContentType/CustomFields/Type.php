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
namespace Blackbird\ContentManager\Model\Config\Source\ContentType\CustomFields;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * ContentType Field Config
     * 
     * @var \Blackbird\ContentManager\Model\ContentType\CustomField\ConfigInterface
     */
    protected $_customFieldConfig;
    
    /**
     * Constructor
     * 
     * @param \Blackbird\ContentManager\Model\ContentType\CustomField\ConfigInterface $config
     */
    public function __construct(\Blackbird\ContentManager\Model\ContentType\CustomField\ConfigInterface $config)
    {
        $this->_customFieldConfig = $config;
    }
    
    /**
     * @return array
     */
    public function getCustomFieldsRenderer()
    {
        $fields = [];
        
        foreach ($this->_customFieldConfig->getAll() as $field) {
            $fields[] = ['name' => $field['name'], 'renderer' => $field['renderer']];
        }
        
        return $fields;
    }
    
    /**
     * @return array
     */
    public function getCustomFieldsTypesRenderer()
    {
        $fields = [];
        
        foreach ($this->_customFieldConfig->getAll() as $field) {
            foreach ($field['types'] as $fieldtype) {
                $fields[$fieldtype['name']] = $fieldtype['renderer'];
            }
        }
        
        return $fields;
    }
    
    /**
     * @return array
     */
    public function getCustomFieldsOptionRenderer()
    {
        $fields = [];
        
        foreach ($this->_customFieldConfig->getAll() as $field) {
            if (!empty($field['option_renderer'])) {
                $fields[] = ['name' => $field['name'], 'renderer' => $field['option_renderer']];
            }
        }
        
        return $fields;
    }
    
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $groups = [['value' => '', 'label' => __('-- Please select --')]];

        foreach ($this->_customFieldConfig->getAll() as $field) {
            $types = [];
            foreach ($field['types'] as $type) {
                if ($type['disabled']) {
                    continue;
                }
                $types[] = ['label' => __($type['label']), 'value' => $type['name']];
            }
            if (count($types)) {
                $groups[] = ['label' => __($field['label']), 'value' => $types, 'optgroup-name' => $field['label']];
            }
        }

        return $groups;
    }
    
    /**
     * @return array
     */
    public function getSelectTypes()
    {
        $types = [
            'currency',
            'locale'
        ];
        
        foreach ($this->_customFieldConfig->getAll() as $field) {
            if ($field['name'] === 'select') {
                foreach ($field['types'] as $type) {
                    $types[] = $type['name'];
                }
            }
        }

        return $types;
    }

    /**
     * Return frontend renderer type corresponding to contenttype type
     * For render in FORM (when creating new content)
     *
     * @param string $fieldType
     * @return string
     */
    public function getRendererTypeByFieldType($fieldType)
    {
        $fieldTypeToDataType = [
            'field' => 'text',
            'area' => 'textarea',
            'editor' => 'editor',
            'password' => 'password',
            'file' => 'file',
            'image' => 'image',
            'drop_down' => 'select',
            'radio' => 'radios',
            'checkbox' => 'checkboxes',
            'multiple' => 'multiselect',
            'date' => 'date',
            'date_time' => 'date',
            'time' => 'time',
            'int' => 'text',
            'country' => 'select',
            'currency' => 'select',
            'locale' => 'select',
            // Special type for category field
            'category' => '\Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Category',
        ];
        
        return isset($fieldTypeToDataType[$fieldType]) ? $fieldTypeToDataType[$fieldType] : 'text';
    }
    
}
