<?php

namespace Mirasvit\Sorting\Factor;

use Magento\Eav\Model\Config as EavConfig;

class Context
{
    public $eavConfig;

    public function __construct(
        EavConfig $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }
}