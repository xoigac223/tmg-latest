<?php

namespace TMG\Customer\Plugin\Customer\Model\ResourceModel;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;

use TMG\Customer\Helper\Customer as CustomerHelper;
use TMG\Customer\Helper\Config as ConfigHelper;


class CustomerRepositoryPlugin
{
    /**
     * @var CustomerHelper
     */
    protected $customerHelper;
    
    /**
     * @var ConfigHelper
     */
    protected $configHelper;
    
    /**
     * CustomerRepositoryPlugin constructor.
     * @param CustomerHelper $customerHelper
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        CustomerHelper $customerHelper,
        ConfigHelper $configHelper
    )
    {
        $this->customerHelper = $customerHelper;
        $this->configHelper = $configHelper;
    }
    
    /**
     * @param CustomerRepository $subject
     * @param CustomerInterface $customer
     * @return null
     * @throws \TMG\Customer\Exception\IncompleteUserException
     */
    public function beforeSave(CustomerRepository $subject, CustomerInterface $customer)
    {
        if($this->configHelper->getIsCustomerEditPost() && !$this->configHelper->getIsCustomerPasswordChange()) {
            $this->customerHelper->updateApiAccountFromMagentoUser($customer);
        }
        return null;
    }
    
}