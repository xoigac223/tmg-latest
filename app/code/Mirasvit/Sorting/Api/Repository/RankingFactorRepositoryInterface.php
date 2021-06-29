<?php

namespace Mirasvit\Sorting\Api\Repository;

use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Factor\FactorInterface;

interface RankingFactorRepositoryInterface
{
    /**
     * @return \Mirasvit\Sorting\Model\ResourceModel\RankingFactor\Collection | RankingFactorInterface[]
     */
    public function getCollection();

    /**
     * @return FactorInterface[]
     */
    public function getFactors();

    /**
     * @param string $type
     *
     * @return FactorInterface
     */
    public function getFactor($type);

    /**
     * @return RankingFactorInterface
     */
    public function create();

    /**
     * @param int $id
     *
     * @return RankingFactorInterface
     */
    public function get($id);

    /**
     * @param RankingFactorInterface $model
     *
     * @return $this
     */
    public function save(RankingFactorInterface $model);

    /**
     * @param RankingFactorInterface $model
     *
     * @return $this
     */
    public function delete(RankingFactorInterface $model);
}