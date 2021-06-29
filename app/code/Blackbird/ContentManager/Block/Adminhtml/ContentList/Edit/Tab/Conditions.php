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
namespace Blackbird\ContentManager\Block\Adminhtml\ContentList\Edit\Tab;

use Blackbird\ContentManager\Api\Data\ContentListInterface as ContentListData;

class Conditions extends \Magento\Backend\Block\Widget\Form\Generic implements 
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Blackbird\ContentManager\Block\Adminhtml\Content\Widget\Conditions
     */
    protected $_conditions;
    
    /**
     * @var \Blackbird\ContentManager\Model\Rule
     */
    protected $_rule;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Blackbird\ContentManager\Block\Adminhtml\Content\Widget\Conditions $conditions
     * @param \Blackbird\ContentManager\Model\Rule $rule
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Blackbird\ContentManager\Block\Adminhtml\Content\Widget\Conditions $conditions,
        \Blackbird\ContentManager\Model\Rule $rule,
        array $data = []
    ) {
        $this->_conditions = $conditions;
        $this->_rule = $rule;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    
    public function getTabLabel()
    {
        return __('Conditions');
    }
    
    public function getTabTitle()
    {
        return __('Conditions');
    }
    
    public function canShowTab()
    {
        return true;
    }
    
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('contentlist_');
        
        // Load content list
        $contentList = $this->_coreRegistry->registry('current_contentlist');
        $data = [];
        if ($contentList) {
            $contentList->rule->setConditionsSerialized($contentList->getData(ContentListData::CONDITIONS));
            $data = $contentList->getData();
        }
        
        /** Conditions */
        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            ['legend' => __('Content Filter (leave blank to get all contents of the content type)')]
        );
        
        $fieldset->addField(
            ContentListData::CONDITIONS,
            'text',
            [
                'name' => ContentListData::CONDITIONS,
                'required' => true
            ]
        )->setRule(
            $this->_rule
        )->setRenderer(
            $this->_conditions
        );
        
        $this->_eventManager->dispatch('adminhtml_block_contentmanager_contentlist_config_prepareform', ['form' => $form]);
        

        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
