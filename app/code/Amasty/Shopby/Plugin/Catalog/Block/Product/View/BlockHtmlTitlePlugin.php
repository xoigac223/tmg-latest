<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Block\Product\View;

use Amasty\ShopbyBase\Helper\FilterSetting as FilterHelper;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\OptionSetting;
use Amasty\ShopbyBase\Model\FilterSetting;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting\CollectionFactory as FilterCollectionFactory;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\CollectionFactory as OptionCollectionFactory;
use Amasty\ShopbyBase\Plugin\Catalog\Block\Product\View\BlockHtmlTitlePluginAbstract;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Store\Model\StoreManagerInterface;

class BlockHtmlTitlePlugin extends BlockHtmlTitlePluginAbstract
{
    /**
     * @var \Amasty\ShopbyBase\Model\ResourceModel\FilterSetting\Collection
     */
    private $filterCollection;
    /**
     * @var \Amasty\ShopbyBase\Helper\Data
     */
    private $baseHelper;

    public function __construct(
        OptionCollectionFactory $optionCollectionFactory,
        Registry $registry,
        StoreManagerInterface $storeManager,
        BlockFactory $blockFactory,
        Configurable $configurableType,
        FilterCollectionFactory $filterCollectionFactory,
        \Amasty\ShopbyBase\Helper\Data $baseHelper
    ) {
        parent::__construct($optionCollectionFactory, $registry, $storeManager, $blockFactory, $configurableType);
        $this->filterCollection = $filterCollectionFactory->create();
        $this->baseHelper = $baseHelper;
    }

    /**
     * @return array
     */
    protected function getAttributeCodes()
    {
        $filtersToShow = $this->filterCollection
            ->addFieldToSelect(OptionSetting::FILTER_CODE)
            ->addFieldToFilter(FilterSettingInterface::SHOW_ICONS_ON_PRODUCT, true);
        $attributeCodes = [];
        foreach ($filtersToShow as $filter) {
            /** @var FilterSetting $filter */
            $attributeCodes[] = substr($filter->getFilterCode(), strlen(FilterHelper::ATTR_PREFIX));
        }

        $brandCode = $this->baseHelper->getBrandAttributeCode();
        $brandCode = $brandCode ? [$brandCode] : [];

        $attributeCodes = array_diff($attributeCodes, $brandCode);

        return $attributeCodes;
    }
}
