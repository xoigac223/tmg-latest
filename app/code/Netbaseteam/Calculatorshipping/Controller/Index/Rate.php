<?php
namespace Netbaseteam\Calculatorshipping\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;
use Netbaseteam\Calculatorshipping\Helper\Data as CalculatorshippingData;
use TMG\PricingKey\Helper\PricingKey as PricingKeyHelper;
use TMG\Shipping\Model\Api\FreightEstimates;



class Rate extends \Magento\Checkout\Controller\Cart
{
    /**
     * @var estshippingcost Data
     */
    protected $_estshippingcostData;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    
    protected $_itemFactory;

    protected $_wishlistProvider;

    protected $_resultJsonFactory;

    protected $_shipModel;

    protected $_checkoutSession;

    protected $_apiShipping;

    protected $_priceHelper;


    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param FreightEstimates $apiShipping
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        FreightEstimates $apiShipping,
        CalculatorshippingData $calshippingData,
        \Magento\Framework\Registry $registry,
        ProductRepositoryInterface $productRepository,
        \Magento\Wishlist\Model\ItemFactory $itemFactory,
        \Magento\Shipping\Model\Shipping $shippingModel,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Framework\Controller\Result\JsonFactory  $resultJsonFactory,
        PricingKeyHelper $pricekeyHelper,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\Pricing\Helper\Data $priceHelper
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->_apiShipping         = $apiShipping;
        $this->cart                 = $cart;
        $this->productRepository    = $productRepository;
        $this->_calshippingData     = $calshippingData;
        $this->_coreRegistry        = $registry;
        $this->_itemFactory         = $itemFactory;
        $this->_wishlistProvider    = $wishlistProvider;
        $this->_resultJsonFactory   = $resultJsonFactory;
        $this->_shipModel           = $shippingModel;
        $this->_checkoutSession     = $checkoutSession;
        $this->pricekeyHelper       = $pricekeyHelper; 
        $this->_blockFactory        = $blockFactory;
        $this->_storeManager        = $storeManager;
        $this->_filterProvider      = $filterProvider;
        $this->_priceHelper         = $priceHelper;
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */

    public function execute()
    {
        $json_encode    = array();
        $productSku     = $this->_request->getParam('product_sku');
        $bssQty         = $this->_request->getParam('bss-qty-custom');
        $productQty     = $this->_request->getParam('bss-qty')[$bssQty];
        $countryId      = $this->_request->getParam('country_id');
        $regionId       = $this->_request->getParam('region_id');
        $postCode       = $this->_request->getParam('post_code');
        $currencyCode   = $this->_request->getParam('currency-code');
        $options        = $this->_request->getParam("options");
        
        $params         = $this->getRequest()->getParams();
        if(isset($params['include_cart']) && $params['include_cart']){
            $quote = $this->_checkoutSession->getQuote();
        } else {
            $quote = $this->_objectManager->create('Magento\Quote\Model\Quote');
        }
        $result = $this->_resultJsonFactory->create();
        if(isset($params['ajax']) && $params['ajax']){
            try {
                $product        = $this->_initProduct();
                

                if (isset($params['qty'])) {
                    $filter = new \Zend_Filter_LocalizedToNormalized(
                        ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                    );
                    $params['qty'] = $filter->filter($params['qty']);
                }
                /**
                 * Check product availability
                 */
                if (!$product) {
                    $json_encode["error"] = 1;
                    $json_encode["error_msg"] = __("The product is not available");
                    return $result->setData($json_encode);
                }
                $thickness      = $this->pricekeyHelper->getItemPricingKeyForShipping($options,$product);                
                try{
                    $tmgShipping    = $this->_apiShipping->getNbAvailableRates($productSku,$productQty,$countryId,$regionId,$postCode,$thickness);
                    /* return options popup content when product type is grouped */
                    if($this->_calshippingData->checkVersion()){
                        $productConfig = $product->getTypeId() != 'downloadable' && $product->getHasOptions()
                            || ($product->getTypeId() == 'grouped' && !isset($params['super_group']))
                            || ($product->getTypeId() == 'configurable' && !isset($params['super_attribute']))
                            || $product->getTypeId() == 'bundle';
                    } else {
                        $productConfig = $product->getHasOptions()
                            || ($product->getTypeId() == 'grouped' && !isset($params['super_group']))
                            || ($product->getTypeId() == 'configurable' && !isset($params['super_attribute']))
                            || $product->getTypeId() == 'bundle';
                    }

                    if($params['utype'] != "detail-add") {
                        if ($productConfig || $params['utype'] == "general-add") {
                            $this->_coreRegistry->register('product', $product);
                            $this->_coreRegistry->register('current_product', $product);

                            $json_encode["popup_option"] = 1;

                            $htmlPopup = $this->_calshippingData->getPopupOptionHtml($product);
                            $json_encode['html_popup_option'] = $htmlPopup;

                            return $result->setData($json_encode);
                        }
                    }
                    

                    $html = '';
                    if($tmgShipping && !empty($tmgShipping))
                    {
                        foreach ($tmgShipping as $key_rate => $value_rate) {
                            $html .='<p><h3>'.$key_rate.'</h3></p>';
                            if(!empty($value_rate)){
                                foreach ($value_rate as $value) 
                                {
                                    $RateDescription = ucwords(str_replace(['-','_','ups','fedex'],' ', mb_strtolower($value['RateDescription'])));
                                    $html .= '<p class="shipping-method-title">'.$RateDescription. " - " . $this->_priceHelper->currency($value['ListCharge'],true,false).'</p>';
                                }
                            }
                        }
                    }
                } catch (\Exception $e){
                    $blockId = 'shipping-weight-error';
                    $storeId = $this->_storeManager->getStore()->getId();
                    $block   = $this->_blockFactory->create();
                    $block->setStoreId($storeId)->load($blockId);
                    $html = $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($block->getContent());
                }
                

                $json_encode["shipping_estimaste"] = $html;
                /* endif $params["estimate"] */
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->_checkoutSession->getUseNotice(true)) {
                    $this->messageManager->addNotice(
                        $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage())
                    );
                    $json_encode["error_msg"] = $e->getMessage();
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $json_encode["error_msg"] = $message;
                    }
                }
                $json_encode["error"] = 1;
            } catch (\Exception $e) {
                $json_encode["error"] = 1;
                $json_encode["error_msg"] = $e->getMessage();
                $this->messageManager->addError( __($json_encode["error_msg"]));
            }
        }
        $json_encode["error"] = 0;
        $json_encode["currency_code"] = $currencyCode;

        return $result->setData($json_encode);
    }
}
