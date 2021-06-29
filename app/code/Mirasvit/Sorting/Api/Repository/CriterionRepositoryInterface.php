<?php

namespace Mirasvit\Sorting\Api\Repository;

use Mirasvit\Sorting\Api\Data\CriterionInterface;

interface CriterionRepositoryInterface
{
    /**
     * @return \Mirasvit\Sorting\Model\ResourceModel\Criterion\Collection | CriterionInterface[]
     */
    public function getCollection();

    /**
     * @return CriterionInterface
     */
    public function create();

    /**
     * @param int $id
     *
     * @return CriterionInterface|false
     */
    public function get($id);

    /**
     * @param string $code
     *
     * @return CriterionInterface|false
     */
    public function getByCode($code);

    /**
     * @param CriterionInterface $model
     *
     * @return $this
     */
    public function save(CriterionInterface $model);

    /**
     * @param CriterionInterface $model
     *
     * @return $this
     */
    public function delete(CriterionInterface $model);
}
