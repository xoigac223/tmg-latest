<?php

namespace Mirasvit\Sorting\Model\ResourceModel\RankingFactor;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(
            \Mirasvit\Sorting\Model\RankingFactor::class,
            \Mirasvit\Sorting\Model\ResourceModel\RankingFactor::class
        );
    }
}
