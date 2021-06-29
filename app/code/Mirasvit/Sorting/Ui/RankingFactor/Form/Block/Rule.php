<?php

namespace Mirasvit\Sorting\Ui\RankingFactor\Form\Block;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset as FieldsetRenderer;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Api\Repository\RankingFactorRepositoryInterface;

class Rule extends Form implements TabInterface
{
    private $rankingFactorRepository;

    /**
     * @var FieldsetRenderer
     */
    private $fieldsetRenderer;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    private $conditions;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    private $formFactory;

    /**
     * @var \Magento\Backend\Model\Url
     */
    private $urlManager;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    private $context;

    /**
     * @var string
     */
    protected $_nameInLayout = 'config.conditions_serialized';

    public function __construct(
        RankingFactorRepositoryInterface $rankingFactorRepository,
        Conditions $conditions,
        FieldsetRenderer $fieldsetRenderer,
        FormFactory $formFactory,
        Registry $registry,
        Context $context
    ) {
        $this->rankingFactorRepository = $rankingFactorRepository;
        $this->fieldsetRenderer        = $fieldsetRenderer;
        $this->conditions              = $conditions;
        $this->formFactory             = $formFactory;
        $this->registry                = $registry;
        $this->context                 = $context;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Conditions');
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
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $formName = \Mirasvit\Sorting\Factor\ProductRule\Rule::FORM_NAME;

        /** @var RankingFactorInterface $rankingFactor */
        $rankingFactor = $this->registry->registry(RankingFactorInterface::class);

        /** @var \Mirasvit\Sorting\Factor\RuleFactor $factor */
        $factor = $this->rankingFactorRepository->getFactor($rankingFactor->getType());

        $rule = $factor->getRule($rankingFactor);

        $form = $this->formFactory->create();
        $form->setData('html_id_prefix', 'config_');

        $fieldsetName = 'conditions_fieldset';

        $renderer = $this->fieldsetRenderer
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setData('new_child_url', $this->getUrl('*/rankingFactor/newConditionHtml', [
                'form'      => 'config_' . $fieldsetName,
                'form_name' => $formName,
            ]));

        $fieldset = $form->addFieldset($fieldsetName, [
            'legend' => __('Conditions (leave blank for all products)'),

        ])->setRenderer($renderer);

        $rule->getConditions()
            ->setFormName($formName);

        $conditionsField = $fieldset->addField('conditions', 'text', [
            'name'           => 'conditions',
            'required'       => true,
            'data-form-part' => $formName,
        ]);

        $conditionsField->setRule($rule)
            ->setRenderer($this->conditions)
            ->setFormName($formName);

        $form->setValues($rankingFactor->getData());
        $this->setConditionFormName($rule->getConditions(), $formName);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param object $conditions
     * @param string $formName
     *
     * @return void
     */
    private function setConditionFormName($conditions, $formName)
    {
        $conditions->setFormName($formName);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }

    /**
     * Compatibility with 2.1.x
     * @return $this
     */
    public function setLayout(\Magento\Framework\View\LayoutInterface $layout)
    {
        return $this;
    }
}
