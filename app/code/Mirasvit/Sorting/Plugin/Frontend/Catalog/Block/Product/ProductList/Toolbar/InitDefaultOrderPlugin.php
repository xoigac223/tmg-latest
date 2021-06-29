<?php

namespace Mirasvit\Sorting\Plugin\Frontend\Catalog\Block\Product\ProductList\Toolbar;

use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Mirasvit\Sorting\Service\CriteriaManagementService;

class InitDefaultOrderPlugin
{
    /**
     * @var CriteriaManagementService
     */
    private $criteriaManagement;

    public function __construct(
        CriteriaManagementService $criteriaManagement
    ) {
        $this->criteriaManagement = $criteriaManagement;
    }

    /**
     * Initialize default sort order and direction.
     *
     * @param Toolbar                            $subject
     * @param \Magento\Framework\Data\Collection $collection
     */
    public function beforeSetCollection(Toolbar $subject, $collection)
    {
        if ($criterion = $this->criteriaManagement->getDefaultCriterion()) {
            $subject->setDefaultOrder($criterion->getCode());

            $subject->setDefaultDirection($this->criteriaManagement->getDefaultDirection($criterion));
        }
    }
}
