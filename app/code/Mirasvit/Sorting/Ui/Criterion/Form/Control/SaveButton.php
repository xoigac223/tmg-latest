<?php

namespace Mirasvit\Sorting\Ui\Criterion\Form\Control;

class SaveButton extends ButtonAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label'          => __('Save'),
            'class'          => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order'     => 90,
        ];
    }
}
