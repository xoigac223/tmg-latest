<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Helper;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterface;
use Amasty\ShopbyBase\Helper\FilterSetting as FilterSettingHelper;
use Amasty\ShopbyBase\Helper\OptionSetting as OptionSettingHelper;
use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\CollectionFactory as OptionCollectionFactory;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class Data extends AbstractHelper
{
    const PATH_BRAND_URL_KEY = 'amshopby_brand/general/url_key';

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var OptionSettingHelper
     */
    private $optionSettingHelper;

    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var OptionCollectionFactory
     */
    private $optionCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var ProductUrl
     */
    private $productUrl;

    /**
     * @var OptionSettingRepositoryInterface
     */
    private $optionSettingRepository;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var array
     */
    private $brandAliases = [];

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        OptionSettingHelper $optionSettingHelper,
        AttributeRepository $repository,
        OptionCollectionFactory $optionCollectionFactory,
        StoreManagerInterface $storeManager,
        ProductUrl $productUrl,
        OptionSettingRepositoryInterface $optionSettingRepository,
        \Magento\Framework\Escaper $escaper
    ) {
        parent::__construct($context);
        $this->url = $context->getUrlBuilder();
        $this->optionSettingHelper = $optionSettingHelper;
        $this->attributeRepository = $repository;
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->storeManager = $storeManager;
        $this->productUrl = $productUrl;
        $this->optionSettingRepository = $optionSettingRepository;
        $this->escaper = $escaper;
    }

    /**
     * @param null $scopeCode
     * @return string
     */
    public function getAllBrandsUrl($scopeCode = null)
    {
        $pageIdentifier = $this->scopeConfig->getValue(
            'amshopby_brand/general/brands_page',
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );

        $identifierWithId = explode('|', $pageIdentifier);

        return $this->url->getUrl($identifierWithId[0]);
    }

    /**
     * Update branded option setting collection.
     */
    public function updateBrandOptions()
    {
        $attrCode   = $this->scopeConfig->getValue(
            'amshopby_brand/general/attribute_code',
            ScopeInterface::SCOPE_STORE
        );

        if (!$attrCode) {
            return;
        }

        $filterCode = FilterSettingHelper::ATTR_PREFIX . $attrCode;
        $currentAttributeValues = $this->getCurrentBrandAttributeValues($attrCode);
        // Temporary disabled: planning feature "Relink"
        //$this->deleteExtraBrandOptions($currentAttributeValues, $filterCode);
        $this->addMissingBrandOptions($currentAttributeValues, $filterCode);
    }

    /**
     * @param string $attrCode
     * @return string[]
     */
    private function getCurrentBrandAttributeValues($attrCode)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\Option[]  $attributeOptions */
        $attributeOptions = $this->attributeRepository->get($attrCode)->getOptions();
        $attributeValues = [];
        foreach ($attributeOptions as $option) {
            if ($option->getValue()) {
                $attributeValues[] = $option->getValue();
            }
        }
        return $attributeValues;
    }

    /**
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface[]|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBrandOptions()
    {
        try {
            $result = $this->attributeRepository->get($this->getBrandAttributeCode())->getOptions();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $result = [];
        }

        return $result;
    }

    /**
     * @param string[] $currentAttributeValues
     * @param string $filterCode
     */
    private function deleteExtraBrandOptions($currentAttributeValues, $filterCode)
    {
        $settingOptionCollection = $this->optionCollectionFactory->create();
        $optionsToDelete = $settingOptionCollection
            ->addFieldToFilter(OptionSettingInterface::FILTER_CODE, $filterCode)
            ->addFieldToFilter(OptionSettingInterface::VALUE, ['nin' => $currentAttributeValues]);
        foreach ($optionsToDelete as $optionSetting) {
            /** @var \Amasty\ShopbyBase\Model\OptionSetting $optionSetting */
            $optionSetting->getResource()->delete($optionSetting);
        }
    }

    /**
     * @param string[] $currentAttributeValues
     * @param string $filterCode
     */
    private function addMissingBrandOptions($currentAttributeValues, $filterCode)
    {
        foreach ($currentAttributeValues as $value) {
            /** @var \Amasty\ShopbyBase\Model\OptionSetting $optionSetting */
            $optionSetting = $this->optionSettingHelper->getSettingByValue($value, $filterCode, 0);
            if (!$optionSetting->getId()) {
                $this->optionSettingRepository->save($optionSetting);
            }
        }
    }

    /**
     * @return array
     */
    public function getBrandAliases()
    {
        if (empty($this->brandAliases)) {
            $attributeCode = $this->getBrandAttributeCode();
            if ($attributeCode == '') {
                return [];
            }

            $suffix = $this->getSuffix();

            $options = $this->attributeRepository->get($attributeCode)->getOptions();
            array_shift($options);

            if (empty($options)) {
                return [];
            }

            $items = [];
            foreach ($options as $option) {
                $items[$option->getValue()] = str_replace(
                    '-',
                    $this->getSpecialChar(),
                    $this->productUrl->formatUrlKey($option->getLabel())
                );
            }

            $this->brandAliases = $this->getStoreAliases($items, $this->storeManager->getStore()->getId());
        }
        return $this->brandAliases;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        $suffix = '';
        if ($this->scopeConfig->isSetFlag('amasty_shopby_seo/url/add_suffix_shopby')) {
            $suffix = $this->scopeConfig
                ->getValue('catalog/seo/category_url_suffix', ScopeInterface::SCOPE_STORE);
        }

        return $suffix;
    }

    /**
     * @return string
     */
    public function getBrandAttributeCode()
    {
        return $this->scopeConfig->getValue(
            'amshopby_brand/general/attribute_code',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getBrandUrlKey()
    {
        return $this->scopeConfig->getValue(
            self::PATH_BRAND_URL_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Option $option
     * @return string
     */
    public function getBrandUrl(\Magento\Eav\Model\Entity\Attribute\Option $option)
    {
        $url = '#';
        $aliases = $this->getBrandAliases();
        $urlKey = $this->scopeConfig->getValue(
            self::PATH_BRAND_URL_KEY,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
        if (isset($aliases[$option->getValue()])) {
            $brandAlias = $aliases[$option->getValue()];
            $url = $this->_urlBuilder->getBaseUrl() . (!!$urlKey ? $urlKey . '/' . $brandAlias : $brandAlias);
        }

        return $url;
    }

    /**
     * @param array $defaultAliases
     * @param int $storeId
     * @return array
     */
    private function getStoreAliases($defaultAliases, $storeId)
    {
        $storeIds = [
            \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            $storeId
        ];
        $filterCode =  FilterSettingHelper::ATTR_PREFIX . $this->getBrandAttributeCode();
        $collection = $this->optionCollectionFactory->create();
        $collection->addFieldToFilter('filter_code', ['eq' => $filterCode])
            ->addFieldToFilter('value', ['in' => array_keys($defaultAliases)])
            ->addFieldToFilter('store_id', ['in' => $storeIds])
            ->addFieldToFilter('url_alias', ['neq' => ''])
            ->setOrder('store_id', AbstractDb::SORT_ORDER_ASC);

        foreach ($collection as $item) {
            $formatAlias = $this->productUrl->formatUrlKey($item->getUrlAlias());
            $defaultAliases[$item->getValue()] = str_replace('-', $this->getSpecialChar(), $formatAlias);
        }

        return $defaultAliases;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getModuleConfig($path)
    {
        return $this->scopeConfig->getValue(
            'amshopby_brand/' . $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param array $item
     * @return array|string
     */
    public function generateToolTipContent(array $item)
    {
        $content = '';

        $template = $this->getModuleConfig('general/tooltip_content');
        $template = $this->replaceCustomVariables($template, $item);
        $template = trim($template);

        if ($template) {
            $template = $this->escaper->escapeHtml($template);
            $content = 'data-amshopby-js="brand-tooltip" data-tooltip-content="' . $template . '"';
        }

        return $content;
    }

    /**
     * @param string $template
     * @param array $item
     * @return string
     */
    private function replaceCustomVariables($template, array $item)
    {
        preg_match_all('@\{(.+?)\}@', $template, $matches);
        if (isset($matches[1]) && is_array($matches[1])) {
            foreach ($matches[1] as $match) {
                $value = '';
                switch ($match) {
                    case 'title':
                        if (isset($item['label'])) {
                            $value = '<h3>' . $item['label'] . '</h3>';
                        }
                        break;
                    case 'description':
                    case 'short_description':
                        if (isset($item[$match]) && $item[$match]) {
                            $value = '<p>' . $item[$match] . '</p>';
                        }
                        break;
                    case 'small_image':
                    case 'image':
                        $imgUrl = $match == 'small_image' ? $item['img'] : $item['image'];
                        if (isset($imgUrl)) {
                            $value = '<img class="am-brand-' . $match . '" src="' . $imgUrl . '"/>';
                        }
                        break;
                }
                $template = str_replace('{' . $match . '}', $value, $template);
            }
        }

        return $template;
    }

     /**
     * @return string
     */
    public function getSpecialChar()
    {
        return $this->_moduleManager->isEnabled('Amasty_ShopbySeo')
            ? $this->scopeConfig->getValue('amasty_shopby_seo/url/special_char', ScopeInterface::SCOPE_STORE)
            : '-';
    }

    /**
     * @return string
     */
    public function getBrandLabel()
    {
        return (string) $this->scopeConfig->getValue(
            'amshopby_brand/general/menu_item_label',
            ScopeInterface::SCOPE_STORE
        );
    }
}
