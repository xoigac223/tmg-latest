<?php

namespace TMG\Customer\Helper;

use Magento\Framework\Registry;
use Magento\Framework\App\Helper\AbstractHelper;

class Config extends AbstractHelper
{
    
    // MAIN ATTRIBUTES
    
    const ATTRIBUTE_ENCRYPT_ACCOUNT = 'tmg_encrypt_account';
    
    const ATTRIBUTE_USER_ID = 'tmg_user_id';
    
    const ATTRIBUTE_SLX_CONTACT_ID = 'tmg_slx_contact_id';
    
    const ATTRIBUTE_ACCOUNT_ID = 'tmg_account_id';
    
    const ATTRIBUTE_SALES_TAX_RATES = 'tmg_sales_tax_rates';
    
    const ATTRIBUTE_TELEPHONE = 'tmg_telephone';
    
    const ATTRIBUTE_FAX = 'tmg_fax';
    
    // INDUSTRY ATTRIBUTES
    
    const ATTRIBUTE_COMPANY_NAME = 'tmg_company_name';
    
    const ATTRIBUTE_MAGNET_ACCOUNT_ID = 'tmg_magnet_account_id';
    
    const ATTRIBUTE_ASI_ACCOUNT_ID = 'tmg_asi_account_id';
    
    const ATTRIBUTE_PPAI_ACCOUNT_ID = 'tmg_ppai_account_id';
    
    const ATTRIBUTE_SAGE_ACCOUNT_ID = 'tmg_sage_account_id';
    
    // AUTH ATTRIBUTES
    
    const ATTRIBUTE_FTP_AUTHORIZED = 'tmg_ftp_authorized';
    
    const ATTRIBUTE_SPECIAL_PRICING_AUTHORIZED = 'tmg_special_pricing_authorized';
    
    const ATTRIBUTE_EPAY_AUTHORIZED = 'tmg_epay_authorized';
    
    const ATTRIBUTE_CHARGE_FREIGHT_HANDLING = 'tmg_charge_freight_handling';
    
    // ADDRESS ATTRIBUTES
    
    const ATTRIBUTE_ADDRESS_ID = 'tmg_address_id';
    
    // REGISTRY FLAGS
    
    const REGISTRY_FLAG_IS_CUSTOMER_EDIT_POST = 'is_customer_edit_post';
    
    const REGISTRY_FLAG_IS_CUSTOMER_ADDRESS_FORM_POST = 'is_customer_address_form_post';
    
    const REGISTRY_FLAG_IS_CUSTOMER_PASSWORD_CHANGE = 'is_customer_password_change';
    
    const REGISTRY_FLAG_SKIP_API_LOGIN_CREATE = 'skip_api_login_create';
    
    
    /**
     * @var Registry
     */
    protected $registry;
    
    /**
     * @var array
     */
    protected $customerFrontAttributesForms = [
        'adminhtml_customer',
        'customer_account_create',
        'customer_account_edit',
    ];
    
    /**
     * @var array
     */
    protected $addressFrontAttributesForms = [
        'adminhtml_customer_address',
        'customer_address_edit',
        'customer_register_address',
    ];
    
    
    public function __construct
    (
        Registry $registry
    )
    {
        $this->registry = $registry;
    }
    
    /// - - - ATTRIBUTES
    
    /**
     * @return array
     */
    public function getCustomAddressAttributesConfig()
    {
        return [
            // Internal
            self::ATTRIBUTE_ADDRESS_ID => [
                'label' => 'Address ID',
                'type' => 'varchar',
                'input' => 'text',
                'position' => 1000,
                'used_in_forms' => $this->addressFrontAttributesForms,
            ],
        ];
    }
    
