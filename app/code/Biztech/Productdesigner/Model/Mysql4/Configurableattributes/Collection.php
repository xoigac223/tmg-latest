<?php
namespace Biztech\Productdesigner\Model\Mysql4\Configurableattributes;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
     protected function _construct()
    {
        $this->_init('Biztech\Productdesigner\Model\Configurableattributes', 'Biztech\Productdesigner\Model\Mysql4\Configurableattributes');
    }
}
