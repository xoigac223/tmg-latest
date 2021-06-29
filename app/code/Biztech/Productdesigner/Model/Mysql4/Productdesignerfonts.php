<?php
namespace Biztech\Productdesigner\Model\Mysql4;

class Productdesignerfonts extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('productdesigner_fonts', 'font_id');
    }
}