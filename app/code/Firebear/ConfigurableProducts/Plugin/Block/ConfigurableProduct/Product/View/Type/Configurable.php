<?php
/**
 * Copyright © 2017 Firebear Studio. All rights reserved.
 */

namespace Firebear\ConfigurableProducts\Plugin\Block\ConfigurableProduct\Product\View\Type;

use Firebear\ConfigurableProducts\Model\Product\Defaults;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use \Magento\Framework\Json\EncoderInterface;
use \Magento\Framework\Json\DecoderInterface;
use Magento\Swatches\Helper\Data as SwatchesHelper;

class Configurable
{
    /**
     * Core registry.
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Defaults
     */
    protected $productDefaults;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var SwatchesHelper
     */
    protected $swatchesHelper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var []
     */
    protected $settings;

    /**
     * @var \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable
     */
    protected $subject;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @param Registry                                        $coreRegistry
     * @param EncoderInterface                                $jsonEncoder
     * @param DecoderInterface                                $jsonDecoder
     * @param ScopeConfigInterface                            $scopeConfig
     * @param Defaults                                        $productDefaults
     * @param Manager                                         $moduleManager
     * @param ProductRepositoryInterface $productRepository
     * @param SwatchesHelper                                  $swatchesHelper
     * @param RequestInterface         $request
     */
    public function __construct(
        Registry $coreRegistry,
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder,
        ScopeConfigInterface $scopeConfig,
        Defaults $productDefaults,
        Manager $moduleManager,
        ProductRepositoryInterface $productRepository,
        SwatchesHelper $swatchesHelper,
        RequestInterface $request
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->scopeConfig = $scopeConfig;
        $this->productDefaults = $productDefaults;
        $this->moduleManager = $moduleManager;
        $this->productRepository = $productRepository;
        $this->swatchesHelper = $swatchesHelper;
        $this->request = $request;
    }

