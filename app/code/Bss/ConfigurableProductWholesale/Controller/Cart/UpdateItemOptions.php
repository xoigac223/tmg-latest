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

use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Catalog\Model\ProductFactory;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\Escaper;
use Magento\Framework\Filter\LocalizedToNormalized;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\DataObjectFactory;

/**
 * Class UpdateItemOptions
 *
 * @package Bss\ConfigurableProductWholesale\Controller\Cart
 */
class UpdateItemOptions extends \Magento\Checkout\Controller\Cart\UpdateItemOptions
{
    /**
     * @var \Bss\ConfigurableProductWholesale\Helper\Data
     */
    private $helperBss;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Cart
     */
    private $cartHelper;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var LocalizedToNormalized
     */
    private $localFilter;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param \Bss\ConfigurableProductWholesale\Helper\Data $helperBss
     * @param ProductFactory $productFactory
     * @param LoggerInterface $logger
     * @param Cart $cartHelper
     * @param Escaper $escaper
     * @param LocalizedToNormalized $localFilter
     * @param ResolverInterface $localeResolver
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        \Bss\ConfigurableProductWholesale\Helper\Data $helperBss,
        ProductFactory $productFactory,
        LoggerInterface $logger,
        Cart $cartHelper,
        Escaper $escaper,
        LocalizedToNormalized $localFilter,
        ResolverInterface $localeResolver,
        DataObjectFactory $dataObjectFactory
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->storeManager = $storeManager;
        $this->helperBss = $helperBss;
        $this->productFactory = $productFactory;
        $this->logger = $logger;
        $this->cartHelper = $cartHelper;
        $this->escaper = $escaper;
        $this->localFilter = $localFilter;
        $this->localeResolver = $localeResolver;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Update product configuration for a cart item
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute($coreRoute = null)
    {
        $params = $this->getRequest()->getParams();
        if (!$this->helperBss->getConfig() || !$params['bss-table-ordering']) {
            return parent::execute($coreRoute);
        }

        try {
            $related = $this->getRequest()->getParam('related_product');
            $this->_addMultipleProduct($params);

            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();
            if (isset($item)) {
                $this->_eventManager->dispatch(
                    'checkout_cart_update_item_complete',
                    [
                        'item' => $item,
                        'request' => $this->getRequest(),
                        'response' => $this->getResponse()
                    ]
                );
                if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                    if (!$this->cart->getQuote()->getHasError()) {
                        $message = __(
                            '%1 was updated in your shopping cart.',
                            $this->escaper->escapeHtml(
                                $item->getProduct()->getName()
                            )
                        );
                        $this->messageManager->addSuccessMessage($message);
                    }
                    return $this->_goBack($this->_url->getUrl('checkout/cart'));
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_catchException($e);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t update the item right now.')
            );
            $this->logger->critical($e);
            return $this->_goBack();
        }
        return $this->resultRedirectFactory->create()->setPath('*/*');
    }

    /**
     * Add all product to cart
     *
     * @param array $params
     * @return mixed
     */
    private function _addMultipleProduct($params)
    {
        if (!empty($params['bss-qty'])) {
            foreach ($params['bss-qty'] as $productId => $qty) {
                if ($qty < 0) {
                    continue;
                }
                if (isset($qty) && $qty != 0) {
                    $this->localFilter->setOptions(
                        ['locale' => $this->localeResolver->getLocale()]
                    );
                    $qty = $this->localFilter->filter((double)$qty);
                }
                $paramsTableOrdering = [];
                $product = $this->_getProduct($params['product']);
                $paramsTableOrdering['qty'] = $qty;
                $paramsTableOrdering['super_attribute'] = $params['bss_super_attribute'][$productId];
                if (isset($params['options'])) {
                    $paramsTableOrdering['options'] = $params['options'];
                } else {
                    $paramsTableOrdering['options'] = [];
                }
                $paramsTableOrdering['selected_configurable_option'] = $params['selected_configurable_option'];

                if (isset($params['bss-item'][$productId])) {
                    $id = (int)$params['bss-item'][$productId];
                    $quoteItem = $this->cart->getQuote()->getItemById($id);
                    if (!$quoteItem) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('We can\'t find the quote item.')
                        );
                    }
                    $paramsTableOrdering['id'] = $id;
                    $paramsTableOrdering['product'] = $quoteItem->getProductId();
                    $this->_updateItems($qty, $id, $paramsTableOrdering);
                } else {
                    if ($qty <= 0) {
                        continue;
                    }
                    try {
                        $this->cart->addProduct($product, $paramsTableOrdering);
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage(__($e->getMessage()));
                        $this->_goBack();
                        return;
                    }
                }
            }
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
     * @param mixed $e
     * @return string
     */
    private function _catchException($e)
    {
        if ($this->_checkoutSession->getUseNotice(true)) {
            $this->messageManager->addNoticeMessage($e->getMessage());
        } else {
            $messages = array_unique(explode("\n", $e->getMessage()));
            foreach ($messages as $message) {
                $this->messageManager->addErrorMessage($message);
            }
        }

        $url = $this->_checkoutSession->getRedirectUrl(true);
        if ($url) {
            return $this->resultRedirectFactory->create()->setUrl($url);
        } else {
            $cartUrl = $this->cartHelper->getCartUrl();
            return $this->resultRedirectFactory->create()->setUrl(
                $this->_redirect->getRedirectUrl($cartUrl)
            );
        }
    }

    /**
     * @param float $qty
     * @param int $id
     * @param array $paramsTableOrdering
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _updateItems($qty, $id, $paramsTableOrdering)
    {
        if ($qty == 0) {
            $this->cart->removeItem($id);
        } else {
            try {
                $data = $this->dataObjectFactory->create();
                $item = $this->cart->updateItem(
                    $id,
                    $data->setData($paramsTableOrdering)
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __($e->getMessage())
                );
                $this->_goBack();
                return;
            }

            if (is_string($item)) {
                throw new \Magento\Framework\Exception\LocalizedException(__($item));
            }
            if ($item->getHasError()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($item->getMessage())
                );
            }
        }
    }
}
