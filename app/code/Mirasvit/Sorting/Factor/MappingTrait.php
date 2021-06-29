<?php

namespace Mirasvit\Sorting\Factor;

trait MappingTrait
{
    /**
     * @param array  $mapping
     * @param string $id
     *
     * @return int
     */
    private function getValue($mapping, $id)
    {
        $id = array_filter(explode(',', $id));

        $value = [];
        foreach ($mapping as $item) {
            if (in_array($item['id'], $id)) {
                $value[] = $item['value'];
            }
        }

        return count($value) ? max($value) : 0;
    }
}