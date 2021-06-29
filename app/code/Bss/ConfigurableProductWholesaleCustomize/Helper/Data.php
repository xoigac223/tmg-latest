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

namespace Bss\ConfigurableProductWholesaleCustomize\Helper;

use Bss\ConfigurableProductWholesaleCustomize\Plugin\Context as ModulePlugin;

/**
 * Class Data
 *
 * @package Bss\ConfigurableProductWholesale\Helper
 */
class Data extends \Bss\ConfigurableProductWholesale\Helper\Data
{
    const CONFIGURABLEPRODUCTWHOLESALE_CUSTOMIZE_TEXT_SPECIAL_PRICE = 'configurableproductwholesale/customize/text_special_price';

    protected $httpContext;

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
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        parent::__construct(
            $localeFormat,
            $jsonEncoder,
            $registry,
            $customerSession,
            $priceHelper,
            $storeManager,
            $productMetadata,
            $currency,
            $currencyLocale,
            $taxHelper,
            $context
        );
        $this->httpContext = $httpContext;
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
            $productChild->setCustomerGroupId(0);
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

    public function getCustomerGroupId() {
        if($this->httpContext->getValue(ModulePlugin::CONTEXT_CUSTOMER_ID))
            return $this->httpContext->getValue(ModulePlugin::CONTEXT_GROUP_ID);
        return 0;
    }

    /**
     *
     * @return string|null
     */
    public function getMessageSpecialPrice()
    {
        return $this->scopeConfig->getValue(
            self::CONFIGURABLEPRODUCTWHOLESALE_CUSTOMIZE_TEXT_SPECIAL_PRICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
