<?php

namespace Mirasvit\Sorting\Factor\ProductRule;

use Magento\Rule\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\CatalogRule\Model\Rule\Condition\CombineFactory as CatalogRuleCombineFactory;

class Rule extends AbstractModel
{
    const FORM_NAME = 'sorting_rankingFactor_form';

    private $conditionCombineFactory;

    public function __construct(
        CatalogRuleCombineFactory $conditionCombineFactory,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate
    ) {
        $this->conditionCombineFactory = $conditionCombineFactory;

        parent::__construct($context, $registry, $formFactory, $localeDate);
    }

    public function getActionsInstance()
    {
    }

    public function getConditionsInstance()
    {
        return $this->conditionCombineFactory->create();
    }
}
