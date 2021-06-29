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
 * @category   BSS
 * @package    Bss_InfiniteScroll
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\InfiniteScroll\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Json\Encoder;

/**
 * Class InfiniteScroll
 *
 * @package Bss\InfiniteScroll\Block
 */
class InfiniteScroll extends Template
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Bss\InfiniteScroll\Helper\Data
     */
    private $helper;

    /**
     * @var Encoder
     */
    private $jsonEncoder;

    /**
     * InfiniteScroll constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Bss\InfiniteScroll\Helper\Data $helper
     * @param Encoder $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Bss\InfiniteScroll\Helper\Data $helper,
        Encoder $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $context->getScopeConfig();
        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Get stored media file Url
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Get config by path, check for path at module's system.xml
     *
     * @param string $config_path
     * @return mixed
     */
    public function getConfig($config_path = '')
    {
        return $this->scopeConfig
            ->getValue('infinitescroll/settings/' . $config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get loading Icon
     *
     * @return bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadingIcon()
    {
        $loading = $this->getConfig('loading_icon');
        if ($loading) {
            return $this->getMediaUrl() .'infinitescroll/'. $loading;
        }
        return false;
    }

    /**
     * Get config by path, check for path at module's system.xml
     *
     * @param string $config_path
     * @return bool|mixed
     */
    public function getConfigGototop($config_path = '')
    {
        if ($this->scopeConfig
            ->getValue('infinitescroll/gototop/enabled_gototop', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return $this->scopeConfig
                ->getValue('infinitescroll/gototop/' . $config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return false;
    }

    /**
     * Get config by path, check for path at module's system.xml
     *
     * @param string $config_path
     * @return mixed
     */
    public function getConfigButton($config_path = '')
    {
        return $this->scopeConfig
            ->getValue('infinitescroll/btn_loadmore/' . $config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get config module and parse to JSON
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getJsonConfig()
    {
        $data = [
            'button' => [
                'background_btn_loadmore' => $this->getConfigButton('background_btn_loadmore'),
                'color_btn_loadmore' => $this->getConfigButton("color_btn_loadmore"),
                'text_btn_loadmore' => $this->getConfigButton("text_btn_loadmore"),
                'text_btn_prev' => $this->getConfigButton("text_btn_prev"),
                'text_end_load' => $this->getConfigButton('text_end_load')
            ],
            'general' => [
                'use_previous' => (bool) $this->getConfig('use_previous'),
                'triggerpage_threshold' => $this->getConfig("triggerpage_threshold"),
                'loadingIcon' => $this->loadingIcon(),
                'loading_icon_text' => $this->getConfig('loading_icon_text')
            ],
            'gototop' => [
                'enabled_gototop' => (bool) $this->getConfigGototop('enabled_gototop'),
                'goup_speed' => $this->getConfigGototop('goup_speed'),
                'location' => $this->getConfigGototop('location'),
                'location_offset' => $this->getConfigGototop('location_offset'),
                'bottom_offset' => $this->getConfigGototop('bottom_offset'),
                'container_size' => $this->getConfigGototop('container_size'),
                'container_radius' => $this->getConfigGototop('container_radius'),
                'always_visible' => $this->getConfigGototop('always_visible'),
                'trigger' => $this->getConfigGototop('trigger'),
                'hide_under_width' => $this->getConfigGototop('hide_under_width'),
                'container_color' => $this->getConfigGototop('container_color'),
                'arrow_color' => $this->getConfigGototop('arrow_color'),
                'text_hover' => $this->getConfigGototop('text_hover'),
                'zindex' => $this->getConfigGototop('zindex')
            ]
        ];
        return $this->jsonEncoder->encode($data);
    }

    /**
     * Get helper function
     *
     * @return \Bss\InfiniteScroll\Helper\Data
     */
    public function getBssHelper()
    {
        return $this->helper;
    }
}
