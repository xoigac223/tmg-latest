<?php

namespace Mirasvit\Sorting\Plugin\Frontend\Catalog\Model\Config;

use Magento\Catalog\Model\Config;
use Mirasvit\Sorting\Model\Config\Source\CriteriaSource;

class ReplaceOptionsPlugin
{
    private $criteria;

    public function __construct(CriteriaSource $criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * Add Improved Sorting criteria to default "sort by" options.
     *
     * @param Config $subject
     * @param array  $result
     *
     * @return array
     */
    public function afterGetAttributeUsedForSortByArray(Config $subject, array $result = [])
    {
        return $this->criteria->toArray();
    }
}