<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 *
 */
namespace Ubertheme\UbMegaMenu\Block\Adminhtml\Item\Edit\Tab;

/**
 * Menu item edit form MegaSetting tab
 */
class MegaSetting extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Ubertheme\UbMegaMenu\Model\Item */
        $model = $this->_coreRegistry->registry('ubmegamenu_item');
        
        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Ubertheme_UbMegaMenu::item_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('item_');

        $fieldset = $form->addFieldset('mega_setting_fieldset', ['legend' => __('Mega Settings'), 'class' => 'fieldset-wide']);

        $settings = [
            'theme_advanced_buttons1' => 'magentowidget,bold,italic,|,justifyleft,justifycenter,justifyright,|,'
                . 'fontselect,fontsizeselect,|,forecolor,backcolor,|,link,unlink,image,|,bullist,numlist,|,code',
            'theme_advanced_buttons2' => null,
            'theme_advanced_buttons3' => null,
            'theme_advanced_buttons4' => null,
            'height' => '100px'
        ];
        $wysiwygConfig = $this->_wysiwygConfig->getConfig(['tab_id' => $this->getTabId(), 'settings' => $settings]);

        $fieldset->addField(
            'is_group',
            'select',
            [
                'label' => __('Enable Group Item'),
                'title' => __('Enable Group Item'),
                'name' => 'is_group',
                'required' => true,
                'options' => $model->getIsGroupOptions(),
                'disabled' => $isElementDisabled
            ]
        );

        $subMenuType = $fieldset->addField(
            'mega_sub_content_type',
            'select',
            [
                'label' => __('Submenu Content'),
                'title' => __('Submenu Content Type'),
                'name' => 'mega_sub_content_type',
                'required' => true,
                'options' => $model->getSubMenuContentOptions(),
                'disabled' => $isElementDisabled
            ]
        );

        //custom content
        $customContent = $fieldset->addField(
            'custom_content',
            'editor',
            [
                'name' => 'custom_content',
                'label' => __('Custom Content'),
                'title' => __('Custom Content'),
                'required' => false,
                'disabled' => $isElementDisabled,
                'note' => __("Your Mega Content goes here."),
                'config' => $wysiwygConfig
            ]
        );

        $staticBlocks = $fieldset->addField(
            'static_blocks',
            'multiselect',
            [
                'name' => 'static_blocks[]',
                'label' => __('Static Blocks'),
                'title' => __('Static Blocks'),
                'values' => [],
                'required' => false,
                'disabled' => $isElementDisabled,
                'note' => __("Your Mega Content goes here."),
                'style' => 'width:100%'
            ]
        );

        $fieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description of menu item'),
                'disabled' => $isElementDisabled,
                'note' => __("Your Mega Content goes here."),
                'config' => $wysiwygConfig,
            ]
        );

        $visibleOption = $fieldset->addField(
            'visible_option',
            'select',
            [
                'name' => 'visible_option',
                'label' => __('Display Mega Content Option'),
                'title' => __('Display Mega Content Option'),
                'required' => false,
                'options' => $model->getVisibleOptions(),
                'disabled' => $isElementDisabled,
            ]
        );

        $values = [];
        foreach ($model->getVisibleInOptions() as $key => $value) {
            $values[] = [
                'value' => $key,
                'label' => $value
            ];
        }
        $visibleIn = $fieldset->addField(
            'visible_in',
            'multiselect',
            [
                'name' => 'visible_in',
                'label' => __('Display Mega Content On'),
                'title' => __('Display Mega Content On'),
                'required' => false,
                'values' => $values,
                'note' => __("Hold down the Command (or Ctrl) key and click to select device(s) you wish to show Mega contents (Description, Custom Content, Static Blocks)"),
                'disabled' => $isElementDisabled,
            ]
        );

        $fieldset->addField(
            'mega_cols',
            'text',
            [
                'name' => 'mega_cols',
                'label' => __('Number of Columns'),
                'title' => __('Number of Columns'),
                'required' => false,
                'class' => 'validate-number',
                'note' => __("Default value is 1. Set to 0 or leave blank to use the default value."),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'mega_base_width_type',
            'select',
            [
                'name' => 'mega_base_width_type',
                'label' => __('Set Base Width'),
                'title' => __('Set Base Width'),
                'required' => true,
                'options' => $model->getBaseWidthTypeOptions(),
                'disabled' => $isElementDisabled,
            ]
        );

       /* $fieldset->addField(
            'mega_col_width',
            'text',
            [
                'name' => 'mega_col_width',
                'label' => __('Submenu Column Width'),
                'title' => __('Submenu Column Width'),
                'required' => false,
                'class' => 'validate-number',
                'note' => __("Unless otherwise specified in 'Set Base Width', default column width is in pixels. Default value: 200px. Set to 0 or leave blank to use the default value."),
                'disabled' => $isElementDisabled
            ]
        );*/

        $fieldset->addField(
            'mega_col_x_width',
            'textarea',
            [
                'name' => 'mega_col_x_width',
                'label' => __('Grid Column Submenu'),
                'title' => __('Grid Column Submenu'),
                'note' => __("Leave blank to set equal columns.<br/>You can create submenus with different width columns. An example of fixed width setting(px):<br/>col1=200<br/>col2=250"),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'mega_width',
            'text',
            [
                'name' => 'mega_width',
                'label' => __('Wrapper Width'),
                'title' => __('Wrapper Width'),
                'required' => false,
                'class' => 'validate-number',
                'note' => __("Define the container width of all submenu columns. Set to 0 or leave blank to use auto width."),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'addition_class',
            'text',
            [
                'name' => 'addition_class',
                'label' => __('Extra Class'),
                'title' => __('Extra Class'),
                'note' => __("Adding your own custom CSS class here"),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        // field dependencies
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                $subMenuType->getHtmlId(),
                $subMenuType->getName()
            )->addFieldMap(
                $staticBlocks->getHtmlId(),
                $staticBlocks->getName()
            )->addFieldMap(
                $visibleOption->getHtmlId(),
                $visibleOption->getName()
            )->addFieldMap(
                $visibleIn->getHtmlId(),
                $visibleIn->getName()
            )->addFieldDependence(
                $staticBlocks->getName(),
                $subMenuType->getName(),
                \Ubertheme\UbMegaMenu\Model\Item::SUB_CONTENT_TYPE_STATIC_BLOCK
            )->addFieldDependence(
                $visibleIn->getName(),
                $visibleOption->getName(),
                \Ubertheme\UbMegaMenu\Model\Item::VISIBLE_OPTION_CUSTOM_CONFIG
            )
        );
        //some depend we can't declare here, we will apply it via javascript: see /app/code/Ubertheme/UbMegaMenu/view/adminhtml/templates/item/edit_js.phtml

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Mega Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Mega Settings');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

}