    /**
     * @return array
     */
    public function getCustomCustomerAttributesConfig()
    {
        return [
            
            // Internal
            self::ATTRIBUTE_ENCRYPT_ACCOUNT => [
                'label' => 'Encrypt Account',
                'type' => 'varchar',
                'input' => 'text',
                'position' => 1000,
                'used_in_forms' => $this->customerFrontAttributesForms,
            ],
            self::ATTRIBUTE_ACCOUNT_ID => [
                'label' => 'Account ID',
                'type' => 'varchar',
                'input' => 'text',
                'position' => 1001,
                'used_in_forms' => $this->customerFrontAttributesForms,
            ],
            self::ATTRIBUTE_USER_ID => [
                'label' => 'User ID Number',
                'type' => 'varchar',
                'input' => 'text',
                'position' => 1002,
                'used_in_forms' => $this->customerFrontAttributesForms,
            ],
            self::ATTRIBUTE_SLX_CONTACT_ID => [
                'label' => 'SLX Contact ID',
                'type' => 'varchar',
                'input' => 'text',
                'position' => 1003,
                'used_in_forms' => $this->customerFrontAttributesForms,
            ],
            self::ATTRIBUTE_SALES_TAX_RATES => [
                'label' => 'Sales tax Rates',
                'type' => 'text',
                'input' => 'text',
                'position' => 1004,
            ],
            // Accounts
            self::ATTRIBUTE_TELEPHONE => [
                'label' => 'Phone',
                'type' => 'varchar',
                'input' => 'text',
                'position' => 1010,
                'used_in_forms' => $this->customerFrontAttributesForms,
            ],
            self::ATTRIBUTE_FAX => [
                'label' => 'Fax',
                'type' => 'varchar',
                'input' => 'text',
                'position' => 1010,
                'used_in_forms' => $this->customerFrontAttributesForms,
            ],
            self::ATTRIBUTE_COMPANY_NAME => [
                'label' => 'Company Name',
                'type' => 'varchar',
                'input' => 'text',
                'position' => 1010,
                'used_in_forms' => $this->customerFrontAttributesForms,
            ],
            self::ATTRIBUTE_MAGNET_ACCOUNT_ID => [
                'label' => 'TheMagnetGroup',
                'type' => 'varchar',
                'input' => 'text',
                'position' => 1010,
                'used_in_forms' => $this->customerFrontAttributesForms,
            ],
            self::ATTRIBUTE_ASI_ACCOUNT_ID => [
                'label' => 'ASI',
                'type' => 'varchar',
                'input' => 'text',
                'position' => 1011,
                'used_in_forms' => $this->customerFrontAttributesForms,
            ],
            self::ATTRIBUTE_PPAI_ACCOUNT_ID=> [
                'label' => 'PPAI',
                'type' => 'varchar',
                'input' => 'text',
                'position' => 1012,
                'used_in_forms' => $this->customerFrontAttributesForms,
            ],
            self::ATTRIBUTE_SAGE_ACCOUNT_ID => [
                'label' => 'SAGE',
                'type' => 'varchar',
                'input' => 'text',
                'position' => 1013,
                'used_in_forms' => $this->customerFrontAttributesForms,
            ],
            
            // Authorization
            self::ATTRIBUTE_FTP_AUTHORIZED => [
                'label' => 'FTP Authorized',
                'type' => 'int',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'default' => '0',
                'position' => 1020,
            ],
            self::ATTRIBUTE_SPECIAL_PRICING_AUTHORIZED => [
                'label' => 'Special Pricing Authorized',
                'type' => 'int',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'default' => '0',
                'position' => 1021,
            ],
            self::ATTRIBUTE_EPAY_AUTHORIZED => [
                'label' => 'ePay Authorized',
                'type' => 'int',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'default' => '0',
                'position' => 1022,
            ],
            self::ATTRIBUTE_CHARGE_FREIGHT_HANDLING => [
                'label' => 'Charge Freight 3rdParty Handling',
                'type' => 'int',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'default' => '0',
                'position' => 1023,
            ],
        ];
    }
    
    /// - - - REGISTRY FLAGS
    
    public function getIsCustomerEditPost()
    {
        return (bool) $this->registry->registry(self::REGISTRY_FLAG_IS_CUSTOMER_EDIT_POST);
    }
    
    public function setIsCustomerEditPost()
    {
        $this->registry->register(self::REGISTRY_FLAG_IS_CUSTOMER_EDIT_POST,true);
        return $this;
    }
    
    public function getIsCustomerAddressFormPost()
    {
        return (bool) $this->registry->registry(self::REGISTRY_FLAG_IS_CUSTOMER_ADDRESS_FORM_POST);
    }
    
    public function setIsCustomerAddressFormPost()
    {
        $this->registry->register(self::REGISTRY_FLAG_IS_CUSTOMER_ADDRESS_FORM_POST,true);
        return $this;
    }
    
    public function getIsCustomerPasswordChange()
    {
        return (bool) $this->registry->registry(self::REGISTRY_FLAG_IS_CUSTOMER_PASSWORD_CHANGE);
    }
    
    public function setIsCustomerPasswordChange()
    {
        $this->registry->register(self::REGISTRY_FLAG_IS_CUSTOMER_PASSWORD_CHANGE,true);
        return $this;
    }
    
    public function getSkipApiLoginCreate()
    {
        return (bool) $this->registry->registry(self::REGISTRY_FLAG_SKIP_API_LOGIN_CREATE);
    }
    
    public function setSkipApiLoginCreate()
    {
        $this->registry->register(self::REGISTRY_FLAG_SKIP_API_LOGIN_CREATE,true);
        return $this;
    }
    
    
    
    
}