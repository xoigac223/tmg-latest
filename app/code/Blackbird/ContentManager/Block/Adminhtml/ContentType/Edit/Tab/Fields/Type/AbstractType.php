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
namespace Blackbird\ContentManager\Block\Adminhtml\ContentType\Edit\Tab\Fields\Type;

abstract class AbstractType extends \Magento\Backend\Block\Widget
{
    /**
     * @var string 
     */
    protected $_name = 'abstract';
    
    /**
     * @return string
     */
    public function getFieldsetName()
    {
        return 'contenttype[fieldsets]';
    }
    
    /**
     * @return string
     */
    public function getFieldsetId()
    {
        return 'contenttype_fieldset';
    }
    
    /**
     * @return string
     */
    public function getFieldName()
    {
        return '[fields]';
    }
    
    /**
     * @return string
     */
    public function getFieldId()
    {
        return 'field';
    }
    
    /**
     * @return string
     */
    public function getTypeName()
    {
        return '[types]';
    }
    
    /**
     * @return string
     */
    public function getTypeId()
    {
        return 'type';
    }
}
