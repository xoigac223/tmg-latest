<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_ConfigurableProductWholesaleCustomize
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConfigurableProductWholesaleCustomize\Block\Product\Renderer;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Store\Model\ScopeInterface;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\Swatch;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Bss\ConfigurableProductWholesaleCustomize\Plugin\Context as ModulePlugin;
use TMG\CustomerPricing\Helper\CustomerPricing as CustomerPricingHelper;

class ConfigurableProductWholesale extends \Bss\ConfigurableProductWholesale\Block\Product\Renderer\ConfigurableProductWholesale
{
    protected $rawPriceList;
    protected $groupManagement;
    protected $customerSession;
    protected $httpContext;
    protected $customerPricingHelper;

    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Data $helper,
        CatalogProduct $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockRegistryProvider,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Bss\ConfigurableProductWholesale\Helper\Data $helperBss,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        GroupManagementInterface $groupManagement,
        CustomerSession $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        CustomerPricingHelper $customerPricingHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $swatchHelper,
            $swatchMediaHelper,
            $stockState,
            $stockRegistryProvider,
            $productRepository,
            $helperBss,
            $attrOptionCollectionFactory,
            $data
        );
        $this->helperBss             = $helperBss;
        $this->storeManager          = $context->getStoreManager();
        $this->stockState            = $stockState;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->stockRegistry         = $context->getStockRegistry();
        $this->productRepository     = $productRepository;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->groupManagement       = $groupManagement;
        $this->customerSession       = $customerSession;
        $this->httpContext           = $httpContext;
        $this->customerPricingHelper = $customerPricingHelper;
    }
    const PRODUCT_WHOLESALE_RENDERER_TEMPLATE = 'Bss_ConfigurableProductWholesaleCustomize::product/view/configurable.phtml';
    const PRODUCT_WHOLESALE_RENDERER_SWATCHES_TEMPLATE = 'Bss_ConfigurableProductWholesaleCustomize::product/view/renderer.phtml';

    public function getRendererTemplate()
    {
        if ($this->helperBss->getConfig()) {
            $hasSwatch = false;
            if ($this->helperBss->getMagentoVersion('2.1.6')) {
                $hasSwatch = $this->isProductHasSwatchAttribute();
            } else {
                $hasSwatch = $this->isProductHasSwatchAttribute;
            }
            if ($hasSwatch) {
                return self::PRODUCT_WHOLESALE_RENDERER_SWATCHES_TEMPLATE;
            } else {
                return self::PRODUCT_WHOLESALE_RENDERER_TEMPLATE;
            }
        } else {
            return parent::getRendererTemplate();
        }
    }

    public function getJsonConfigTable()
    {
        $currentProduct = $this->getProduct();
        $options = $this->helper->getOptions($currentProduct, $this->getAllowProducts());
        $tableData = $this->configurableAttributeData->getTableOrdering($currentProduct, $options);
        return $tableData;
    }

    public function getJsonChildInfo()
    {
        $code = $this->getJsonConfigTable();
        $childData = $this->getConfigChildProductIds($code['code']);
        return ['jsonChildInfo' => $this->jsonEncoder->encode($childData['childrenList']), 'tierPriceRange' => $childData['tierPriceRange'], "isLoggedIn" => $childData['isLoggedIn'], "customerGroupArray" => $childData['customerGroupArray']];
    }

    public function getConfigChildProductIds($code = null)
    {
        $product = $this->getProduct();
        if (!isset($product)) {
            return;
        }
        $storeId = $this->storeManager->getStore()->getId();
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter($storeId, $product);
        $usedProducts = $productTypeInstance->getUsedProducts($product);
        $childrenList = [];
        $tierPriceRange = [];
        $tierPriceRangeNotLogged = [];
        $tierPriceRangeGeneral = [];
        $options = $this->helper->getOptions($product, $this->getAllowProducts());
        $attributesDatas = $this->configurableAttributeData->getAttributesData($product, $options);
        $useTier_price = true;
        $customerGroupNotLogged = "not_login";
        $customerId = $this->httpContext->getValue(ModulePlugin::CONTEXT_CUSTOMER_ID);
        $customGroupId = $this->httpContext->getValue(ModulePlugin::CONTEXT_GROUP_ID);
        $customerGroupArray = [];
        if ($customerId != 0) {
            $customerGroup = "login";
            $customerGroupArray['login'] = 2;
            $isLoggedIn = true;
            $isLoggedInWhosale = false;
            if($customGroupId == 2) {
                $customerWhoSaleGroup = "whosale";
                $isLoggedInWhosale = true;
                $customerGroupArray['whosale'] = 1;
        //$customerGroup          = "whosale";
            }
        } else {
            $isLoggedIn = false;
            $isLoggedInWhosale = false;
            $customerGroup = "not_login";
        }

        foreach ($usedProducts as $key => $child) {
            $isSaleable = $child->isSaleable();

            if ($isSaleable) {
                $childProductId = $child->getId();
                $productChild = $this->productRepository->getById($childProductId);
                $optionId = $productChild->getData($code);
                $websiteId = $productChild->getStore()->getWebsiteId();
                $stockItem = $this->stockRegistryProvider->getStockItem($childProductId, $websiteId);
                $stock = $stockItem->getIsInStock();
                $attr = $productChild->getResource()->getAttribute($code);
                if ($attr->usesSource()) {
                    $optionText = $attr->getSource()->getOptionText($optionId);
                }
                $optionCollection = $this->attrOptionCollectionFactory->create()->setAttributeFilter(
                    $attr->getId())->setPositionOrder('asc',true)->load();
                foreach($optionCollection as $option) {
                    if($option->getOptionId() == $optionId) {
                        $sortOrder = $option->getSortOrder();
                    }
                }
                foreach ($attributesDatas['attributes'] as $attributesData) {
                    $codeAttr = $attributesData['code'];
                    $idAttr = $attributesData['id'];

                    $codeProduct = $productChild->getData($codeAttr);
                    if (isset($codeProduct)) {
                        $childrenList[$key][$childProductId]['option']['data-option-' . $idAttr] = $codeProduct;
                    }
                }

                if ($stock) {
                    if (!$this->helperBss->getConfig('stock_number')) {
                        $status = __('In stock');
                    } else {
                        $status = $this->stockState->getStockQty($childProductId, $websiteId);
                    }
                } else {
                    $status = __('Out of stock');
                }

                $childrenList[$key][$childProductId]['attribute'] = $optionText;
                if ($productChild->getData('discountcode')) {
                    $childrenList[$key][$childProductId]['other']['discountcode'] = $productChild->getData('discountcode');
                } else {
                    $childrenList[$key][$childProductId]['other']['discountcode'] = '';
                }
                if ($this->helperBss->getDisplayAttribute('sku')) {
                    $childrenList[$key][$childProductId]['sku'] = $productChild->getSku();
                }
                if ($this->helperBss->getDisplayAttribute('availability')) {
                    $childrenList[$key][$childProductId]['qty_stock'] = $status;
                }
                if ($this->helperBss->getDisplayAttribute('tier_price') && $useTier_price) {
                    $finalPrice = $productChild->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
                    $tierPriceModel = $this->getStoredTierPrices($child);
                    $tierPricesList = $this->filterTierPrices($tierPriceModel);
                    $tierPricesListNotLogged = $this->filterTierPricesNotLogged($tierPriceModel);
                    $tierPricesListGeneral = [];
                    $minTierPrice = [];
                    $tierPriceRangeDefault = [];
                    if($isLoggedInWhosale) {
                        $tierPricesListGeneral = $this->filterTierPricesGeneral($tierPriceModel);
                    }

                    if (isset($tierPricesList) && !empty($tierPricesList)) {
                        if(empty($tierPriceRange)) {
                            foreach ($tierPricesList as $index => $price) {
                                $tierPriceRangeDefault[] = (int)$price['price_qty'];
                            }
                            if (isset($tierPricesListNotLogged) && !empty($tierPricesListNotLogged)) {
                                if(empty($tierPriceRangeNotLogged)) {
                                    foreach ($tierPricesListNotLogged as $index => $price) {
                                        $tierPriceRangeNotLogged[] = (int)$price['price_qty'];
                                    }
                                    $tierPriceRangeDefault = array_unique(array_merge($tierPriceRangeDefault,$tierPriceRangeNotLogged), SORT_REGULAR);
                                    sort($tierPriceRangeDefault);
                                }

                                if(empty($tierPriceRangeGeneral) && $isLoggedInWhosale) {
                                    foreach ($tierPricesListGeneral as $index => $price) {
                                        $tierPriceRangeGeneral[] = (int)$price['price_qty'];
                                    }
                                    $tierPriceRangeDefault = array_unique(array_merge($tierPriceRangeDefault,$tierPriceRangeGeneral), SORT_REGULAR);
                                    sort($tierPriceRangeDefault);
                                }

                                foreach($tierPriceRangeDefault as $tier) {
                                    $tierPriceRange[] = $tier;
                                }
                            }
                        }

                        foreach ($tierPricesList as $index => $price) {
                            if(!empty($tierPriceRange)) {
                                if(count($tierPriceRange) == 1) {
                                    $minTierPrice[$tierPriceRange[0]][$customerGroup] = $price['website_price'];
                                }else {
                                    for($i=1; $i<count($tierPriceRange); $i++) {
                                        if($price['price_qty'] >= $tierPriceRange[$i-1] && $price['price_qty'] < $tierPriceRange[$i]) {
                                            $minTierPrice[$tierPriceRange[$i-1]][$customerGroup] = $price['website_price'];
                                            break;
                                        }
                                        if($price['price_qty'] <= $tierPriceRange[$i] && $price['price_qty'] > $tierPriceRange[$i-1]) {
                                            $minTierPrice[$tierPriceRange[$i]][$customerGroup] = $price['website_price'];
                                            break;
                                        }
                                        if($i == count($tierPriceRange)-1 && $price['price_qty'] >= $tierPriceRange[$i]) {
                                            $minTierPrice[$tierPriceRange[$i]][$customerGroup] = $price['website_price'];
                                            break;
                                        }
                                    }
                                }
                            } else {
                                $minTierPrice[$price['price_qty']][$customerGroup] = (int)$price['website_price'];
                            }
                        }
                        foreach ($tierPricesListNotLogged as $index => $price) {
                            if(!empty($tierPriceRange)) {
                                if(count($tierPriceRange) == 1) {
                                    $minTierPrice[$tierPriceRange[0]][$customerGroupNotLogged] = $price['website_price'];
                                }else {
                                    for($i=1; $i<count($tierPriceRange); $i++) {
                                        if($price['price_qty'] >= $tierPriceRange[$i-1] && $price['price_qty'] < $tierPriceRange[$i]) {
                                            $minTierPrice[$tierPriceRange[$i-1]][$customerGroupNotLogged] = $price['website_price'];
                                            break;
                                        }
                                        if($price['price_qty'] <= $tierPriceRange[$i] && $price['price_qty'] > $tierPriceRange[$i-1]) {
                                            $minTierPrice[$tierPriceRange[$i]][$customerGroupNotLogged] = $price['website_price'];
                                            break;
                                        }
                                        if($i == count($tierPriceRange)-1 && $price['price_qty'] >= $tierPriceRange[$i]) {
                                            $minTierPrice[$tierPriceRange[$i]][$customerGroupNotLogged] = $price['website_price'];
                                            break;
                                        }
                                    }
                                }
                            } else {
                                $minTierPrice[$price['price_qty']][$customerGroupNotLogged] = (int)$price['website_price'];
                            }
                        }
                        if($isLoggedInWhosale) {
                            foreach ($tierPricesListGeneral as $index => $price) {
                                if(!empty($tierPriceRange)) {
                                    if(count($tierPriceRange) == 1) {
                                        $minTierPrice[$tierPriceRange[0]][$customerWhoSaleGroup] = $price['website_price'];
                                    }else {
                                        for($i=1; $i<count($tierPriceRange); $i++) {
                                            if($price['price_qty'] >= $tierPriceRange[$i-1] && $price['price_qty'] < $tierPriceRange[$i]) {
                                                $minTierPrice[$tierPriceRange[$i-1]][$customerWhoSaleGroup] = $price['website_price'];
                                                break;
                                            }
                                            if($price['price_qty'] <= $tierPriceRange[$i] && $price['price_qty'] > $tierPriceRange[$i-1]) {
                                                $minTierPrice[$tierPriceRange[$i]][$customerWhoSaleGroup] = $price['website_price'];
                                                break;
                                            }
                                            if($i == count($tierPriceRange)-1 && $price['price_qty'] >= $tierPriceRange[$i]) {
                                                $minTierPrice[$tierPriceRange[$i]][$customerWhoSaleGroup] = $price['website_price'];
                                                break;
                                            }
                                        }
                                    }
                                } else {
                                    $minTierPrice[$price['price_qty']][$customerWhoSaleGroup] = (int)$price['website_price'];
                                }
                            }
                        }
                    } else {
                        if(empty($tierPriceRange)) {
                            $useTier_price = false;
                        } else {
                            foreach($tierPriceRange as $tier) {
                                $minTierPrice[$tier][$customerGroupNotLogged] = $finalPrice;
                                $minTierPrice[$tier][$customerGroup] = $finalPrice;
                            }
                        }
                    }

                    if ($minTierPrice != '' && !$this->helperBss->checkCustomer('hide_price')) {
                        $childrenList[$key][$childProductId]['tier_price'] = $minTierPrice;
                    }
                }

                $stockItem = $this->stockRegistry->getStockItem($childProductId, $productChild->getStore()->getWebsiteId());
                $minSaleQty = (float) $stockItem->getMinSaleQty();
                $maxSaleQty = (float) $stockItem->getMaxSaleQty();
                if (isset($minSaleQty) && $minSaleQty > 0) {
                    $childrenList[$key][$childProductId]['other']['min_qty'] = $minSaleQty;
                }
                if (isset($maxSaleQty) && $maxSaleQty > 0) {
                    $childrenList[$key][$childProductId]['other']['max_qty'] = $maxSaleQty;
                }
                $childrenList[$key][$childProductId]['status_stock'] = $stock;
                $childrenList[$key][$childProductId]['attribute_code'] = $code;
                $childrenList[$key][$childProductId]['option_id'] = $optionId;
                $childrenList[$key][$childProductId]['sort_order'] = $sortOrder;
            }
        }
        
        return ["childrenList" => $childrenList, "tierPriceRange" => $tierPriceRange, "isLoggedIn" => $isLoggedIn, "customerGroupArray" => $customerGroupArray];
    }


    public function getChildName($code = null)
    {
        $product = $this->getProduct();
        if (!isset($product)) {
            return;
        }
        $storeId = $this->storeManager->getStore()->getId();
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter($storeId, $product);
        $usedProducts = $productTypeInstance->getUsedProducts($product);
        $childrenListName = [];
        $number_attribute = count($productTypeInstance->getConfigurableAttributes($product));
        foreach ($usedProducts as $key => $child) {
            $isSaleable = $child->isSaleable();
            if ($isSaleable) {
                $childProductId = $child->getId();
                $productChild = $this->productRepository->getById($childProductId);
                if ($number_attribute == 1) {
                    $childrenListName[$childProductId] = $productChild->getName();
                }
            }
        }
        $childrenListName['mainproduct'] = $product->getName();
        return $this->jsonEncoder->encode($childrenListName);
    }

    public function getJsonConfigTableOrdering()
    {
        $store = $this->getCurrentStore();
        $currentProduct = $this->getProduct();

        $regularPrice = $currentProduct->getPriceInfo()->getPrice('regular_price');
        $finalPrice = $currentProduct->getPriceInfo()->getPrice('final_price');

        $options = $this->helper->getOptions($currentProduct, $this->getAllowProducts());

        //fix 2.2
        if ($this->helperBss->getMagentoVersion('2.2.0')) {
            foreach ($allowProducts as $product) {
                $productId = $product->getId();
                $tableImages = $this->helper->getGalleryImages($product);
                if ($tableImages) {
                    foreach ($tableImages as $image) {
                        $options['images'][$productId][] =
                            [
                                'thumb' => $image->getData('small_image_url'),
                                'img' => $image->getData('medium_image_url'),
                                'full' => $image->getData('large_image_url'),
                                'caption' => $image->getLabel(),
                                'position' => $image->getPosition(),
                                'isMain' => $image->getFile() == $product->getImage(),
                            ];
                    }
                }
            }
        }

        $attributesData = $this->configurableAttributeData->getAttributesDataTableOrdering($currentProduct, $options);

        $config = [
            'attributes' => $attributesData['attributes'],
            'template' => str_replace('%s', '<%- data.price %>', $store->getCurrentCurrency()->getOutputFormat()),
            'optionPrices' => $this->getOptionPrices(),
            'prices' => [
                'oldPrice' => [
                    'amount' => $this->_registerJsPrice($regularPrice->getAmount()->getValue()),
                ],
                'basePrice' => [
                    'amount' => $this->_registerJsPrice(
                        $finalPrice->getAmount()->getBaseAmount()
                    ),
                ],
                'finalPrice' => [
                    'amount' => $this->_registerJsPrice($finalPrice->getAmount()->getValue()),
                ],
            ],
            'productId' => $currentProduct->getId(),
            'chooseText' => __('Choose an Option...'),
            'images' => isset($options['images']) ? $options['images'] : [],
            'index' => isset($options['index']) ? $options['index'] : [],
        ];

        if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
            $config['defaultValues'] = $attributesData['defaultValues'];
        }

        $config = array_merge($config, $this->_getAdditionalConfig());

        return $this->jsonEncoder->encode($config);
    }

    public function getPriceTableOrdering()
    {
        if ($this->helperBss->checkCustomer('hide_price')) {
            return false;
        }
        $storeId = $this->storeManager->getStore()->getId();
        $product = $this->getProduct();
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter($storeId, $product);
        $usedProducts = $productTypeInstance->getUsedProducts($product);
        $childrenList = [];
        foreach ($usedProducts as $child) {
            $attributes = [];
            $tierPrices = [];
            $isSaleable = $child->isSaleable();
            if ($isSaleable) {
                $tierPrices[1] = $child->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
                $tierPricesList = $child->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
                if (isset($tierPricesList) && !empty($tierPricesList)) {
                    foreach ($tierPricesList as $price) {
                        $tierPrices[$price['price_qty']] = $this->priceCurrency->convert($price['price']->getValue());
                    }
                }
                if (isset($tierPrices) && !empty($tierPrices)) {
                    $childrenList[$child->getId()] = $tierPrices;
                }
            }
        }
        return $this->jsonEncoder->encode($childrenList);
    }

    public function getStoredTierPrices($child)
    {
        $rawPriceList = $child->getData('tier_price');
        if (null === $rawPriceList || !is_array($rawPriceList)) {
            /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
            $attribute = $child->getResource()->getAttribute('tier_price');
            if ($attribute) {
                $attribute->getBackend()->afterLoad($child);
                $rawPriceList = $child->getData('tier_price');
            }
        }
        if (null === $this->rawPriceList || !is_array($this->rawPriceList)) {
            $this->rawPriceList = [];
        }
        if (!$this->isPercentageDiscount()) {
            foreach ($rawPriceList as $index => $rawPrice) {
                if (isset($rawPrice['price'])) {
                    $rawPriceList[$index]['price'] =
                        $this->priceCurrency->convertAndRound($rawPrice['price']);
                }
                if (isset($rawPrice['website_price'])) {
                    $rawPriceList[$index]['website_price'] =
                        $this->priceCurrency->convertAndRound($rawPrice['website_price']);
                }
            }
        }
        return $rawPriceList;
    }

    /**
     * @return bool
     */
    public function isPercentageDiscount()
    {
        return false;
    }

    /**
     * @param array $priceList
     * @return array
     */
    public function filterTierPrices(array $priceList)
    {
        $qtyCache = [];
        if ($this->customerSession->isLoggedIn()) {
            $customerGroup = $this->customerSession->getCustomer()->getGroupId();
        } else {
            $customerGroup = $this->groupManagement->getNotLoggedInGroup()->getId();
        }
        $allCustomersGroupId = $this->groupManagement->getAllCustomersGroup()->getId();
        foreach ($priceList as $priceKey => &$price) {
            /* filter price by customer group */
            if (isset($customerGroup)) {
                if ($price['cust_group'] != $customerGroup && $price['cust_group'] != $allCustomersGroupId) {
                    unset($priceList[$priceKey]);
                }
            }
        }
        return array_values($priceList);
    }

    public function filterTierPricesGeneral(array $priceList)
    {
        $customerGroup = 1;
        $allCustomersGroupId = $this->groupManagement->getAllCustomersGroup()->getId();
        foreach ($priceList as $priceKey => &$price) {

            if (isset($price['price_qty']) && $price['price_qty'] == 1) {
                unset($priceList[$priceKey]);
                continue;
            }
            /* filter price by customer group */
            if (isset($customerGroup)) {
                if ($price['cust_group'] != $customerGroup && $price['cust_group'] != $allCustomersGroupId) {
                    unset($priceList[$priceKey]);
                }
            }
        }
        return array_values($priceList);
    }

    public function filterTierPricesNotLogged(array $priceList)
    {
        $customerGroup = $this->groupManagement->getNotLoggedInGroup()->getId();
        $allCustomersGroupId = $this->groupManagement->getAllCustomersGroup()->getId();
        foreach ($priceList as $priceKey => &$price) {

            if (isset($price['price_qty']) && $price['price_qty'] == 1) {
                unset($priceList[$priceKey]);
                continue;
            }
            /* filter price by customer group */
            if (isset($customerGroup)) {
                if ($price['cust_group'] != $customerGroup && $price['cust_group'] != $allCustomersGroupId) {
                    unset($priceList[$priceKey]);
                }
            }
        }
        return array_values($priceList);
    }

    protected function isFirstPriceBetter($firstPrice, $secondPrice)
    {
        return $firstPrice < $secondPrice;
    }
}
