<?php

namespace Biztech\Productdesigner\Model\System\Config;

class Shapescategories extends \Magento\Framework\Model\AbstractModel {

    protected $_objectManager;

    public function __construct(
    \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {

        $this->_objectManager = $objectmanager;
    }

    public function toOptionArray() {

        $model1 = $this->_objectManager->create('Biztech\Productdesigner\Model\Mysql4\Shapes\Collection');
        $collection = ($model1->getData());
       
        $template_array = array();
        foreach ($collection as $designtemplatescategry) {


            $label = $designtemplatescategry['shapes_title'];
            
            

            $template_array[] = array(
                'label' => $label,
                 'value' => $designtemplatescategry['shapes_id']
            );
        }

        return $template_array;
    }

}
