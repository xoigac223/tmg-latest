<?php

namespace Mirasvit\Sorting\Ui\RankingFactor\Listing;

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
            $itemData = $item->getData();
//            foreach ([TemplateInterface::ICON, TemplateInterface::IMAGE] as $key) {
//                foreach ($item->getCustomAttributes() as $attribute) {
//                    $itemData[$attribute->getAttributeCode()] = $attribute->getValue();
//                }
//
//                if ($item->getData($key)) {
//                    $itemData[$key . '_src'] = $this->config->getMediaUrl($item->getData($key));
//                }
//            }
            $arrItems['items'][] = $itemData;
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        return $arrItems;
    }
}
