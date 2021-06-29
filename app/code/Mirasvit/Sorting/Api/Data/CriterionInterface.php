<?php

namespace Mirasvit\Sorting\Api\Data;

interface CriterionInterface
{
    const SORT_BY_ATTRIBUTE      = 'attribute';
    const SORT_BY_RANKING_FACTOR = 'ranking_factor';

    const TABLE_NAME = 'mst_sorting_criterion';

    const ID = 'criterion_id';

    const NAME                  = 'name';
    const CODE                  = 'code';
    const IS_ACTIVE             = 'is_active';
    const IS_DEFAULT            = 'is_default';
    const IS_SEARCH_DEFAULT     = 'is_search_default';
    const POSITION              = 'position';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const CONDITIONS            = 'conditions';

    const CONDITION_SORT_BY        = 'sortBy';
    const CONDITION_ATTRIBUTE      = 'attribute';
    const CONDITION_RANKING_FACTOR = 'rankingFactor';
    const CONDITION_DIRECTION      = 'direction';
    const CONDITION_WEIGHT         = 'weight';

    const CONDITION_SORT_BY_ATTRIBUTE      = 'attribute';
    const CONDITION_SORT_BY_RANKING_FACTOR = 'ranking_factor';


    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCode($value);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setName($value);

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setIsActive($value);

    /**
     * @return bool
     */
    public function isDefault();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setIsDefault($value);

    /**
     * @return bool
     */
    public function isSearchDefault();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setIsSearchDefault($value);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setPosition($value);

    /**
     * @return array
     */
    public function getConditions();

    /**
     * @param array $value
     *
     * @return $this
     */
    public function setConditions(array $value);
}
