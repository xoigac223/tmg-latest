<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Model\Mysql4\Stockcolors;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Biztech\Productdesigner\Model\Stockcolors', 'Biztech\Productdesigner\Model\Mysql4\Stockcolors');
    }
}
