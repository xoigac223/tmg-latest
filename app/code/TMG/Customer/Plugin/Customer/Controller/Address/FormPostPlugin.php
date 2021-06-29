<?php

namespace TMG\Customer\Plugin\Customer\Controller\Address;

use Magento\Customer\Controller\Address\FormPost;
use TMG\Customer\Helper\Config as ConfigHelper;
use TMG\Customer\Helper\Customer as CustomerHelper;

class FormPostPlugin
{
    
    protected $configHelper;
    
    protected $customerHelper;
    
    public function __construct(
        ConfigHelper $configHelper,
        CustomerHelper $customerHelper
    )
    {
        $this->configHelper = $configHelper;
        $this->customerHelper = $customerHelper;
    }
    
    /**
     * @param FormPost $subject
     * @return null
     */
    public function beforeExecute(FormPost $subject)
    {
        $this->configHelper->setIsCustomerAddressFormPost();
        return null;
    }
}