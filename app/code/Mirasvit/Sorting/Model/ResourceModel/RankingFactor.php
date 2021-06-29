<?php

namespace Mirasvit\Sorting\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

class RankingFactor extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(RankingFactorInterface::TABLE_NAME, RankingFactorInterface::ID);
    }
}
