<?php
/**
* Copyright © Pulse Storm LLC 2016
* All rights reserved
*/
namespace Stephanieragsdale\Commercebug\Model\ResourceModel\Log;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Stephanieragsdale\Commercebug\Model\Log','Stephanieragsdale\Commercebug\Model\ResourceModel\Log');
    }
}
