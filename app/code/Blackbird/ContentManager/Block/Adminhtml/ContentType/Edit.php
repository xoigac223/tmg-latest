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
namespace Blackbird\ContentManager\Block\Adminhtml\ContentType;

/**
 * Content type edit form block
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var string
     */
    protected $_template = 'Blackbird_ContentManager::contenttype/edit.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize form
     * Add standard buttons
     * Add "Save and Continue" button
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_contentType';
        $this->_blockGroup = 'Blackbird_ContentManager';

        parent::_construct();

        $this->removeButton('save');
        $this->updateButton('delete', 'label', 'Delete Content Type');
    }

    /**
     * Retrieve currently edited content type object
     *
     * @return \Blackbird\ContentManager\Model\ContentType
     */
    public function getContentType()
    {
        return $this->_coreRegistry->registry('current_contenttype');
    }

    /**
     * @return int
     */
    public function getContentTypeId()
    {
        return $this->getContentType()->getId();
    }

    /**
     * Check whether new content type is being created
     *
     * @return bool
     */
    public function isContentTypeNew()
    {
        $contentType = $this->getContentType();
        return (!$contentType || !$contentType->getId());
    }

    /**
     * Add elements in layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = '';

        $this->getToolbar()->addChild(
            'save-split-button',
            'Magento\Backend\Block\Widget\Button\SplitButton',
            [
                'id' => 'save-split-button',
                'label' => __('Save Content Type'),
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ],
                'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
                'button_class' => 'widget-button-save',
                'options' => $this->_getSaveSplitButtonOptions()
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Get Save Split Button html
     *
     * @return string
     */
    public function getSaveSplitButtonHtml()
    {
        return $this->getChildHtml('save-split-button');
    }

    /**
     * @return string
     */
    public function getSaveAndContinueUrl()
    {
        return $this->getUrl(
            'contentmanager/*/save',
            ['_current' => true, 'back' => 'edit', 'tab' => '{{tab_id}}', 'active_tab' => null]
        );
    }

    /**
     * @return string
     */
    public function getExportUrl()
    {
        return $this->getUrl('contentmanager/*/export', ['_current' => true]);
    }

    /**
     * Get dropdown options for save split button
     *
     * @return array
     */
    protected function _getSaveSplitButtonOptions()
    {
        $options = [];

        $options[] = [
            'id' => 'new-button',
            'label' => __('Save Content Type & New'),
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndNew', 'target' => '#edit_form'],
                ],
            ],
        ];

        /*
         ** @todo export / import feature
         *
        $options[] = [
            'id' => 'export-button',
            'label' => __('Save Content Type & Export'),
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndExport', 'target' => '#edit_form'],
                ],
            ],
        ];*/

        $options[] = [
            'id' => 'close-button',
            'label' => __('Save Content Type & Close'),
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'saveAndClose', 'target' => '#edit_form']],
            ],
        ];

        return $options;
    }
}
