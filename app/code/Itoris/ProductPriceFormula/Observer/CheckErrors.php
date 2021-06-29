<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCT_PRICE_FORMULA
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\ProductPriceFormula\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\InputException;

class CheckErrors implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $quoteItem = $observer->getEvent()->getItem();
        $price = $this->_objectManager->get('Itoris\ProductPriceFormula\Helper\Price')->getProductFinalPrice($quoteItem);
        if ($quoteItem->getPriceFormulaError()) {
            throw new InputException($block->escapeHtml(__($quoteItem->getPriceFormulaError())));
            //Mage::throwException($quoteItem->getPriceFormulaError());
        }
    }
}