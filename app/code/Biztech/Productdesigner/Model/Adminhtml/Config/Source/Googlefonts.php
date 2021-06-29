<?php
namespace Biztech\Productdesigner\Model\Adminhtml\Config\Source;
class Googlefonts extends \Magento\Config\Block\System\Config\Form\Field {

    public function toOptionArray() {

        $font_array = array('Rancho', 'Inconsolata', 'Philosopher', 'Plaster', 'Rokkitt', 'Sofia', 'Playball', 'Nosifer', 'Pacifico','Tangerine');

        foreach ($font_array as $font) {
            $option_array[] = array(
                'value' => str_replace(' ', '+', $font),
                'label' => __($font)
            );
        }


        return $option_array;
    }

}
