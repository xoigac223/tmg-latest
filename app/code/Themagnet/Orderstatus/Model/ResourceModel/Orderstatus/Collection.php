<?php

namespace Themagnet\Orderstatus\Model\ResourceModel\Orderstatus;

use  Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'orderstatus_id';
    protected function _construct()
    {
        $this->_init(
            'Themagnet\Orderstatus\Model\Orderstatus',
            'Themagnet\Orderstatus\Model\ResourceModel\Orderstatus'
        );
    }
}