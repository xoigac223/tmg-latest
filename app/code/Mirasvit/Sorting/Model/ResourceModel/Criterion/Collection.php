<?php

namespace Mirasvit\Sorting\Model\ResourceModel\Criterion;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(
            \Mirasvit\Sorting\Model\Criterion::class,
            \Mirasvit\Sorting\Model\ResourceModel\Criterion::class
        );
    }
}
