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

namespace Bss\ConfigurableProductWholesale\Helper;

/**
 * Class Data
 *
 * @package Bss\ConfigurableProductWholesale\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIGURABLE_PRODUCT_TYPE = 'configurable';
    const DEFAULT_FINAL_PRICE_TEMPLATE = 'Magento_Catalog::product/price/final_price.phtml';
    const CUSTOM_FINAL_PRICE_TEMPLATE = 'Bss_ConfigurableProductWholesale::product/price/final_price.phtml';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Directory\Model\PriceCurrency
     */
    private $currency;

    /**
     * @var \Magento\Framework\Locale\Currency
     */
    private $currencyLocale;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    private $taxHelper;

    /**
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Directory\Model\PriceCurrency $currency
     * @param \Magento\Framework\Locale\Currency $currencyLocale
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Directory\Model\PriceCurrency $currency,
        \Magento\Framework\Locale\Currency $currencyLocale,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->localeFormat = $localeFormat;
        $this->jsonEncoder = $jsonEncoder;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->priceHelper = $priceHelper;
        $this->productMetadata = $productMetadata;
        $this->currency = $currency;
        $this->currencyLocale = $currencyLocale;
        $this->taxHelper = $taxHelper;
    }

    /**
     * Get Configuration by Field
     *
     * @param string $field
     * @return mixed
     */
    public function getConfig($field = 'active')
    {
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $active = $this->scopeConfig->getValue(
            'configurableproductwholesale/general/active',
            $scope
        );
        if (!$active || !$this->checkCustomer('active_customer_groups')) {
            return false;
        }
        $result = $this->scopeConfig->getValue(
            'configurableproductwholesale/general/'.$field,
            $scope
        );
        if ($result) {
            return $result;
        }
        return false;
    }

    /**
     * Check attribute display
     *
     * @param string|null $value
     * @return bool
     */
    public function getDisplayAttribute($value = null)
    {
        if (!$this->getConfig()) {
            return false;
        }
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $result = $this->scopeConfig->getValue(
            'configurableproductwholesale/general/show_attr',
            $scope
        );
        $resultArr = explode(',', $result);
        return in_array($value, $resultArr);
    }

    /**
     * @return string
     */
    public function getFomatPrice()
    {
        $config = $this->localeFormat->getPriceFormat();
        return $this->jsonEncoder->encode($config);
    }

    /**
     * @param string|null $field
     * @return bool
     */
    public function checkCustomer($field = null)
    {
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $customerConfig = $this->scopeConfig->getValue(
            'configurableproductwholesale/general/'.$field,
            $scope
        );
        if ($customerConfig != '') {
            $customerConfigArr = explode(',', $customerConfig);
            if ($this->customerSession->isLoggedIn()) {
                $customerGroupId = $this->customerSession->getCustomerGroupId();
                if (in_array($customerGroupId, $customerConfigArr)) {
                    return true;
                }
            } else {
                if (in_array(0, $customerConfigArr)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param \Magento\Catalog\Model\Product|null $product
     * @return bool
     */
    public function checkTierPrice($product = null)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter($storeId, $product);
        $usedProducts = $productTypeInstance->getUsedProducts($product);
        $check = [];
        $count = 0;
        $apply = true;
        foreach ($usedProducts as $child) {
            $tierPriceModel = $child->getPriceInfo()->getPrice('tier_price');
            $tierPricesList = $tierPriceModel->getTierPriceList();
            if (isset($tierPricesList) && !empty($tierPricesList)) {
                $countPricesList = $this->_coutTierPrice($tierPricesList);
                foreach ($tierPricesList as $index => $price) {
                    if ($count == 0) {
                        $countList = $countPricesList;
                        $check[$price['price_qty']] = $price['website_price'];
                    } else {
                        $websitePrice = $price['website_price'];
                        if ($check && isset($check[$price['price_qty']]) &&
                            $check[$price['price_qty']] != $websitePrice ||
                            $countPricesList != $countList
                        ) {
                            $apply = false;
                            break;
                        }
                    }
                }
            } else {
                $apply = false;
                break;
            }
            $count++;
        }
        return $apply;
    }

    /**
     * @param array $tierPricesList
     * @return int
     */
    private function _coutTierPrice($tierPricesList)
    {
        return count($tierPricesList);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return mixed
     */
    public function setPriceForItem($item)
    {
        if (!isset($item)) {
            return;
        }
        $product = $item->getProduct();
        if (!$this->checkTierPrice($product)) {
            return;
        }
        $qty = $this->_getTotalQty($item);
        foreach ($item->getQuote()->getAllVisibleItems() as $quoteItem) {
            $productId = $quoteItem->getProduct()->getId();
            $productType = $quoteItem->getProduct()->getTypeId();

            if ($productType != 'configurable' || $product->getId() != $productId) {
                continue;
            }

            $finalPrice = $quoteItem->getProduct()->getFinalPrice($qty);
            $quoteItem->setCustomPrice($finalPrice);
            $quoteItem->setOriginalCustomPrice($finalPrice);
            $quoteItem->getProduct()->setIsSuperMode(true);
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return mixed
     */
    public function _getTotalQty($item)
    {
        $totalsQty = 0;
        if (!isset($item)) {
            return false;
        }
        $product = $item->getProduct();
        foreach ($item->getQuote()->getAllVisibleItems() as $quoteItem) {
            $productId = $quoteItem->getProduct()->getId();
            $productType = $quoteItem->getProduct()->getTypeId();
            if ($productType != 'configurable' || $product->getId() != $productId) {
                continue;
            }
            $totalsQty += $quoteItem->getQty();
        }
        if ($totalsQty > 0) {
            return $totalsQty;
        } else {
            return false;
        }
    }

    /**
     *  Get price template
     *
     * @return string
     */
    public function getPriceTemplate()
    {
        $product = $this->registry->registry('current_product');
        if (isset($product) && $this->getConfig('range_price') &&
            $product->getTypeId() == self::CONFIGURABLE_PRODUCT_TYPE
        ) {
            return self::CUSTOM_FINAL_PRICE_TEMPLATE;
        }
        return self::DEFAULT_FINAL_PRICE_TEMPLATE;
    }

    /**
     * @param float|null $price
     * @return string
     */
    public function getFormatPrice($price = null)
    {
        $currencyCode = $this->currency->getCurrency()->getCurrencyCode();
        return $this->currencyLocale->getCurrency($currencyCode)->toCurrency($price);
    }

    /**
     * @param \Magento\Catalog\Model\Product|null $product
     * @param float|null $min
     * @param float|null $max
     * @return array|bool
     */
    public function getRangePrice($product = null, $min = null, $max = null)
    {
        $usedProducts = $product->getTypeInstance()->getUsedProducts($product);
        $price = [];
        $result = [];
        foreach ($usedProducts as $productChild) {
            $priceModel = $productChild->getPriceInfo()->getPrice('final_price');
            $price['finalPrice'][] = $priceModel->getAmount()->getValue();
            $price['exclTaxFinalPrice'][] = $priceModel->getAmount()->getValue(['tax']);
            $tierPriceModel = $productChild->getPriceInfo()->getPrice('tier_price');
            $tierPricesList = $tierPriceModel->getTierPriceList();
            if (isset($tierPricesList) && !empty($tierPricesList)) {
                foreach ($tierPricesList as $tierPrices) {
                    $price['finalPrice'][] = $tierPrices['price']->getValue();
                    $price['exclTaxFinalPrice'][] = $tierPrices['price']->getValue(['tax']);
                }
            }
        }

        $result['finalPrice'] = array_unique($price['finalPrice']);
        $result['exclTaxFinalPrice'] = array_unique($price['exclTaxFinalPrice']);
        $maxFinalPrice = max($result['finalPrice']);
        $maxExclTaxFinalPrice = max($result['exclTaxFinalPrice']);
        $minFinalPrice = min($result['finalPrice']);
        $minExclTaxFinalPrice = min($result['exclTaxFinalPrice']);
        if (isset($max)) {
            return [
                'finalPrice' => $maxFinalPrice,
                'exclTaxFinalPrice' => $maxExclTaxFinalPrice
            ];
        } elseif (isset($min)) {
            return [
                'finalPrice' => $minFinalPrice,
                'exclTaxFinalPrice' => $minExclTaxFinalPrice
            ];
        } else {
            return false;
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product|null $product
     * @return string|bool
     */
    public function getJsonSystemConfig($product = null)
    {
        if (!$product) {
            return false;
        }
        $showSubTotal = false;
        if ($this->getDisplayAttribute('subtotal') && !$this->checkCustomer('hide_price')) {
            $showSubTotal = true;
        }
        $showExclTaxSubTotal = false;
        if ($this->getExclTaxConfig() && !$this->checkCustomer('hide_price')) {
            $showExclTaxSubTotal = true;
        }
        $tierPriceAdvanced = false;
        if ($this->getConfig('tier_price_advanced') && $this->checkTierPrice($product)) {
            $tierPriceAdvanced = true;
        }
        $config = [
            'tierPriceAdvanced' => $tierPriceAdvanced,
            'showSubTotal' => $showSubTotal,
            'showExclTaxSubTotal' => $showExclTaxSubTotal,
            'textColor' => $this->getConfig('header_text_color'),
            'backGround' => $this->getConfig('header_background_color'),
        ];
        if ($this->getConfig('mobile_active')) {
            $config['mobile'] = $this->getDisplayAttributeAdvanced('mobile_attr', 'mobile_active');
        }
        if ($this->getConfig('tab_active')) {
            $config['tablet'] = $this->getDisplayAttributeAdvanced('tab_attr', 'tab_active');
        }
        return $this->jsonEncoder->encode($config);
    }

    /**
     * @param string $field
     * @param string|null $active
     * @return array|bool
     */
    public function getDisplayAttributeAdvanced($field, $active = null)
    {
        if (!$this->getConfig($active)) {
            return false;
        }
        $respon = [];
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $result = $this->scopeConfig->getValue(
            'configurableproductwholesale/general/'.$field,
            $scope
        );
        $resultArr = explode(',', $result);
        foreach ($resultArr as $value) {
            $respon[$value] = $value;
        }
        return $respon;
    }

    /**
     *  Add class for mobile and tablet
     *
     * @param string $value
     * @return string
     */
    public function getClassAdvanced($value)
    {
        $html = '';
        $mobileArr = $this->getDisplayAttributeAdvanced('mobile_attr', 'mobile_active');
        $tabletArr = $this->getDisplayAttributeAdvanced('tab_attr', 'tab_active');
        if (!empty($mobileArr) || !empty($tabletArr)) {
            $html .= 'class="';
            if (is_array($mobileArr) && !in_array($value, $mobileArr)) {
                $html .= 'bss-hidden-480';
            }
            if (is_array($tabletArr) && !in_array($value, $tabletArr)) {
                $html .= ' bss-hidden-1024';
            }
            $html .= '"';
        }
        return $html;
    }

    /**
     *  Compare magento version
     *
     * @param string $version
     * @return bool
     */
    public function getMagentoVersion($version)
    {
        $dataVersion = $this->productMetadata->getVersion();
        if (version_compare($dataVersion, $version) >= 0) {
            return true;
        }
        return false;
    }

    /**
     * Check config exclude tax price
     *
     * @return bool
     */
    public function getExclTaxConfig()
    {
        if ($this->getConfig() && $this->taxHelper->displayBothPrices() &&
            $this->getDisplayAttribute('excl_tax_price')
        ) {
            return true;
        }
        return false;
    }
}
