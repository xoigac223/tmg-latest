<?php

namespace TMG\CustomerPricing\Controller\Json;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use TMG\Customer\Controller\Json;
use TMG\Customer\Helper\Customer as CustomerHelper;
use TMG\CustomerPricing\Helper\CustomerPricing as CustomerPricingHelper;

class productCustomerPricing extends Json
{
    /**
     * @var CustomerPricingHelper
     */
    protected $customerPricingHelper;
    
    /**
     * @var ProductRepository
     */
    protected $productRepository;
    
    public function __construct(
        Context $context,
        CustomerHelper $customerHelper,
        CustomerPricingHelper $customerPricingHelper,
        ProductRepository $productRepository,
        FormKeyValidator $formKeyValidator,
        JsonHelper $jsonHelper,
        JsonFactory $jsonFactory
    ){
        parent::__construct($context, $customerHelper, $formKeyValidator, $jsonHelper, $jsonFactory);
        $this->customerPricingHelper = $customerPricingHelper;
        $this->productRepository = $productRepository;
    }
    
    public function execute()
    {
        $response = [
            'error' => false,
            'message' => ''
        ];
    
        try {
        
            if(!$content = $this->getRequest()->getContent()) {
                throw new LocalizedException(__('No Params'));
            }

            $params = $this->jsonHelper->jsonDecode($this->getRequest()->getContent());
        
            if (!isset($params['sku']) || empty($params['sku'])) {
                throw new LocalizedException(__('No SKU'));
            }
            
            $product = $this->productRepository->get($params['sku']);
            $customer = $this->customerHelper->getCurrentCustomer();
            
            if (!$customer->getId()) {
                throw new LocalizedException(__('No Customer'));
            }
            
            $response['result'] = $this->customerPricingHelper->getProductCustomerPricing($product);
            
        } catch (\Exception $e) {
            $response = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}