<?php

namespace Mirasvit\Sorting\Ui\Criterion\Listing;

use Magento\Framework\Api\Search\SearchResultInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{

    /**
     * {@inheritdoc}
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems = [];

        $arrItems['items'] = [];
        foreach ($searchResult->getItems() as $item) {
            $itemData            = $item->getData();
            $arrItems['items'][] = $itemData;
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        return $arrItems;
    }
}
