<?php

namespace Biztech\Productdesigner\Model\Adminhtml\Config\Source;

class Template extends \Magento\Config\Block\System\Config\Form\Field {

    public function toOptionArray() {
        return array(
            array('value' => 'full_view', 'label' => __('Full View')),
            array('value' => 'box_view', 'label' => __('Box View')),
        );
    }

}
