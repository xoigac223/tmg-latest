<?php

namespace Mirasvit\Sorting\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Mirasvit\Sorting\Api\Repository\RankingFactorRepositoryInterface;

class FactorSource implements ArrayInterface
{
    private $repository;

    public function __construct(
        RankingFactorRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function toOptionArray()
    {
        $result = [];

        foreach ($this->repository->getFactors() as $identifier => $factor) {
            $result[] = [
                'label' => __($factor->getName()),
                'value' => $identifier
            ];
        }

        return $result;
    }
}