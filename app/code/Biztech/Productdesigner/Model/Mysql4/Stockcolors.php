<?php

namespace Biztech\Productdesigner\Model\Mysql4;

class Stockcolors extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    protected function _construct()
    {
        $this->_init('productdesigner_product_stockcolors', 'product_stockcolor_id');
    }

}
