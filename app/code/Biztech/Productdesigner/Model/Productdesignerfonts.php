<?php

namespace Biztech\Productdesigner\Model;

class Productdesignerfonts extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Biztech\Productdesigner\Model\Mysql4\Productdesignerfonts');
    }      
}