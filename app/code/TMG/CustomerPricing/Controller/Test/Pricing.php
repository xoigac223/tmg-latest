<?php

namespace TMG\CustomerPricing\Controller\Test;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Context;

use TMG\Base\Controller\Test;
use TMG\CustomerPricing\Helper\CustomerPricing as CustomerPricingHelper;
use TMG\Customer\Helper\Customer as CustomerHelper;

class Pricing extends Test
{
    
    /**
     * @var CustomerPricingHelper
     */
    protected $customerPricingHelper;
    
    /**
     * @var CustomerHelper
     */
    protected $customerHelper;
    
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    
    public function __construct(
        Context $context,
        CustomerHelper $customerHelper,
        CustomerPricingHelper $customerPricingHelper,
        ProductRepositoryInterface $productRepository
    )
    {
        parent::__construct($context);
        $this->customerHelper = $customerHelper;
        $this->customerPricingHelper = $customerPricingHelper;
        $this->productRepository = $productRepository;
    }
    
    
    public function execute()
    {
        try {
            
            // Param
            if(!$sku = $this->getRequest()->getParam('sku',false)) {
                if(!$id = $this->getRequest()->getParam('id',false)) {
                    echo 'NO ID OR SKU'; die();
                }
                $product = $this->productRepository->getById($id);
            } else {
                $product = $this->productRepository->get($sku);
            }
    
            $customer = $this->customerHelper->getCurrentCustomer();
            
            if(!$customer->getId()) {
                echo 'NO CUSTOMER LOGGED'; die();
            }
            
            echo '<pre>';
            $pricing = $this->customerPricingHelper->getProductCustomerPricing($product);
            print_r("\nRESULT:\n");
            print_r($pricing);
            echo '</pre>';
        
        } catch (\Exception $e) {

            print_r("\nERROR:\n");
            echo "<h3>{$e->getMessage()}</h3>";

        }
        
        
        die("\n" . __METHOD__ . "\n");
    }
}