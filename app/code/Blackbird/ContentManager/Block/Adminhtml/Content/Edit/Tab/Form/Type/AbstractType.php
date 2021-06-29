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
namespace Blackbird\ContentManager\Block\Adminhtml\Content\Edit\Tab\Form\Type;

use Blackbird\ContentManager\Model\ContentType\CustomField;

abstract class AbstractType extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element 
implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var CustomField
     */
    protected $_customField;
    
    /**
     * @var array
     */
    protected $_contentField;
    
    /**
     * 
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_customField = $this->getData('custom_field');
        $this->_contentField = $this->getData('content_field');
    }

    /**
     * Retrive the type of the special custom field
     * 
     * @return string
     */
    public function getFieldType()
    {
        return $this->_customField->getType();
    }
    
    /**
     * Retrieve the custom field
     * 
     * @return CustomField
     */
    public function getCustomField()
    {
        return $this->_customField;
    }
    
    /**
     * Retrieve the content field data
     * 
     * @return array
     */
    public function getContentField()
    {
        return $this->_contentField;
    }
    
    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'open_chooser',
            'Magento\Backend\Block\Widget\Button',
            [
                'id' => 'open_chooser',
                'label' => __('Open Chooser'),
                'title' => __('Open Chooser'),
                'class' => 'button-open-chooser'
            ]
        );
        
        $this->addChild(
            'apply',
            'Magento\Backend\Block\Widget\Button',
            [
                'id' => 'apply',
                'label' => __('Apply'),
                'title' => __('Apply'),
                'class' => 'button-apply'
            ]
        );
        
        return parent::_prepareLayout();
    }
    
    /**
     * @return string
     */
    public function getOpenChooserButtonHtml()
    {
        return $this->getChildHtml('open_chooser');
    }
    
    /**
     * @return string
     */
    public function getApplyButtonHtml()
    {
        return $this->getChildHtml('apply');
    }
    
    /**
     * @return string
     */
    public function getRelationClass()
    {
        return 'field-type-relation';
    }
    
    /**
     * @return string
     */
    public function getFileClass()
    {
        return 'field-type-file';
    }
}
