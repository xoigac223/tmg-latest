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
namespace Blackbird\ContentManager\Block\Adminhtml\Content\Widget;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Class Conditions
 */
class Conditions extends Template implements RendererInterface
{
    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $_conditions;

    /**
     * @var \Blackbird\ContentManager\Model\Rule
     */
    protected $_rule;

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_elementFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var AbstractElement
     */
    protected $_element;

    /**
     * @var \Magento\Framework\Data\Form\Element\Text
     */
    protected $_input;

    /**
     * @var string
     */
    protected $_template = 'Blackbird_ContentManager::content/widget/conditions.phtml';

    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $conditionsHelper;

    /**
     * @var \Magento\Widget\Model\Widget\InstanceFactory
     */
    protected $_widgetFactory;

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Blackbird\ContentManager\Model\Rule $rule
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Widget\Helper\Conditions $conditionsHelper
     * @param \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Blackbird\ContentManager\Model\Rule $rule,
        \Magento\Framework\Registry $registry,
        \Magento\Widget\Helper\Conditions $conditionsHelper,
        \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory,
        array $data = []
    ) {
        $this->_elementFactory = $elementFactory;
        $this->_conditions = $conditions;
        $this->_rule = $rule;
        $this->_coreRegistry = $registry;
        $this->conditionsHelper = $conditionsHelper;
        $this->_widgetFactory = $widgetFactory;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $widget = $this->getRequest()->getParam('widget');
        $instanceId = $this->getRequest()->getParam('instance_id');

        // Widget for wysiwyg
        if ($widget) {
            $widgetParams = json_decode($widget);

            if ($widgetParams && isset($widgetParams->values) && isset($widgetParams->values->conditions_encoded)) {
                $conditions = $this->getConditionsDecoded($widgetParams->values->conditions_encoded);
            }
        // Widget for Blocks
        } elseif ($instanceId) {
            $widgetInstance = $this->_widgetFactory->create()->load($instanceId);
            $widgetParam = $widgetInstance->getWidgetParameters();

            if (!empty($widgetParam['conditions'])) {
                $conditions = $widgetParam['conditions'];
            }
        }

        if (!empty($conditions)) {
            $this->_rule->loadPost(['conditions' => $conditions]);
        }
    }

    /**
     * @return \Magento\Rule\Model\Condition\Combine
     */
    protected function getConditionsDecoded($conditionsEncoded)
    {
        return $this->conditionsHelper->decode($conditionsEncoded);
    }

    /**
     * {@inheritdoc}
     */
    public function render(AbstractElement $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }

    /**
     * @return string
     */
    public function getNewChildUrl()
    {
        return $this->getUrl(
            'contentmanager/content_widget/conditions',
            ['form' => $this->getElement()->getContainer()->getHtmlId()]
        );
    }

    /**
     * @return AbstractElement
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * @return string
     */
    public function getHtmlId()
    {
        return $this->getElement()->getContainer()->getHtmlId();
    }

    /**
     * @return string
     * @todo render conditions by Custom Field type
     */
    public function getInputHtml()
    {
        $this->_input = $this->_elementFactory->create('text');
        $this->_input
                ->setRule($this->_rule)
                ->setValues()
                ->setRenderer($this->_conditions);
        
        return $this->_input->toHtml();
    }
}
