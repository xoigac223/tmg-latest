<?php

namespace Mirasvit\Sorting\Api\Data;

interface RankingFactorInterface
{
    const TABLE_NAME = 'mst_sorting_ranking_factor';

    const ID = 'factor_id';

    const NAME              = 'name';
    const IS_ACTIVE         = 'is_active';
    const TYPE              = 'type';
    const IS_GLOBAL         = 'is_global';
    const WEIGHT            = 'weight';
    const CONFIG_SERIALIZED = 'config_serialized';
    const CONFIG            = 'config';

    /**
     * @return int
     */
    public function getId();

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
     * @return string
     */
    public function getType();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setType($value);

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
    public function isGlobal();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setIsGlobal($value);

    /**
     * @return int
     */
    public function getWeight();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setWeight($value);

    /**
     * @return array
     */
    public function getConfig();

    /**
     * @param array $value
     *
     * @return $this
     */
    public function setConfig(array $value);

    /**
     * @param string       $key
     * @param string|false $default
     *
     * @return string|array
     */
    public function getConfigData($key, $default = false);
}