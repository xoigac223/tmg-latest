<?php

namespace Biztech\Productdesigner\Model\Mysql4;
class Configurableattributes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {    
        $this->_init('productdesigner_configurableattributes', 'attribute_id');
    }
}
