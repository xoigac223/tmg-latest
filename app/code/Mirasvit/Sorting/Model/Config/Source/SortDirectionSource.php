<?php

namespace Mirasvit\Sorting\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class SortDirectionSource implements ArrayInterface
{
    public function toOptionArray()
    {
        $result = [
            [
                'label' => 'Ascending',
                'value' => 'asc',
            ],
            [
                'label' => 'Descending',
                'value' => 'desc',
            ],
        ];

        return $result;
    }
}