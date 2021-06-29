<?php

namespace TMG\Customer\Plugin\Customer\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement;

use TMG\Customer\Helper\Customer as CustomerHelper;
use TMG\Customer\Helper\Config as ConfigHelper;

class AccountManagementPlugin
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
     * @param AccountManagement $accountManagement
     * @param callable $proceed
     * @param $email
     * @param $password
     * @return CustomerInterface|null
     * @throws \Magento\Framework\Exception\InvalidEmailOrPasswordException
     */
    public function aroundAuthenticate(AccountManagement $accountManagement,callable $proceed, $email, $password)
    {
        $this->configHelper->setSkipApiLoginCreate();
        return $this->customerHelper->loginMagentoUser($email,$password);
    }
    
    /**
     * @param AccountManagement $accountManagement
     * @param callable $proceed
     * @param CustomerInterface $customer
     * @param null $password
     * @param string $redirectUrl
     * @return mixed
     * @throws \Exception
     */
    public function aroundCreateAccount(AccountManagement $accountManagement,callable $proceed, CustomerInterface $customer, $password = null, $redirectUrl = '')
    {
        // Avoid Error on Address Telephone
        foreach ($customer->getAddresses() as $address) {
            if(!$address->getTelephone()) {
                $address->setTelephone($customer->getCustomAttribute('tmg_telephone')->getValue());
            }
        }
        if(!$this->configHelper->getSkipApiLoginCreate()) {
            $this->customerHelper->createApiLoginFromMagentoUser($customer, $password);
        }
        return $proceed($customer,$password,$redirectUrl);
    }
    
    /**
     * @param AccountManagement $accountManagement
     * @param callable $proceed
     * @param $email
     * @param $currentPassword
     * @param $newPassword
     * @return mixed
     * @throws \Exception
     */
    public function aroundChangePassword(AccountManagement $accountManagement, callable $proceed, $email, $currentPassword, $newPassword)
    {
        $this->configHelper->setIsCustomerPasswordChange();
        $this->customerHelper->changeApiUserPassword($email,$currentPassword,$newPassword);
        return $proceed($email, $currentPassword, $newPassword);
    }
    
    public function aroundInitiatePasswordReset(AccountManagement $accountManagement, callable $proceed, $email, $template, $websiteId)
    {
        return $this->customerHelper->createApiResetPwdRequest($email);
    }
    
}