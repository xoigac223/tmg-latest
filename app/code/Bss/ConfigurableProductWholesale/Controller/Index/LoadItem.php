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

namespace Bss\ConfigurableProductWholesale\Controller\Index;

use Magento\Framework\App\Action;
use Magento\Framework\Json\EncoderInterface;

/**
 * Class LoadItem
 *
 * @package Bss\ConfigurableProductWholesale\Controller\Index
 */
class LoadItem extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Bss\ConfigurableProductWholesale\Helper\Data
     */
    private $helperBss;

    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    private $checkoutSession;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @param Action\Context $context
     * @param \Magento\Checkout\Model\SessionFactory $checkoutSession
     * @param EncoderInterface $jsonEncoder
     * @param \Bss\ConfigurableProductWholesale\Helper\Data $helperBss
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     */
    public function __construct(
        Action\Context $context,
        \Magento\Checkout\Model\SessionFactory $checkoutSession,
        EncoderInterface $jsonEncoder,
        \Bss\ConfigurableProductWholesale\Helper\Data $helperBss,
        \Magento\Customer\Model\SessionFactory $customerSession
    ) {
        parent::__construct($context);
        $this->helperBss = $helperBss;
        $this->checkoutSession = $checkoutSession;
        $this->jsonEncoder = $jsonEncoder;
        $this->customerSession = $customerSession;
    }

    /**
     * Get qty item in cart when edit product
     *
     * @return mixed
     */
    public function execute()
    {
        $itemId = $this->getRequest()->getParam('item_id');
        $productId = $this->getRequest()->getParam('product');
        if (!$this->helperBss->getConfig() || !isset($itemId) || !isset($productId)) {
            return false;
        }
        $quote = $this->checkoutSession->create()->getQuote();
        $itemCurrent = $quote->getItemById($itemId);
        if (!isset($itemCurrent)) {
            return false;
        }
        $optionsCurrent = $this->_getOptionProduct($itemCurrent);
        $customOptionsCurrent = [];
        if (isset($optionsCurrent['options'])) {
            $customOptionsCurrent = $optionsCurrent['options'];
        }
        $productApply = [];
        $childApply = [];
        $respon = [];
        $items = $quote->getAllItems();
        foreach ($items as $item) {
            if ($item->getProduct()->getId() == $productId) {
                $options = $this->_getOptionProduct($item);
                $apply = true;
                if (isset($options['options']) && !empty($options['options'])) {
                    $apply = $this->_checkItem($options, $customOptionsCurrent);
                }
                if ($apply) {
                    $productApply[$item->getId()] = $item->getQty();
                }
            } else {
                $parentItem = $item->getParentItem();
                if (isset($parentItem) && $parentItem->getProduct()->getId() == $productId) {
                    $childApply[$parentItem->getId()] = $item->getProduct()->getId();
                }
            }
        }
        foreach ($productApply as $id => $qty) {
            $productId = $childApply[$id];
            $respon['product'][$productId] = $qty;
            $respon['item'][$productId] = $id;
        }
        $respon['default'] = $childApply[$itemId];
        return $this->getResponse()->setBody(
            $this->jsonEncoder->encode($respon)
        );
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return mixed
     */
    private function _getOptionProduct($item)
    {
        if ($item) {
            $product = $item->getProduct();
            return $product->getTypeInstance()->getOrderOptions($product);
        }
        return false;
    }

    /**
     * @param array $options
     * @param array $customOptionsCurrent
     * @return bool
     */
    private function _checkItem($options, $customOptionsCurrent)
    {
        foreach ($options['options'] as $key => $option) {
            $result = array_diff($option, $customOptionsCurrent[$key]);
            if (isset($result) && !empty($result)) {
                return false;
            }
        }
        return true;
    }
}
