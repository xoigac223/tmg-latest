<?php

namespace Mirasvit\Sorting\Plugin\Frontend\CatalogWidget\Block\Product\ProductsList;

use Mirasvit\Sorting\Api\Repository\CriterionRepositoryInterface;
use Mirasvit\Sorting\Service\CriteriaApplierService;

class ApplySortingPlugin
{
    private $criterionRepository;

    private $criteriaApplierService;

    public function __construct(
        CriterionRepositoryInterface $criterionRepository,
        CriteriaApplierService $criteriaApplierService
    ) {
        $this->criterionRepository    = $criterionRepository;
        $this->criteriaApplierService = $criteriaApplierService;
    }

    /**
     * @param \Magento\CatalogWidget\Block\Product\ProductsList       $subject
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function afterCreateCollection($subject, $collection)
    {
        $this->criteriaApplierService->applyGlobalRankingFactors($collection);

        $sortBy = $subject->getData('sort_by');

        if ($sortBy) {
            $criteria = $this->criterionRepository->getByCode($sortBy);

            if (!$criteria) {
                return $collection;
            }

            $this->criteriaApplierService->applyCriterion($criteria, $collection);
        }

        return $collection;
    }
}