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

use Magento\Framework\App\Action;
use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Controller\Index\Cart as WishlistCart;
use Bss\ConfigurableProductWholesale\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\Item\OptionFactory;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\LocaleQuantityProcessor;
use Magento\Wishlist\Model\ItemFactory;
use Magento\Checkout\Model\Cart as CartModel;
use Magento\Catalog\Helper\Product;
use Magento\Framework\Escaper;
use Magento\Wishlist\Helper\Data as WishlistData;
use Magento\Checkout\Helper\Cart as CheckoutCart;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Catalog\Model\ProductFactory;

/**
 * Class Cart
 *
 * @package Bss\ConfigurableProductWholesale\Controller\Cart
 */
class Cart extends WishlistCart
{
    /**
     * @var Data
     */
    private $helperBss;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var OptionFactory
     */
    private $optionFactory;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @param Action\Context $context
     * @param WishlistProviderInterface $wishlistProvider
     * @param LocaleQuantityProcessor $quantityProcessor
     * @param ItemFactory $itemFactory
     * @param CartModel $cart
     * @param OptionFactory $optionFactory
     * @param Product $productHelper
     * @param Escaper $escaper
     * @param WishlistData $helper
     * @param CheckoutCart $cartHelper
     * @param Validator $formKeyValidator
     * @param StoreManagerInterface $storeManager
     * @param Data $helperBss
     * @param ProductFactory $productFactory
     */
    public function __construct(
        Action\Context $context,
        WishlistProviderInterface $wishlistProvider,
        LocaleQuantityProcessor $quantityProcessor,
        ItemFactory $itemFactory,
        CartModel $cart,
        OptionFactory $optionFactory,
        Product $productHelper,
        Escaper $escaper,
        WishlistData $helper,
        CheckoutCart $cartHelper,
        Validator $formKeyValidator,
        StoreManagerInterface $storeManager,
        Data $helperBss,
        ProductFactory $productFactory
    ) {
        parent::__construct(
            $context,
            $wishlistProvider,
            $quantityProcessor,
            $itemFactory,
            $cart,
            $optionFactory,
            $productHelper,
            $escaper,
            $helper,
            $cartHelper,
            $formKeyValidator
        );
        $this->optionFactory = $optionFactory;
        $this->storeManager = $storeManager;
        $this->helperBss = $helperBss;
        $this->productFactory = $productFactory;
    }

    /**
     * Add wishlist item to shopping cart and remove from wishlist
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/');
        }

        $params = $this->getRequest()->getParams();
        if (!$this->helperBss->getConfig() || !isset($params['bss-table-ordering'])) {
            return parent::execute();
        }

        $itemId = (int)$this->getRequest()->getParam('item');
        $item = $this->itemFactory->create()->load($itemId);

        $wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
        if (!$wishlist || !$item->getId()) {
            $resultRedirect->setPath('*/*');
            return $resultRedirect;
        }
        $redirectUrl = $this->_url->getUrl('*/*');
        $configureUrl = $this->_url->getUrl(
            '*/*/configure/',
            [
                'id' => $item->getId(),
                'product_id' => $item->getProductId(),
            ]
        );

        try {
            $optionsCollection = $this->optionFactory->create()->getCollection();
            $options = $optionsCollection->addItemFilter([$itemId]);
            $item->setOptions($options->getOptionsByItem($itemId));
            // add to cart function
            $count = $this->_addMultipleProduct($params, $item);
            $this->cart->save()->getQuote()->collectTotals();
            $wishlist->save();
            $redirectUrl = $this->_getRedirectUrl($count, $configureUrl, $item);
        } catch (ProductException $e) {
            $this->messageManager->addErrorMessage(
                __('This product(s) is out of stock.')
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addNoticeMessage($e->getMessage());
            $redirectUrl = $configureUrl;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add the item to the cart right now.')
            );
        }

        $this->helper->calculate();

        if ($this->getRequest()->isAjax()) {
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData(['backUrl' => $redirectUrl]);
            return $resultJson;
        }

        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }

    /**
     * Add all product to cart
     *
     * @param array $params
     * @param mixed $item
     * @return int
     */
    private function _addMultipleProduct($params, $item = null)
    {
        $count = 0;
        if (!empty($params['bss-qty']) && $item) {
            foreach ($params['bss-qty'] as $row => $qty) {
                if ($qty <= 0) {
                    continue;
                }
                $paramsTableOrdering = [];
                $product = $this->_getProduct($params['product']);
                if ($count == 0) {
                    $paramFistItem = [];
                    $qty = $this->quantityProcessor->process($qty);
                    if ($qty) {
                        $item->setQty($qty);
                    }
                    $paramFistItem['qty'] = $qty;
                    $paramFistItem['super_attribute'] = $params['bss_super_attribute'][$row];
                    if (isset($params['options'])) {
                        $paramFistItem['options'] = $params['options'];
                    }
                    $buyRequest = $this->productHelper->addParamsToBuyRequest(
                        $paramFistItem,
                        ['current_config' => $item->getBuyRequest()]
                    );

                    $item->mergeBuyRequest($buyRequest);
                    $item->addToCart($this->cart, true);
                } else {
                    $paramsTableOrdering['qty'] = $qty;
                    $paramsTableOrdering['super_attribute'] = $params['bss_super_attribute'][$row];
                    if (isset($params['options'])) {
                        $paramsTableOrdering['options'] = $params['options'];
                    }
                    $paramsTableOrdering['selected_configurable_option'] = $params['selected_configurable_option'];
                    $this->cart->addProduct($product, $paramsTableOrdering);
                }
                $count++;
            }
        }
        return $count;
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
     * @param int $count
     * @param string $configureUrl
     * @param mixed $item
     * @return mixed
     */
    private function _getRedirectUrl($count, $configureUrl, $item = null)
    {
        if (!$item) {
            return false;
        }
        if (!$this->cart->getQuote()->getHasError()) {
            if ($count == 0) {
                $this->messageManager->addErrorMessage(
                    __('No items add to your shopping cart.')
                );
            } else {
                $message = __(
                    'You added %1 to your shopping cart.',
                    $this->escaper->escapeHtml($item->getProduct()->getName())
                );
                $this->messageManager->addSuccessMessage($message);
            }
        }

        if ($this->cartHelper->getShouldRedirectToCart()) {
            $redirectUrl = $this->cartHelper->getCartUrl();
        } else {
            $refererUrl = $this->_redirect->getRefererUrl();
            if ($refererUrl && $refererUrl != $configureUrl) {
                $redirectUrl = $refererUrl;
            }
        }
        return $redirectUrl;
    }
}
