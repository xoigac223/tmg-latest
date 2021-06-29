<?php

namespace Mirasvit\Sorting\Plugin\Adminhtml\Catalog\Model\Category\Attribute\Source\SortBy;

use Magento\Catalog\Model\Category\Attribute\Source\Sortby;
use Mirasvit\Sorting\Model\Config\Source\CriteriaSource;

class ReplaceOptionsPlugin
{
    /**
     * @var CriteriaSource
     */
    private $criteria;

    public function __construct(CriteriaSource $criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * Add Improved Sorting criteria to default "sort by" options.
     *
     * @param Sortby $subject
     * @param array  $result
     *
     * @return array
     */
    public function afterGetAllOptions(Sortby $subject, array $result = [])
    {
        return $this->criteria->toOptionArray();
    }
}
