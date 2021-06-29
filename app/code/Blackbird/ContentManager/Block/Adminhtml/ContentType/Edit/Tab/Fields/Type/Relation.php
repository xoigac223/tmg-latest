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

class Relation extends \Blackbird\ContentManager\Block\Adminhtml\ContentType\Edit\Tab\Fields\Type\AbstractType
{
    /**
     * @var string
     */
    protected $_template = 'Blackbird_ContentManager::contenttype/edit/tab/fields/type/relation.phtml';
    
    /**
     * @var \Blackbird\ContentManager\Model\Config\Source\Product\Attributes
     */
    protected $_attributesSource;
    
    /**
     * @var \Blackbird\ContentManager\Model\Config\Source\ContentTypes
     */
    protected $_contenttypesSource;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Blackbird\ContentManager\Model\Config\Source\Product\Attributes $attributesSource
     * @param \Blackbird\ContentManager\Model\Config\Source\ContentTypes $contenttypesSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Blackbird\ContentManager\Model\Config\Source\Product\Attributes $attributesSource,
        \Blackbird\ContentManager\Model\Config\Source\ContentTypes $contenttypesSource,
        array $data = []
    ) {
        $this->_attributesSource = $attributesSource;
        $this->_contenttypesSource = $contenttypesSource;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getAttributesSelectHtml()
    {
        $select = $this->getLayout()->createBlock('Magento\Framework\View\Element\Html\Select')
            ->setData([
                'id' => $this->getFieldsetId() . '_<%- data.id %>_' . $this->getFieldId() . '_<%- data.field_id %>_attribute',
                'class' => 'select',
                'title' => 'Attribute',
            ])
            ->setName($this->getFieldsetName() . '[<%- data.id %>]' . $this->getFieldName() . '[<%- data.field_id %>][attribute]')
            ->setOptions($this->_attributesSource->toOptionArray());

        return $select->getHtml();
    }
    
    /**
     * @return string
     */
    public function getContentTypesSelectHtml()
    {
        $select = $this->getLayout()->createBlock('Magento\Framework\View\Element\Html\Select')
            ->setData([
                'id' => $this->getFieldsetId() . '_<%- data.id %>_' . $this->getFieldId() . '_<%- data.field_id %>_content_type',
                'class' => 'select',
                'title' => 'Content Type',
            ])
            ->setName($this->getFieldsetName() . '[<%- data.id %>]' . $this->getFieldName() . '[<%- data.field_id %>][content_type]')
            ->setOptions($this->_contenttypesSource->toOptionArray(true));

        return $select->getHtml();
    }
}
