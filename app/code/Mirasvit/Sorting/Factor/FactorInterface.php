<?php

namespace Mirasvit\Sorting\Factor;

use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

interface FactorInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string|false
     */
    public function getUiComponent();

    public function reindexAll(RankingFactorInterface $rankingFactor);
}