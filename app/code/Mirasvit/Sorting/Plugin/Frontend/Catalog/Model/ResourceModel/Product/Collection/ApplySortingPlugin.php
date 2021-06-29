<?php

namespace Mirasvit\Sorting\Plugin\Frontend\Catalog\Model\ResourceModel\Product\Collection;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Db\Select;
use Mirasvit\Sorting\Api\Repository\CriterionRepositoryInterface;
use Mirasvit\Sorting\Service\CriteriaApplierService;

class ApplySortingPlugin
{
    private $criterionRepository;

    private $criteriaApplierService;

    public function __construct(
        CriteriaApplierService $criteriaApplierService,
        CriterionRepositoryInterface $criterionRepository
    ) {
        $this->criterionRepository    = $criterionRepository;
        $this->criteriaApplierService = $criteriaApplierService;
    }

    public function beforeAddAttributeToSort(Collection $collection, $attribute, $dir = Select::SQL_DESC)
    {
        return $this->beforeSetOrder($collection, $attribute, $dir);
    }

    /**
     * Apply sort criteria to collection.
     *
     * @param Collection $collection
     * @param string     $attribute
     * @param string     $dir
     *
     * @return array
     */
    public function beforeSetOrder(Collection $collection, $attribute, $dir = Select::SQL_DESC)
    {
        if ($collection->getFlag($attribute)) {
            return [$attribute, $dir];
        }

        $collection->setFlag($attribute, true);

        if (!$collection->getFlag('global')) {
            $collection->setFlag('global', true);

            $this->criteriaApplierService->applyGlobalRankingFactors($collection);
        }

        $criterion = $this->criterionRepository->getByCode($attribute);

        if ($criterion) {
            $this->criteriaApplierService->applyCriterion($criterion, $collection, $dir);
        }

        return [$attribute, $dir];
    }

}
