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

namespace Bss\InfiniteScroll\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Bss\InfiniteScroll\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->moduleManager = $context->getModuleManager();
        $this->registry = $registry;
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * Check if there is Bss_LazyLoad module enable
     *
     * @return bool
     */
    public function checkBssLazy()
    {
        return $this->moduleManager->isEnabled('Bss_LazyImageLoader');
    }

    /**
     * Get all category at mark as exclude form Infinite Scroll
     *
     * @return array|bool
     */
    public function getExcludeCategory()
    {
        $category = $this->scopeConfig
            ->getValue('infinitescroll/settings/exclude_category', ScopeInterface::SCOPE_STORE);
        if ($category) {
            return explode(',', $category);
        }
        return false;
    }

    /**
     * Check total active condition
     *
     * @return bool
     */
    public function checkActive()
    {
        $categoryExclude = $this->getExcludeCategory();
        $category = $this->registry->registry('current_category');
        $action = $this->request->getFullActionName();
        $active = $this->scopeConfig
            ->getValue('infinitescroll/settings/active', ScopeInterface::SCOPE_STORE);
        if (!$active) {
            return false;
        }

        if ($this->isCategoryEnable($categoryExclude, $category, $action)) {
            return true;
        }

        if ($this->isSearchPageEnable($action)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the current category meet the condition to run Infinite Scroll
     *
     * @param array $categoryExclude
     * @param \Magento\Catalog\Model\Category $category
     * @param string $action
     * @return bool
     */
    public function isCategoryEnable($categoryExclude, $category, $action)
    {
        if ($category && $action == 'catalog_category_view') {
            $categoryIdParent = $category->getParentCategory()->getId();
            $categoryId = $category->getId();
            if (($categoryExclude && !in_array($categoryIdParent, $categoryExclude)
                    && !in_array($categoryId, $categoryExclude)) || !$categoryExclude) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if enable Infinite Scroll in Search Page
     *
     * @param string $action
     * @return bool
     */
    public function isSearchPageEnable($action)
    {
        if (($action == 'catalogsearch_advanced_result' || $action == 'catalogsearch_result_index')
            && $this->enabledSearchPage()) {
            return true;
        }
        return false;
    }

    /**
     * Check if enable module's lazy load integrated function
     *
     * @return mixed
     */
    public function enabledLazy()
    {
        return $this->scopeConfig
            ->getValue('infinitescroll/settings/active_lazy', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get config enable search page
     *
     * @return mixed
     */
    private function enabledSearchPage()
    {
        return $this->scopeConfig
            ->getValue('infinitescroll/settings/active_search_page', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isNoDeferJs()
    {
        if ($this->scopeConfig->isSetFlag('infinitescroll/settings/active', ScopeInterface::SCOPE_STORE)
            && $this->scopeConfig->isSetFlag('dev/js/move_script_to_bottom', ScopeInterface::SCOPE_STORE)
            && $this->checkActive()
        ) {
            $page = $this->request->getParam('p');
            if ($page && (int)$page > 1) {
                return true;
            }
        }
        return false;
    }
}
