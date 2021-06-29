<?php

namespace Mirasvit\Sorting\Model\Config\Source\Factor\Date;

use Magento\Framework\Option\ArrayInterface;

class DateFieldSource implements ArrayInterface
{
    public function toOptionArray()
    {
        $result = [
            [
                'label' => 'Creation Date',
                'value' => 'created_at',
            ],
            [
                'label' => 'Updating Date',
                'value' => 'updated_at',
            ],
        ];

        return $result;
    }
}