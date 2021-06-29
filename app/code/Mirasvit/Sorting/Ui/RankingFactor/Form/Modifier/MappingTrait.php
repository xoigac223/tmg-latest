<?php

namespace Mirasvit\Sorting\Ui\RankingFactor\Form\Modifier;

trait MappingTrait
{
    public function sync($options, $mapping)
    {

        # step 1: remove old
        foreach ($mapping as $idx => $item) {
            $id = $item['id'];

            $isFound = false;
            foreach ($options as $option) {
                if ($option['value'] == $id) {
                    $isFound = true;
                }
            }

            if (!$isFound) {
                unset($mapping[$idx]);
            }
        }

        # step 2: add new & fill labels
        foreach ($options as $option) {
            $label = $option['label'];
            $value = $option['value'];

            $isFound = false;
            foreach ($mapping as $idx => $item) {
                if ($item['id'] == $value) {
                    $mapping[$idx]['label'] = $label;

                    $isFound = true;
                }
            }

            if (!$isFound) {
                $mapping[] = [
                    'id'    => $value,
                    'label' => $label,
                    'value' => 0,
                ];
            }
        }

        $mapping = array_values($mapping);

        return $mapping;
    }
}