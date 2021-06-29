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

namespace Bss\InfiniteScroll\Plugin\Block\Product;

/**
 * Class Image
 */
class Image
{
    /**
     * @var \Bss\InfiniteScroll\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * Image constructor.
     * @param \Bss\InfiniteScroll\Helper\Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Bss\InfiniteScroll\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * Set module's template for block before apply default template
     *
     * @param \Magento\Catalog\Block\Product\Image $subject
     * @param string $template
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetTemplate(\Magento\Catalog\Block\Product\Image $subject, $template)
    {
        $controller = $this->request->getControllerName();
        $action     = $this->request->getActionName();
        $route      = $this->request->getRouteName();
        $path =  $route.'_'.$controller.'_'.$action;

        if (($path == 'catalog_category_view' ||
                $path == 'catalogsearch_advanced_result' ||
                $path == 'catalogsearch_result_index')
            && $this->helper->enabledLazy()) {
            $template = 'Bss_InfiniteScroll::product/image_with_borders.phtml';
        }
        return [$template];
    }
}
