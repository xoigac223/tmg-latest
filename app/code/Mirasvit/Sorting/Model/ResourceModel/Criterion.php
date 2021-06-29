<?php

namespace Mirasvit\Sorting\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Mirasvit\Sorting\Api\Data\CriterionInterface;

class Criterion extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(CriterionInterface::TABLE_NAME, CriterionInterface::ID);
    }
}
