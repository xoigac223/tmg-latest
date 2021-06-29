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

namespace Bss\InfiniteScroll\Block\Product\ProductList;

use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;

/**
 * Class Toolbar
 *
 * @package Bss\InfiniteScroll\Block\Product\ProductList
 */
class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
    /**
     * @var null
     */
    protected $defaultSize = null;

    /**
     * Toolbar constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param ToolbarModel $toolbarModel
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param ProductList $productListHelper
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\Config $catalogConfig,
        ToolbarModel $toolbarModel,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        ProductList $productListHelper,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $catalogSession,
            $catalogConfig,
            $toolbarModel,
            $urlEncoder,
            $productListHelper,
            $postDataHelper,
            $data
        );
    }

    /**
     * Set default collection size
     *
     * @return $this
     */
    public function setDefaultCollectionSize()
    {
        $collection = $this->getCollection();
        if ($this->defaultSize == null) {
            $this->defaultSize = $collection->getSize();
        }
        return $this;
    }

    /**
     * Get default collection size
     *
     * @return mixed
     */
    public function getDefaultSize()
    {
        $this->setDefaultCollectionSize();
        return $this->defaultSize;
    }
}
