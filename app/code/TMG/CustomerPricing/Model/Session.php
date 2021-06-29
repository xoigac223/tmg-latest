<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TMG\CustomerPricing\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface as CustomerData;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\ResourceModel\Customer as ResourceCustomer;

/**
 * Customer session model
 * @method string getNoReferer()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Session extends \Magento\Customer\Model\Session
{
    /**
     * Get customer group id
     * If customer is not logged in system, 'not logged in' group id will be returned
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        if($this->getCustomerData() && !$this->getCustomerData()->getCustomAttribute('tmg_encrypt_account')){
            return \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID;
        }
        if ($this->storage->getData('customer_group_id')) {
            return $this->storage->getData('customer_group_id');
        }
        if ($this->getCustomerData()) {
            $customerGroupId = $this->getCustomerData()->getGroupId();
            $this->setCustomerGroupId($customerGroupId);
            return $customerGroupId;
        }
        return \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID;
    }
}