    /**
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param $result
     *
     * @return string
     */
    public function afterGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        $result
    ) {
        $this->subject = $subject;
        $this->layout = $this->subject->getLayout();
        $data = $this->coreRegistry->registry('firebear_configurableproducts');

        if (isset($data['child_id'])) {
            $productId = $data['child_id'];
        } else {
            $productId = $this->subject->getProduct()->getId();
        }
        $config = $this->jsonDecoder->decode($result);

        /**
         * Prepare default values for configurable product
         */
        $isProductHasSwatch = $this->swatchesHelper->isProductHasSwatch($this->subject->getProduct());

        $defaultValues = $this->prepareDefaultValues($config, $productId, $isProductHasSwatch);

        $usedProductId = $this->productDefaults->getDefaultProductId($this->subject->getProduct());
        
        if (empty($defaultValues) && $usedProductId) {
            $defaultValues = $this->prepareDefaultValues($config, $usedProductId, $isProductHasSwatch);
        }

        $config['defaultValues'] = $defaultValues;

        /**
         * Do not replace page content on category view page.
         */
        if ($this->request->getFullActionName() == 'catalog_category_view') {
            $config['doNotReplaceData'] = true;
        }

        /**
         * Prepare simple product attributes, such as name, sku, description
         */
        $config = $this->getOptions($config);
        $result = $this->jsonEncoder->encode($config);

        return $result;
    }

    /**
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param                                                                   $result
     *
     * @return mixed
     */
    public function afterGetCacheKeyInfo(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        $result
    ) {
        $jsonConfig = $subject->getJsonConfig();
        $config = $this->jsonDecoder->decode($jsonConfig);

        /**
         * Different cache for different simple products.
         */
        if (isset($config['defaultValues']) && !empty($config['defaultValues'])) {
            $result[] = http_build_query($config['defaultValues']);
        }

        /**
         * Prevent save same cache on category view page and product view page with same default values.
         */
        if ($this->request->getFullActionName() == 'catalog_category_view') {
            $result[] = 'doNotReplaceData';
        }

        return $result;
    }

    /**
     * Prepare default values.
     *
     * @param $config
     * @param $productId
     * @param $isSwatchEnabled
     *
     * @return array
     */
    protected function prepareDefaultValues($config, $productId, $isSwatchEnabled)
    {
        $defaultValues = [];
        foreach ($config['attributes'] as $attributeId => $attribute) {
            foreach ($attribute['options'] as $option) {
                $optionId = $option['id'];
                if (in_array($productId, $option['products'])) {
                    if ($isSwatchEnabled) {
                        $defaultValues[$attribute['code']] = $optionId;
                    } else {
                        $defaultValues[$attributeId] = $optionId;
                    }
                }
            }
        }
        return $defaultValues;
    }

    /**
     * Get extension settings.
     *
     * @return mixed
     */
    protected function getSettings()
    {
        if (!$this->settings) {
            $this->settings = $this->scopeConfig->getValue(
                'firebear_configurableproducts/general',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        return $this->settings;
    }

    /**
     * Get Options
     *
     * @param $config
     *
     * @return mixed
     */
    public function getOptions($config)
    {
        $settings = $this->getSettings();
        $allowedProducts = $this->subject->getAllowProducts();

        foreach ($allowedProducts as $allowedProduct) {
            $productId = $allowedProduct->getId();
            $product = $this->productRepository->getById($productId);
            
            /**
             * Render default product attributes.
             */
            $config['customAttributes'][$productId] = $this->renderAttributes($product);

            /**
             * Render tier prices templates for each simple product.
             */
            if (isset($settings['change_tier_prices']) && $settings['change_tier_prices'] == 1) {
                $config['customAttributes'][$productId]['tier_prices_html'] = $this->renderTierPrice($product);
            }

            /**
             * Render attributes block.
             */
            if (isset($settings['change_attributes_block']) && $settings['change_attributes_block'] == 1) {
                $config['customAttributes'][$productId]['attributes_html'] = $this->renderAttributesBlock($product);
            }

            /**
             * Render simple product urls.
             */
            $config['urls'][$productId] = $this->prepareUrls($product);

            /**
             * Render custom product attributes.
             */
            $config['customAttributes'][$productId]['custom_1'] = $this->renderCustomBlock($product);

            /**
             * Change breadcrumbs
             */
            $config['customAttributes'][$productId]['.breadcrumbs .items .product'] = [
                'value' => $product->getName(),
                'class' => '.breadcrumbs .items .product'
            ];
        }

        return $config;
    }

    /**
     * Render default product attributes.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @return array
     */
    protected function renderAttributes(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $attributesArray = ['name', 'description', 'short_description', 'sku'];
        $settings = $this->getSettings();
        $customAttributes = [];

        foreach ($attributesArray as $attributeCode) {
            if (isset($settings['change_' . $attributeCode]) && $settings['change_' . $attributeCode] == 1) {
                $customAttributes[$attributeCode] = [
                    'value' => $product->getData($attributeCode),
                    'class' => $settings[$attributeCode . '_id_class']
                ];
            }
        }

        return $customAttributes;
    }

    /**
     * Render tier prices templates for each simple product.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @return array
     */
    protected function renderTierPrice(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $settings = $this->getSettings();
        $priceRender = $this->layout->getBlock('product.price.render.default');
        $priceHtml = '';
        if (!$priceRender) {
            $priceRender = $this->layout->createBlock(
                'Magento\Framework\Pricing\Render',
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }
        if ($priceRender) {
            $priceHtml = $priceRender->render(
                \Magento\Catalog\Pricing\Price\TierPrice::PRICE_CODE,
                $product,
                ['zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST]
            );
        }
        //solve conflict with FireGento MageSetup
        if ($this->moduleManager->isEnabled('FireGento_MageSetup')) {
            preg_match('/<div class=\"price\-details\">(.*?)<\/div>/s', $priceHtml, $match);
            $priceDetailsBlock = $match[0];
            $priceHtml = str_replace($priceDetailsBlock, '', $priceHtml);
        }
        //
        $html = [
            'value'     => $priceHtml,
            'class'     => $settings['tier_prices_id_class'],
            'replace'   => true,
            'container' => '.prices-tier-container'
        ];

        return $html;
    }

    /**
     * Render attributes block.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @return array
     */
    protected function renderAttributesBlock(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $settings = $this->getSettings();
        $attributesBlock = $this->layout
            ->getBlock('firebear.product.attributes');

        if (!$attributesBlock) {
            if ($this->layout->getBlock('product.attributes')) {
                $attributesTemplate = $this->layout->getBlock('product.attributes')->getTemplate();
            } else {
                $attributesTemplate = 'product/view/attributes.phtml';
            }
            $attributesBlock = $this->layout
                ->createBlock(
                    '\Firebear\ConfigurableProducts\Block\Product\View\Attributes',
                    'firebear.product.attributes'
                )
                ->setTemplate('Magento_Catalog::' . $attributesTemplate);
        }

        $attributesBlock->setProduct($product);

        $html = $attributesBlock->toHtml();
        if ($html) {
            $attributesHtml = [
                'value'   => $html,
                'class'   => $settings['attributes_block_class'],
                'replace' => true,
            ];
        } else {
            $attributesBlock->setProduct($this->subject->getProduct());
            $html = $attributesBlock->toHtml();

            $attributesHtml = [
                'value'   => $html,
                'class'   => $settings['attributes_block_class'],
                'replace' => true,
            ];
        }

        return $attributesHtml;
    }

    /**
     * Render custom block.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @return array
     */
    protected function renderCustomBlock(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $settings = $this->getSettings();

        if (isset($settings['custom_block_1']) &&
            $settings['custom_block_1'] == 1 &&
            !empty($settings['custom_block_1_data']) &&
            !empty($settings['custom_block_1_id_class'])
        ) {
            $attr = $settings['custom_block_1_data'];
            $attr = str_replace(['{', '}'], '', $attr);

            return [
                'value' => $product->getData($attr),
                'class' => $settings['custom_block_1_id_class']
            ];
        }

        return [];
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @return string
     */
    protected function prepareUrls(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $settings = $this->getSettings();
        $url = '';

        if (isset($settings['change_url'])
            && $settings['change_url'] == 1
        ) {
            $url = $product->getProductUrl();
        }

        return $url;
    }
}
