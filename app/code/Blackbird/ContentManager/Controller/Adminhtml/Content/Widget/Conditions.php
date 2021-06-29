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
namespace Blackbird\ContentManager\Controller\Adminhtml\Content\Widget;

use Magento\Rule\Model\Condition\AbstractCondition;

/**
 * Class Conditions
 */
class Conditions extends \Magento\Backend\App\Action
{
    /**
     * @var \Blackbird\ContentManager\Model\Rule
     */
    protected $_rule;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Blackbird\ContentManager\Model\Rule $rule
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Blackbird\ContentManager\Model\Rule $rule
    ) {
        $this->_rule = $rule;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $typeData = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $className = $typeData[0];

        $model = $this->_objectManager->create($className)
            ->setId($id)
            ->setType($className)
            ->setRule($this->_rule)
            ->setPrefix('conditions');

        if (!empty($typeData[1])) {
            $model->setAttribute($typeData[1]);
        }

        $result = '';
        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $result = $model->asHtmlRecursive();
        }
        $this->getResponse()->setBody($result);
    }
}
