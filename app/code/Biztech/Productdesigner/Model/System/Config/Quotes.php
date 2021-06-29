<?php

namespace Biztech\Productdesigner\Model\System\Config;
use Magento\Framework\Option\ArrayInterface;
class Quotes implements ArrayInterface {


 protected $_objectManager;

    public function __construct(
    \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {

        $this->_objectManager = $objectmanager;
    }
    
/**
 * @return array
 */
public function toOptionArray()
{
     $product_id = $this->getRequest()->getParam('options');
 $model1 = $this->_objectManager->create('Biztech\Productdesigner\Model\Mysql4\Quotescategory\Collection');
 $collection = ($model1->getData());
 

}
}
