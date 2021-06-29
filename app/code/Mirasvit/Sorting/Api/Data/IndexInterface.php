<?php

namespace Mirasvit\Sorting\Api\Data;

interface IndexInterface
{
    const TABLE_NAME = 'mst_sorting_index';

    CONST MIN = -100;
    CONST MAX = 100;

    const PRODUCT_ID = 'product_id';
    const FACTOR_ID  = 'factor_id';
    const VALUE      = 'value';
}