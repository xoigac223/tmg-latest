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

namespace Bss\ConfigurableProductWholesale\Controller\Cart;

use Magento\Checkout\Controller\Cart\Add as CheckoutAdd;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Bss\ConfigurableProductWholesale\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Escaper;
use Magento\Checkout\Helper\Cart;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filter\LocalizedToNormalized;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class Add
 *
 * @package Bss\ConfigurableProductWholesale\Controller\Cart
 */
class Add extends CheckoutAdd
{
    /**
     * @var Data
     */
    private $helperBss;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var Cart
     */
    private $cartHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LocalizedToNormalized
     */
    private $localFilter;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param Data $helperBss
     * @param ProductFactory $productFactory
     * @param Escaper $escaper
     * @param Cart $cartHelper
     * @param LoggerInterface $logger
     * @param LocalizedToNormalized $localFilter
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        Data $helperBss,
        ProductFactory $productFactory,
        Escaper $escaper,
        Cart $cartHelper,
        LoggerInterface $logger,
        LocalizedToNormalized $localFilter,
        ResolverInterface $localeResolver
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $productRepository
        );
        $this->storeManager = $storeManager;
        $this->helperBss = $helperBss;
        $this->productFactory = $productFactory;
        $this->escaper = $escaper;
        $this->cartHelper = $cartHelper;
        $this->logger = $logger;
        $this->localFilter = $localFilter;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Add product to shopping cart action
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $params = $this->getRequest()->getParams();
        if (!$this->helperBss->getConfig() || !isset($params['bss-table-ordering'])) {
            return parent::execute();
        }

        try {
            $productDefault = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');
            /**
             * Check product availability
             */
            if (!$productDefault) {
                return $this->goBack();
            }
            $product = $this->_initProduct();
            $success = $this->_addMultipleProduct($params);
            if ($success) {
                if (!empty($related)) {
                    $this->cart->addProductsByIds(explode(',', $related));
                }

                $this->cart->save();
                $this->_eventManager->dispatch(
                    'checkout_cart_add_product_complete',
                    [
                        'product' => $product,
                        'request' => $this->getRequest(),
                        'response' => $this->getResponse()
                    ]
                );

                if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                    if (!$this->cart->getQuote()->getHasError()) {
                        $message = __(
                            'You added %1 to your shopping cart.',
                            $product->getName()
                        );
                        $this->messageManager->addSuccessMessage($message);
                    }
                    return $this->goBack(null, $product);
                }
            } else {
                $this->messageManager->addErrorMessage(
                    __('No items add to your shopping cart.')
                );
                return $this->goBack();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $url = $this->_catchException($e);
            return $this->goBack($url);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this item to your shopping cart right now.')
            );
            $this->logger->critical($e);
            return $this->goBack();
        }
    }

    /**
     * @param int|null $productId
     * @return mixed
     */
    private function _getProduct($productId = null)
    {
        if ($productId) {
            $storeId = $this->storeManager->getStore()->getId();
            $product = $this->productFactory->create()->setStoreId($storeId)->load($productId);
            return $product;
        }
        return false;
    }

    /**
     * Add all product to cart
     *
     * @param array $params
     * @return bool
     */
    private function _addMultipleProduct($params)
    {
        $success = false;
        if (!empty($params['bss-qty'])) {
            foreach ($params['bss-qty'] as $row => $qty) {
                if ($qty <= 0) {
                    continue;
                }
                $paramsTableOrdering = [];
                $product = $this->_getProduct($params['product']);
                if (isset($qty)) {
                    $this->localFilter->setOptions(['locale' => $this->localeResolver->getLocale()]);
                    $qty = $this->localFilter->filter((double)$qty);
                }
                $paramsTableOrdering['qty'] = $qty;
                $paramsTableOrdering['super_attribute'] = $params['bss_super_attribute'][$row];
                if (isset($params['options'])) {
                    $paramsTableOrdering['options'] = $params['options'];
                }
                $paramsTableOrdering['selected_configurable_option'] = $params['selected_configurable_option'];
                $this->cart->addProduct($product, $paramsTableOrdering);
                $success = true;
            }
        }
        return $success;
    }

    /**
     * @param mixed $e
     * @return string
     */
    private function _catchException($e)
    {
        if ($this->_checkoutSession->getUseNotice(true)) {
            $this->messageManager->addNoticeMessage(
                $this->escaper->escapeHtml($e->getMessage())
            );
        } else {
            $messages = array_unique(explode("\n", $e->getMessage()));
            foreach ($messages as $message) {
                $this->messageManager->addErrorMessage(
                    $this->escaper->escapeHtml($message)
                );
            }
        }

        $url = $this->_checkoutSession->getRedirectUrl(true);

        if (!$url) {
            $cartUrl = $this->cartHelper->getCartUrl();
            $url = $this->_redirect->getRedirectUrl($cartUrl);
        }
        return $url;
    }
}
