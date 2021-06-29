<?php

namespace Biztech\Productdesigner\Block\Adminhtml\Config;

class Baseunit extends \Magento\Config\Block\System\Config\Form\Field {

    public function toOptionArray() {
        return array(
            array('value' => 'px', 'label' => __('Pixels')),
            array('value' => 'cm', 'label' => __('Centimeters')),
        );
    }

}
