<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Model\Mysql4;

class Side extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('productdesigner_imageside', 'imageside_id');
    }
}
