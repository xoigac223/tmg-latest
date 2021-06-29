<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */
namespace Amasty\Shopby\Model\Layer\Filter;

use Magento\Framework\Exception\StateException;
use Magento\Search\Model\SearchEngine;
use Amasty\Shopby\Model\Layer\Filter\Traits\FromToDecimal;
use Amasty\Shopby\Model\Source\DisplayMode;
use Amasty\Shopby\Api\Data\FromToFilterInterface;
use Amasty\Shopby\Model\Source\PositionLabel;

class Decimal extends \Magento\CatalogSearch\Model\Layer\Filter\Decimal implements FromToFilterInterface
{
    use FromToDecimal;

    /**
     * @var \Amasty\Shopby\Helper\FilterSetting
     */
    private $settingHelper;

    /**
     * @var \Amasty\Shopby\Model\Search\Adapter\Mysql\AggregationAdapter
     */
    private $aggregationAdapter;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
     */
    private $dataProvider;

    /**
     * @var \Amasty\Shopby\Model\Request
     */
    private $shopbyRequest;

    /**
     * @var float|int|null
     */
    private $extraToValue;

    /**
     * @var array|null
     */
    private $facetedData;

    /**
     * @var \Amasty\Shopby\Helper\Group
     */
    private $groupHelper;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var string
     */
    private $currencySymbol;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\DecimalFactory $filterDecimalFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Amasty\Shopby\Helper\FilterSetting $settingHelper,
        \Amasty\Shopby\Model\Search\Adapter\Mysql\AggregationAdapter $aggregationAdapter,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $dataProviderFactory,
        \Amasty\Shopby\Model\Request $shopbyRequest,
        \Amasty\Shopby\Helper\Group $groupHelper,
        SearchEngine $searchEngine,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $filterDecimalFactory,
            $priceCurrency,
            $data
        );
        $this->settingHelper = $settingHelper;
        $this->currencySymbol = $priceCurrency->getCurrencySymbol();
        $this->aggregationAdapter = $aggregationAdapter;
        $this->dataProvider = $dataProviderFactory->create(['layer' => $layer]);
        $this->shopbyRequest = $shopbyRequest;
        $this->groupHelper = $groupHelper;
        $this->searchEngine = $searchEngine;
        $this->messageManager = $messageManager;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $filter = $this->shopbyRequest->getFilterParam($this);
        $noValidate = 0;
        if (!empty($filter) && !is_array($filter)) {
            $filterParams = explode(',', $filter);
            $validateFilter = $this->dataProvider->validateFilter($filterParams[0]);
            if (!$validateFilter) {
                $noValidate = 1;
            } else {
                $this->setFromTo($validateFilter[0], $validateFilter[1]);
            }
        }
        if ($this->isApplied()) {
            return $this;
        }

        if ($noValidate) {
            return $this;
        }

        $request->setQueryValue($this->getRequestVar(), $filter);
        $apply = parent::apply($request);
        if (!empty($filter) && !is_array($filter)) {
            $filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
            if ($filterSetting->getDisplayMode() == DisplayMode::MODE_SLIDER) {
                $facets = $this->getFacetedData();
                $arrayRange = $this->getExtremeValues($filterSetting, $facets);
                $this->setFromTo($arrayRange['from'], $arrayRange['to']);
            }
        }

        return $apply;
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        $filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        $ignoreRanges = $filterSetting->getDisplayMode() == DisplayMode::MODE_FROM_TO_ONLY
            || $filterSetting->getDisplayMode() == DisplayMode::MODE_SLIDER;
        $itemsCount = $ignoreRanges ? 0 : parent::getItemsCount();

        if ($itemsCount == 0) {
            /**
             * show up filter event don't have any option
             */
            $fromToConfig = $this->getFromToConfig();
            if ($fromToConfig && $fromToConfig['min'] != $fromToConfig['max']) {
                return 1;
            }
        }

        return $itemsCount;
    }

    /**
     * @return array
     */
    public function getFromToConfig()
    {
        return $this->getConfig('decimal');
    }

    /**
     * @return array
     */
    protected function _getItemsData()
    {
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        $productSize = $productCollection->getSize();
        $facets = $this->getFacetedData();

        $data = [];
        foreach ($facets as $key => $aggregation) {
            if ($key === 'data') {
                continue;
            }
            $count = $aggregation['count'];
            if (!$this->isOptionReducesResults($count, $productSize)) {
//                continue;
            }
            list($from, $to) = explode('_', $key);
            if ($from == '*') {
                $from = '';
            }
            if ($to == '*') {
                $to = '';
            }

            $label = $this->renderRangeLabel(
                empty($from) ? 0 : $from,
                empty($to) ? $to : $to
            );

            $value = $from . '-' . $to;

            $data[] = [
                'label' => $label,
                'value' => $value,
                'count' => $count,
                'from' => $from,
                'to' => $to
            ];
        }

        return $data;
    }

    private function getAlteredQueryResponse()
    {
        $alteredQueryResponse = null;
        if ($this->hasCurrentValue()) {
            $attribute = $this->getAttributeModel();
            $productCollection = $this->getLayer()->getProductCollection();
            $requestBuilder = clone $productCollection->getMemRequestBuilder();
            $requestBuilder->removePlaceholder($attribute->getAttributeCode() . '.from');
            $requestBuilder->removePlaceholder($attribute->getAttributeCode() . '.to');
            $requestBuilder->setAggregationsOnly($attribute->getAttributeCode());
            $queryRequest = $requestBuilder->create();
            $alteredQueryResponse = $this->searchEngine->search($queryRequest);
        }

        return $alteredQueryResponse;
    }

    /**
     * @return array
     */
    private function getFacetedData()
    {
        if ($this->facetedData === null) {
            $productCollection = $this->getLayer()->getProductCollection();
            $alteredQueryResponse = $this->getAlteredQueryResponse();
            try {
                $this->facetedData = $productCollection->getFacetedData(
                    $this->getAttributeModel()->getAttributeCode(),
                    $alteredQueryResponse
                );
            } catch (StateException $e) {
                if (!$this->messageManager->hasMessages()) {
                    $this->messageManager->addErrorMessage(
                        __('Make sure that "%1" attribute can be used in layered navigation',
                            $this->getAttributeModel()->getAttributeCode()
                        )
                    );
                }
                $this->facetedData = [];
            }
        }

        return $this->facetedData;
    }

    /**
     * @param float|string $fromPrice
     * @param float|string $toPrice
     * @return \Magento\Framework\Phrase
     */
    protected function renderRangeLabel($fromPrice, $toPrice)
    {
        $ranges = $this->groupHelper->getGroupAttributeMinMaxRanges($this->getAttributeModel()->getAttributeId());
        if ($ranges) {
            if (isset($ranges[$fromPrice . '-' . $toPrice])) {
                return __($ranges[$fromPrice . '-' . $toPrice]);
            }
        }

        $filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        if ($filterSetting->getUnitsLabelUseCurrencySymbol()) {
            return parent::renderRangeLabel($fromPrice, $toPrice);
        }

        $formattedFromPrice = $this->formatLabelForStateAndRange($fromPrice, $filterSetting);
        if ($toPrice === '') {
            return __('%1 and above', $formattedFromPrice);
        } else {
            return __(
                '%1 - %2',
                $formattedFromPrice,
                $this->formatLabelForStateAndRange($toPrice, $filterSetting)
            );
        }
    }

    /**
     * @param $value
     * @param $filterSetting
     * @return string
     */
    private function formatLabelForStateAndRange($value, $filterSetting)
    {
        $value = round((float)$value, 2);
        if ($filterSetting->getPositionLabel() == PositionLabel::POSITION_BEFORE) {
            $formattedLabel = sprintf("%s%s", $filterSetting->getUnitsLabel(), $value);
        } else {
            $formattedLabel = sprintf("%s%s", $value, $filterSetting->getUnitsLabel());
        }

        return $formattedLabel;
    }
}
