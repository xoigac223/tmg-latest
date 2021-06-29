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
 * @package    Bss_ConflictInfiniteScrollAndAmastyShopby
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConflictInfiniteScrollAndAmastyShopby\Override\Block\Navigation;

use Magento\Framework\View\Element\Template;

/**
 * @api
 */
class JsInit extends \Amasty\Shopby\Block\Navigation\JsInit
{
    protected $_template = 'Bss_ConflictInfiniteScrollAndAmastyShopby::jsinit.phtml';
}
