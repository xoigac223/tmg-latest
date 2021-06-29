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
 * @category  BSS
 * @package   Bss_ConfigurableProductWholesale
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ConfigurableProductWholesale\Block\Product\Renderer;

use Magento\Swatches\Block\Product\Renderer\Configurable;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Bss\ConfigurableProductWholesale\Helper\Data as WholesaleData;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;

/**
 * Class ConfigurableProductWholesale
 *
 * @package Bss\ConfigurableProductWholesale\Block\Product\Renderer
 */
class ConfigurableProductWholesale extends Configurable
{
    const WHOLESALE_SWATCHES_TEMPLATE = 'product/view/renderer.phtml';

    const WHOLESALE_TEMPLATE = 'product/view/configurable.phtml';

    /**
     * @var WholesaleData
     */
    private $helperBss;

    /**
     * @var /Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockStateInterface
     */
    private $stockState;

    /**
     * @var StockRegistryProviderInterface
     */
    private $stockRegistryProvider;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var /Magento\CatalogInventory\Api\StockRegistryInterface
     */
    public $stockRegistry;

    /**
     * @var CollectionFactory
     */
    private $attrOptionCollectionFactory;

    /**
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param Data $helper
     * @param CatalogProduct $catalogProduct
     * @param CurrentCustomer $currentCustomer
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param SwatchData $swatchHelper
     * @param Media $swatchMediaHelper
     * @param StockStateInterface $stockState
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param ProductRepository $productRepository
     * @param WholesaleData $helperBss
     * @param CollectionFactory $attrOptionCollectionFactory
     * @param array $data
     */
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
        StockStateInterface $stockState,
        StockRegistryProviderInterface $stockRegistryProvider,
        ProductRepository $productRepository,
        WholesaleData $helperBss,
        CollectionFactory $attrOptionCollectionFactory,
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
            $data
        );
        $this->helperBss = $helperBss;
        $this->storeManager = $context->getStoreManager();
        $this->stockState = $stockState;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->stockRegistry = $context->getStockRegistry();
        $this->productRepository = $productRepository;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
    }

    /**
     * Return renderer template wholesale
     *
     * @return string
     */
    public function getRendererTemplate()
    {
        if ($this->helperBss->getConfig()) {
            if ($this->helperBss->getMagentoVersion('2.1.6')) {
                $hasSwatch = $this->isProductHasSwatchAttribute();
            } else {
                $hasSwatch = $this->isProductHasSwatchAttribute;
            }
            if ($hasSwatch) {
                return self::WHOLESALE_SWATCHES_TEMPLATE;
            } else {
                return self::WHOLESALE_TEMPLATE;
            }
        } else {
            return parent::getRendererTemplate();
        }
    }

    /**
     * @return array
     */
    public function getJsonConfigTable()
    {
        $currentProduct = $this->getProduct();
        $options = $this->helper->getOptions($currentProduct, $this->getAllowProducts());
        $tableData = $this->configurableAttributeData->getTableOrdering($currentProduct, $options);
        return $tableData;
    }

    /**
     * @return string
     */
    public function getJsonChildInfo()
    {
        $code = $this->getJsonConfigTable();
        $childData = $this->getConfigChildProductIds($code['code']);
        return $this->jsonEncoder->encode($childData);
    }

    /**
     * Get Child Product Information by Attribute Code
     *
     * @param string|null $code
     * @return mixed
     */
    public function getConfigChildProductIds($code = null)
    {
        $product = $this->getProduct();
        if (!isset($product)) {
            return fasle;
        }
        $storeId = $this->storeManager->getStore()->getId();
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter($storeId, $product);
        $usedProducts = $productTypeInstance->getUsedProducts($product);
        $childrenList = [];
        $options = $this->helper->getOptions($product, $this->getAllowProducts());
        $attributesDatas = $this->configurableAttributeData->getAttributesData($product, $options);
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
                $optionText = '';
                if ($attr->usesSource()) {
                    $optionText = $attr->getSource()->getOptionText($optionId);
                }

                $sortOrder = $this->_sortOptions($attr, $optionId);
                if (!empty($dataOptions = $this->_pushOptions($attributesDatas, $productChild))) {
                    $childrenList[$key]['option'] = $dataOptions;
                }

                // get tier price
                if ($this->helperBss->getDisplayAttribute('tier_price')) {
                    $childrenList[$key]['tier_price'] = $this->_pushTierPrice($child);
                }

                $status = $this->getStatus($stock, $childProductId, $websiteId);
                $childrenList[$key]['attribute'] = $optionText;

                if ($this->helperBss->getDisplayAttribute('sku')) {
                    $childrenList[$key]['sku'] = $productChild->getSku();
                }

                if ($this->helperBss->getDisplayAttribute('availability')) {
                    $childrenList[$key]['qty_stock'] = $status;
                }

                if (!empty($priceData = $this->_pushPrice($productChild))) {
                    $childrenList[$key]['price'] = $priceData;
                }

                $websiteId = $productChild->getStore()->getWebsiteId();
                $stockItem = $this->stockRegistry->getStockItem($childProductId, $websiteId);
                $childrenList[$key]['other'] = $this->_pushMinMaxQty($stockItem, $childrenList);
                $childrenList[$key]['other']['product_id'] = $childProductId;
                $childrenList[$key]['status_stock'] = $stock;
                $childrenList[$key]['attribute_code'] = $code;
                $childrenList[$key]['attribute_id'] = $attr->getId();
                $childrenList[$key]['option_id'] = $optionId;
                $childrenList[$key]['sort_order'] = $sortOrder;
            }
        }

        uasort($childrenList, function ($a, $b) {
            return $a['sort_order'] - $b['sort_order'];
        });

        $childrenList = array_values($childrenList);
        return $childrenList;
    }

    /**
     * Get Product Information
     *
     * @return string
     */
    public function getJsonConfigTableOrdering()
    {
        $store = $this->getCurrentStore();
        $currentProduct = $this->getProduct();

        $regularPrice = $currentProduct->getPriceInfo()->getPrice('regular_price');
        $finalPrice = $currentProduct->getPriceInfo()->getPrice('final_price');
        $allowProducts = $this->getAllowProducts();
        $options = $this->helper->getOptions($currentProduct, $allowProducts);

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

    /**
     * Get TierPrice Information
     *
     * @return mixed
     */
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
            $tierPrices = [];
            $tierPricesExclTax = [];
            $isSaleable = $child->isSaleable();
            if ($isSaleable) {
                $priceAmount = $child->getPriceInfo()->getPrice('final_price')->getAmount();
                $tierPrices[1] = [
                    'finalPrice' => $priceAmount->getValue(),
                    'exclTaxFinalPrice' => $priceAmount->getValue(['tax'])
                ];
                $tierPricesList = $child->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
                if (isset($tierPricesList) && !empty($tierPricesList)) {
                    foreach ($tierPricesList as $price) {
                        $tierPrices[$price['price_qty']] = [
                            'finalPrice' => $price['price']->getValue(),
                            'exclTaxFinalPrice' => $price['price']->getValue(['tax'])
                        ];
                    }
                }
                if (isset($tierPrices) && !empty($tierPrices)) {
                    $childrenList['finalPrice'][$child->getId()] = $tierPrices;
                    $childrenList['exclTaxFinalPrice'][$child->getId()] = $tierPricesExclTax;
                }
            }
        }
        return $this->jsonEncoder->encode($childrenList);
    }

    /**
     * @param bool $stock
     * @param int $childProductId
     * @param int $websiteId
     * @return string|int
     */
    private function getStatus($stock, $childProductId, $websiteId)
    {
        if ($stock) {
            if (!$this->helperBss->getConfig('stock_number')) {
                $status = __('In stock');
            } else {
                $status = $this->stockState->getStockQty($childProductId, $websiteId);
            }
        } else {
            $status = __('Out of stock');
        }
        return $status;
    }

    /**
     * @param \Magento\Catalog\Model\Product $child
     * @return string
     */
    private function _pushTierPrice($child)
    {
        $tierPriceModel = $child->getPriceInfo()->getPrice('tier_price');
        $tierPricesList = $tierPriceModel->getTierPriceList();
        $detailedPrice = '';
        $tierPriceHtml = '';
        if (isset($tierPricesList) && !empty($tierPricesList)) {
            foreach ($tierPricesList as $index => $price) {
                $detailedPrice .= '<li class="item">';
                $detailedPrice .= __(
                    'Buy %1 for %2 each and <strong class="benefit">save<span class="percent tier-%3">&nbsp;%4</span>%</strong></li>',
                    $price['price_qty'],
                    $this->helperBss->getFormatPrice($price['price']->getValue()),
                    $index,
                    $tierPriceModel->getSavePercent($price['price'])
                );
                $detailedPrice .= '</li>';
            }
        }
        if ($detailedPrice != '' && !$this->helperBss->checkCustomer('hide_price')) {
            $tierPriceHtml = '<ul class="prices-tier items">' . $detailedPrice . '</ul>';
        }
        return $tierPriceHtml;
    }

    /**
     * @param \Magento\Catalog\Model\ProductRepository $productChild
     * @return array
     */
    private function _pushPrice($productChild)
    {
        $priceData = [];
        if ($this->helperBss->getDisplayAttribute('unit_price') &&
            !$this->helperBss->checkCustomer('hide_price')
        ) {
            $finalPriceAmount = $productChild->getPriceInfo()->getPrice('final_price')->getAmount();
            $regularPriceAmount = $productChild->getPriceInfo()->getPrice('regular_price')->getAmount();
            $finalPrice = $finalPriceAmount->getValue();
            $price = $regularPriceAmount->getValue();
            $exclTaxFinalPrice = $finalPriceAmount->getValue(['tax']);
            $exclTaxPrice = $regularPriceAmount->getValue(['tax']);
            $priceData['final_price'] = $finalPrice;
            $priceData['excl_tax_final_price'] = $exclTaxFinalPrice;
            if ($price != $finalPrice) {
                $priceData['old_price'] = $price;
                $priceData['excl_tax_old_price'] = $exclTaxPrice;
            }
        }
        return $priceData;
    }

    /**
     * @param mixed $attr
     * @param int $optionId
     * @return int
     */
    private function _sortOptions($attr, $optionId)
    {
        $sortOrder = '';
        $optionCollection = $this->attrOptionCollectionFactory->create();
        $option = $optionCollection->setAttributeFilter(
            $attr->getId()
        )->setPositionOrder(
            'asc',
            true
        )->addFieldToFilter(
            'main_table.option_id',
            ['eq' => $optionId]
        );
        $optionData = $option->getData();
        if (!empty($optionData) && !empty($optionData[0])) {
            $sortOrder = $optionData[0]['sort_order'];
        }
        return $sortOrder;
    }

    /**
     * @param array $attributesDatas
     * @param \Magento\Catalog\Model\ProductRepository $productChild
     * @return array
     */
    private function _pushOptions($attributesDatas, $productChild)
    {
        $dataOptions = [];
        if (isset($attributesDatas) && !empty($attributesDatas['attributes'])) {
            foreach ($attributesDatas['attributes'] as $attributesData) {
                $codeAttr = $attributesData['code'];
                $idAttr = $attributesData['id'];

                $codeProduct = $productChild->getData($codeAttr);
                if (isset($codeProduct)) {
                    $dataOptions['data-option-' . $idAttr] = $codeProduct;
                }
            }
        }
        return $dataOptions;
    }

    /**
     * @param mixed $stockItem
     * @param array $childrenList
     * @return array
     */
    private function _pushMinMaxQty($stockItem, $childrenList)
    {
        $data = [];
        $minSaleQty = (float) $stockItem->getMinSaleQty();
        $maxSaleQty = (float) $stockItem->getMaxSaleQty();
        if (isset($minSaleQty) && $minSaleQty > 0) {
            $data['min_qty'] = $minSaleQty;
        }
        if (isset($maxSaleQty) && $maxSaleQty > 0) {
            $data['max_qty'] = $maxSaleQty;
        }
        return $data;
    }
}
