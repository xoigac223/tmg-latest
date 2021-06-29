<?php

namespace TMG\CustomerPricing\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

use TMG\Customer\Helper\Customer as CustomerHelper;
use TMG\PricingKey\Helper\PricingKey as PricingKeyHelper;
use TMG\CustomerPricing\Model\Service\CustomerPricing as CustomerPricingService;

class CustomerPricing extends AbstractHelper
{
    /**
     * @var CustomerHelper
     */
    protected $customerHelper;
    
    /**
     * @var PricingKeyHelper
     */
    protected $pricingKeyHelper;
    
    /**
     * @var CustomerPricingService
     */
    protected $customerPricingService;
    
    public function __construct(
        Context $context,
        CustomerHelper $customerHelper,
        PricingKeyHelper $pricingKeyHelper,
        CustomerPricingService $customerPricingService
    )
    {
        parent::__construct($context);
        $this->customerHelper = $customerHelper;
        $this->pricingKeyHelper = $pricingKeyHelper;
        $this->customerPricingService = $customerPricingService;
    }
    
    /**
     * @ToDo Add Canada Support
     *
     * @return string
     */
    public function getCultureCurrency()
    {
        return '';
    }
    
    public function getProductCustomerPricingConfigJson($product)
    {
        return json_encode($this->getProductCustomerPricingConfig($product));
    }
    
    public function getProductCustomerPricingConfig($product)
    {
        $result = [
            'print_method_pricing_key' => $this->pricingKeyHelper->getPricingKeyMapping($product),
            'order_sample_pricing_key' => PricingKeyHelper::ORDER_SAMPLE_PRICING_KEY,
            'option_label' => [
                'print_method' => $this->pricingKeyHelper->getPrintMethodOptionLabel(),
                'buy_option' => $this->pricingKeyHelper->getBuyOptionOptionLabel(),
                'color_charge' => $this->pricingKeyHelper->getColorChargeOptionLabel(),
            ],
            'option_value_label' => [
                'order_sample' => $this->pricingKeyHelper->getOrderSampleOptionValueLabel(),
                'color_charge' => $this->pricingKeyHelper->getColorChargeOptionValueLabel(),
            ],
            'delay_options_init' => [
                'timeout' => 300,
                'max_retry' => 10,
            ]
        ];
//        $this->_logger->info(print_r($result,true));
        return $result;
    }
    
    public function getProductCustomerPricing(Product $product, $customer = null)
    {
        $pricingMatrix = [];
        if(!$this->customerHelper->getEncryptAccount($customer)) {
            return $pricingMatrix;
        }
        
        $customer = ($customer) ?: $this->customerHelper->getCurrentCustomer();
        $params = [
            'CultureCurrency' => '',
            'ItemNumber' => $product->getSku(),
            'encryptedAccount' => $this->customerHelper->getEncryptAccount($customer),
        ];
        
        foreach ($this->pricingKeyHelper->getPricingKeyOptions($product) as $pricingKey) {
            $params['PricingKey'] = $pricingKey;
            $pricingMatrix[$pricingKey] = $this->mapCustomerPricingResponseData(
                $this->customerPricingService->doGetCustomerItemPricingRequest($params)
            );
        }
        return $pricingMatrix;
    
    }
    
    protected function mapCustomerPricingResponseData($pricingMatrix)
    {
        $result = [];
        foreach ($pricingMatrix as $priceConfig) {
            $result[$priceConfig['QuantityLevel']] = [
                'item_price' => $priceConfig['DiscountedNet'],
                'color_price' => $priceConfig['DiscountedAddColor'],
            ];
        }
        return $result;
    }
}