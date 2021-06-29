<?php

namespace Mirasvit\Sorting\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Api\Repository\RankingFactorRepositoryInterface;

class SortBySource implements ArrayInterface
{
    private $repository;

    public function __construct(
        RankingFactorRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function toOptionArray()
    {
        $result = [
            [
                'label' => 'Attribute',
                'value' => CriterionInterface::CONDITION_SORT_BY_ATTRIBUTE,
            ],
            [
                'label' => 'Ranking Factor',
                'value' => CriterionInterface::CONDITION_SORT_BY_RANKING_FACTOR,
            ],
        ];

        return $result;
    }
}