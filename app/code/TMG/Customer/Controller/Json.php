<?php

namespace TMG\Customer\Controller;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

use TMG\Customer\Helper\Customer as CustomerHelper;

abstract class Json extends \Magento\Framework\App\Action\Action
{
    
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * @var JsonHelper
     */
    protected $jsonHelper;
    
    /**
     * @var CustomerHelper
     */
    protected $customerHelper;
    
    /**
     * @var FormKeyValidator
     */
    protected $formKeyValidator;
    
    
    public function __construct(
        Context $context,
        CustomerHelper $customerHelper,
        FormKeyValidator $formKeyValidator,
        JsonHelper $jsonHelper,
        JsonFactory $jsonFactory
    )
    {
        parent::__construct($context);
        $this->customerHelper = $customerHelper;
        $this->jsonHelper = $jsonHelper;
        $this->resultJsonFactory = $jsonFactory;
        $this->formKeyValidator = $formKeyValidator;
    }
    
    
}