<?php

namespace TMG\Customer\Plugin\Customer\Model\ResourceModel;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\ResourceModel\AddressRepository;
use TMG\Customer\Helper\Config as ConfigHelper;
use TMG\Customer\Helper\Customer as CustomerHelper;

class AddressRepositoryPlugin
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
     * @param AddressRepository $subject
     * @param AddressInterface $address
     * @return null
     */
    public function beforeSave(AddressRepository $subject, AddressInterface $address)
    {
        if($this->configHelper->getIsCustomerAddressFormPost()) {
            // Update Address
            $this->customerHelper->updateApiAddressFromMagentoAddress($address);
        }
        return null;
    }
}