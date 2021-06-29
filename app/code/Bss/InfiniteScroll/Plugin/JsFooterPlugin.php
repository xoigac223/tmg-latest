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
namespace Bss\InfiniteScroll\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class JsFooterPlugin
 * @package Bss\InfiniteScroll\Plugin
 */
class JsFooterPlugin
{
    const XML_PATH_DEV_MOVE_JS_TO_BOTTOM = 'dev/js/move_script_to_bottom';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * JsFooterPlugin constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Theme\Controller\Result\JsFooterPlugin $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\App\Response\Http $http
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBeforeSendResponse(
        \Magento\Theme\Controller\Result\JsFooterPlugin $subject,
        \Closure $proceed,
        \Magento\Framework\App\Response\Http $http
    ) {
        $content = $http->getContent();
        if (strpos($content, '</body') !== false) {
            if ($this->scopeConfig->isSetFlag(
                self::XML_PATH_DEV_MOVE_JS_TO_BOTTOM,
                ScopeInterface::SCOPE_STORE
            )
            ) {
                $pattern = '#<script[^>]*+(?<!text/x-magento-template.)>.*?</script>#is';
                $content = preg_replace_callback(
                    $pattern,
                    function ($matchPart) use (&$script) {
                        if (strpos($matchPart[0], 'noDeferJs') !== false) {
                            return $matchPart[0];
                        } else {
                            $script[] = $matchPart[0];
                            return '';
                        }
                    },
                    $content
                );
                $http->setContent(
                    str_replace('</body', implode("\n", $script) . "\n</body", $content)
                );
            }
        }
    }
}