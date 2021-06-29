<?php

namespace TMG\Customer\Helper;

use Magento\Store\Model\StoreManagerInterface;

use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Reflection\DataObjectProcessor;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\AddressFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;

use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\CustomerFactory;

use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\Session as CustomerSession;

use TMG\Customer\Exception\IncompleteUserException;
use TMG\Customer\Model\Service\CustomerSecurity as CustomerSecurityService;
use TMG\Customer\Model\Service\Contact as ContactService;
use TMG\Customer\Helper\Config as ConfigHelper;
use Magento\Store\Model\ScopeInterface;

class Customer extends AbstractHelper
{
    protected $customerBaseAttributesMapping = [
        'firstname' => 'FirstName',
        'lastname' => 'LastName',
        'fax' => 'Fax',
        'email' => 'Email',
    ];
    
    protected $customerSpecialAttributesMapping = [
        ConfigHelper::ATTRIBUTE_ENCRYPT_ACCOUNT => 'EncryptAccount',
        ConfigHelper::ATTRIBUTE_ACCOUNT_ID => 'AccountID',
        ConfigHelper::ATTRIBUTE_USER_ID => 'UserIDNumber',
        ConfigHelper::ATTRIBUTE_SLX_CONTACT_ID => 'SLXContactID',
        ConfigHelper::ATTRIBUTE_SALES_TAX_RATES => 'SalesTaxRates',
        ConfigHelper::ATTRIBUTE_MAGNET_ACCOUNT_ID => 'MagnetAccount',
        ConfigHelper::ATTRIBUTE_ASI_ACCOUNT_ID => 'ASIAccount',
        ConfigHelper::ATTRIBUTE_PPAI_ACCOUNT_ID => 'PPAIAccount',
        ConfigHelper::ATTRIBUTE_SAGE_ACCOUNT_ID => 'SAGEAccount',
        ConfigHelper::ATTRIBUTE_FTP_AUTHORIZED => 'FTPAuthorized',
        ConfigHelper::ATTRIBUTE_SPECIAL_PRICING_AUTHORIZED => 'SpecialPricingAuthorized',
        ConfigHelper::ATTRIBUTE_CHARGE_FREIGHT_HANDLING => 'ChargeFreight3rdPartyHandling',
        ConfigHelper::ATTRIBUTE_EPAY_AUTHORIZED => 'ePayAuthorized',
        ConfigHelper::ATTRIBUTE_COMPANY_NAME => 'CompanyName',
        ConfigHelper::ATTRIBUTE_TELEPHONE => 'WorkPhone',
        ConfigHelper::ATTRIBUTE_FAX => 'Fax',
    ];
    
    protected $addressBaseAttributesMapping = [
        'postcode' => 'ZipCode',
        'region' => 'State',
        'city' => 'City',
        'email' => 'Email',
        'country' => 'Country',
        'default_billing' => 'BillingAddress',
        'default_shipping' => 'ShippingAddress',
        'street_1' => 'Address1',
        'street_2' => 'Address2',
    ];
    
    protected $addressSpecialAttributesMapping = [
        ConfigHelper::ATTRIBUTE_ADDRESS_ID => 'AddressID',
    ];
    
    protected $addressCustomerAttributesMapping = [
        'firstname' => 'FirstName',
        'lastname' => 'LastName',
        'telephone' => 'WorkPhone',
    ];
    
    
    
    /**
     * @var CustomerSecurityService
     */
    protected $customerSecurityService;
    
    /**
     * @var ContactService
     */
    protected $contactService;
    
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;
    
    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerDataFactory;
    
    /**
     * @var AddressInterfaceFactory
     */
    protected $addressDataFactory;
    
    /**
     * @var RegionInterfaceFactory
     */
    protected $regionDataFactory;
    
    /**
     * @var RegionFactory
     */
    protected $regionFactory;
    
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    
    /**
     * @var CustomerSession
     */
    protected $customerSession;
    
    /**
     * @var AccountManagement
     */
    protected $accountManagement;
    
    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;
    
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    
    /**
     * @var AuthenticationInterface
     */
    protected $authentication;

    
    /**
     * @var array
     */
    protected $accountTypeIds = [
        'magnet' => 'MagnetAccount',
        'asi' => 'ASIAccount',
        'ppai' => 'PPAIAccount',
        'sage' => 'SAGEAccount',
    ];
    
    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;
    
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        CustomerInterfaceFactory $customerDataFactory,
        AddressInterfaceFactory $addressInterfaceFactory,
        CustomerSecurityService $customerSecurityService,
        RegionInterfaceFactory $regionInterfaceFactory,
        RegionFactory $regionFactory,
        ContactService $contactService,
        AccountManagement $accountManagement,
        GroupManagementInterface $groupManagement,
        StoreManagerInterface $storeManager,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    )
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->customerDataFactory = $customerDataFactory;
        $this->addressDataFactory = $addressInterfaceFactory;
        $this->regionDataFactory = $regionInterfaceFactory;
        $this->regionFactory = $regionFactory;
        $this->customerSecurityService = $customerSecurityService;
        $this->contactService = $contactService;
        $this->accountManagement = $accountManagement;
        $this->groupManagement = $groupManagement;
        $this->storeManager = $storeManager;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }
    
    /**
     * @param $id
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerById($id)
    {
        try {
            return $this->customerRepository->getById($id);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
    
    /**
     * @param $email
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerByEmail($email)
    {
        try {
            return $this->customerRepository->get($email);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
    
    /**
     * @return CustomerModel
     */
    public function getCurrentCustomer()
    {
        return $this->customerSession->getCustomer();
    }
    
    /**
     * @param $email
     * @return $this
     * @throws \Zend_Validate_Exception
     */
    public function validateEmail($email)
    {
        // Email Validation
        if (!\Zend_Validate::is(trim($email), 'NotEmpty')) {
            throw new \Exception('Email Address can not be empty.');
        }
        if (!\Zend_Validate::is(trim($email), 'EmailAddress')) {
            throw new \Exception('"' . $email . '" is not a valid Email Address.');
        }
        return $this;
    }
    
    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     */
    protected function getAuthentication()
    {
        if (!($this->authentication instanceof AuthenticationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Customer\Model\AuthenticationInterface::class
            );
        } else {
            return $this->authentication;
        }
    }
    
    
    /******************************************************************************************************************/
    /*************************************************************************************** CUSTOM DATA RETRIEVERS ***/
    /******************************************************************************************************************/
    
    // Custom Attributes - Customer
    
    /**
     * @param $attribute
     * @param null $customer
     * @return mixed
     */
    public function getCustomAttributeValue($attribute, $customer = null)
    {
        $customer = ($customer) ?: $this->getCurrentCustomer();
        if($customer instanceof CustomerInterface ) {
            if($attribute = $customer->getCustomAttribute($attribute)) {
                return $attribute->getValue();
            }
            return null;
        }
        return $customer->getData($attribute);
    }
    
    /**
     * @param null $customer
     * @return mixed
     */
    public function getEncryptAccount($customer = null)
    {
        return $this->getCustomAttributeValue(ConfigHelper::ATTRIBUTE_ENCRYPT_ACCOUNT,$customer);
    }
    
    /**
     * @param null $customer
     * @return bool|mixed
     */
    public function isSpecialPriceAuthorized($customer = null)
    {
        return $this->getCustomAttributeValue(ConfigHelper::ATTRIBUTE_SPECIAL_PRICING_AUTHORIZED,$customer);
    }
    
    /**
     * @param null $customer
     * @return bool|mixed
     */
    public function isFtpAuthorized($customer = null)
    {
        return $this->getCustomAttributeValue(ConfigHelper::ATTRIBUTE_FTP_AUTHORIZED,$customer);
    }
    
    /**
     * @param null $customer
     * @return bool|mixed
     */
    public function isEpayAuthorized($customer = null)
    {
        return $this->getCustomAttributeValue(ConfigHelper::ATTRIBUTE_EPAY_AUTHORIZED,$customer);
    }
    
    // Custom Attributes - Address
    
    /**
     * @param $address
     * @return bool|mixed
     */
    public function getAddressTmgId($address)
    {
        if($address instanceof AddressInterface){
            $attribute = $address->getCustomAttribute(ConfigHelper::ATTRIBUTE_ADDRESS_ID);
            if($attribute) {
                return $attribute->getValue();
            }
            return null;
        }
        return (bool) $address->getData(ConfigHelper::ATTRIBUTE_ADDRESS_ID);
    }
    
    
    
    /******************************************************************************************************************/
    /********************************************************************************************** MAPPING METHODS ***/
    /******************************************************************************************************************/

    /**
     * @param $id
     * @return mixed|null
     */
    public function getAccountTypeId($id)
    {
        if(!isset($this->accountTypeIds[$id])) {
            return null;
        }
        return $this->accountTypeIds[$id];
    }
    
    /**
     * @param $data
     * @return array
     */
    public function mapApiCustomerData($data)
    {
        $result = [];
        
        // Base & Special Data
        $customerAttributes = array_merge($this->customerBaseAttributesMapping, $this->customerSpecialAttributesMapping);
        foreach ($customerAttributes as $magentoCode => $apiCode) {
            if(isset($data[$apiCode])) {
                $result[$magentoCode] = $data[$apiCode];
            }
        }

        // Addresses
        if(isset($data['Addresses'])) {
            $addresses = [];
            $addressAttributes = array_merge($this->addressBaseAttributesMapping, $this->addressSpecialAttributesMapping);
            foreach ($data['Addresses'] as $addressData) {
                $address = [];
                // Address Mapping
                foreach ($addressAttributes as $magentoCode => $apiCode) {
                    if(isset($addressData[$apiCode])) {
                        $address[$magentoCode] = $addressData[$apiCode];
                    }
                }
                // From Customer
                foreach ($this->addressCustomerAttributesMapping as $magentoCode => $apiCode) {
                    if(isset($data[$apiCode])) {
                        $address[$magentoCode] = $data[$apiCode];
                    }
                }
                $addresses[] = $address;
            }
            $result['addresses'] = $addresses;
        }
        // Serialized Data
        if(isset($data['SalesTaxRates'])) {
            $result[ConfigHelper::ATTRIBUTE_SALES_TAX_RATES] = serialize($data['SalesTaxRates']);
        }
        return $result;
        
    }
    
    /**
     * @param CustomerInterface $object
     * @return \Magento\Framework\DataObject
     */
    public function getCustomerDataFromDataObject(CustomerInterface $object)
    {
        $data = $this->dataObjectProcessor->buildOutputDataArray(
            $object,
            'Magento\Customer\Api\Data\CustomerInterface'
        );
        // Custom Attributes
        foreach ($object->getCustomAttributes() as $attribute) {
            $data[$attribute->getAttributeCode()] = $attribute->getValue();
        }
        // Addresses
        $addressData = $this->dataObjectProcessor->buildOutputDataArray(
            $object->getAddresses()[0],
            'Magento\Customer\Api\Data\AddressInterface'
        );
        
        // Cleanup
        unset($addressData['firstname'],$addressData['lastname']);
        
        $data = array_merge($data,$addressData);
        unset($data['custom_attributes'],$data['addresses']);
        
        $dataObject = new \Magento\Framework\DataObject();
        $dataObject->setData($data);

        return $dataObject;
    }
    
    
    /**
     * @param $addressData
     * @return array
     */
    protected function extractAddresses($addressData)
    {
        $addresses = [];
        
        foreach ($addressData['addresses'] as $address) {
            
            try {
                
                // @TODO - Consider to move this to Mapping Method
                
                // Country Mapping
                $address['country_id'] = ($address['country'] == 'CAN') ? 'CA' : 'US';
                
                // Region
                $region = $address['region'];
                /* @var Region $regionModel */
                $regionModel = $this->regionFactory->create();
                $regionModel->loadByName($region,$address['country_id']);
                if(!$regionModel->getId()) {
                    throw new LocalizedException(__('State "%1" Does not exist.',$region));
                }
                
                // Phone
                $address['telephone'] = empty($address['telephone']) ? '55555555' : $address['telephone'];
                
                // Street
                $address['street'] = [$address['street_1'],$address['street_2']];
                
                // Cleanup
                unset($address['street_1'],$address['street_2'],$address['region'],$address['country']);
                
                
                // AddressData
                $addressDataObject = $this->addressDataFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $addressDataObject,
                    $address,
                    '\Magento\Customer\Api\Data\AddressInterface'
                );
                
                // Region / Country City
                $regionDataObject = $this->regionDataFactory->create();
                $regionDataObject->setRegionId($regionModel->getId());
                $regionDataObject->setRegion($region);
                // Data
                $addressDataObject->setRegion($regionDataObject);
                $addressDataObject->setIsDefaultBilling($address['default_billing']);
                $addressDataObject->setIsDefaultShipping($address['default_shipping']);
                
                $addresses[] = $addressDataObject;
                
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }
        
        return $addresses;
        
    }
    
    /******************************************************************************************************************/
    /************************************************************************************************** API METHODS ***/
    /******************************************************************************************************************/
    
    /**
     * @param $email
     * @param bool $checkMagentoAccount
     * @return array|void
     */
    public function validateApiAccountEmail($email, $checkMagentoAccount = true)
    {
        $result = [
            'status' => true,
            'has_magento_account' => false,
            'has_tmg_account' => false,
            'has_tmg_login' => false,
            'message' => 'OK',
        ];
        
        try {
            
            $this->validateEmail($email);
            
            if($checkMagentoAccount) {
                if($customer = $this->getCustomerByEmail($email)) {
                    $result['has_magento_account'] = true;
                    throw new AlreadyExistsException(__('There is already an account with this email address.'));
                }
            }
            
            // Check if email exist on System
            if(!$this->customerSecurityService->doLookupEmailRequest($email)) {
                throw new \Exception("Email address does not match any existent Account.");
            }
            
            $result['has_tmg_account'] = true;
    
            // Check if email is associated to an account
            if($this->customerSecurityService->doValidAccountEmailRequest($email)) {
                $result['has_tmg_login'] = true;
                throw new \Exception("There is already an login created with this email address.");
            }
        
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
        
        return $result;
        
    }
    
    /**
     * @param $email
     * @param $accountType
     * @param $accountId
     * @return array
     */
    public function validateApiAccountId($email,$accountType,$accountId)
    {
        $result = [
            'status' => true,
            'data' => [],
            'message' => 'OK',
        ];
    
        try {
        
            $this->validateEmail($email);
            $result['data'] = $this->customerSecurityService->doLookupAccountRequest($email,$accountType,$accountId);
        
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
    
        return $result;
    }
    
    /**
     * @param $user
     * @param $pass
     * @return array
     */
    public function loginApiUser($user,$pass)
    {
        $result = [
            'status' => true,
            'data' => [],
            'message' => 'OK',
        ];
    
        try {
        
            $this->validateEmail($user);
            
            $result['data'] = $this->customerSecurityService->doCustomerLoginRequest($user,$pass);
        
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
    
        return $result;
    
    }
    
    /**
     * @param $data
     * @return array
     */
    public function createApiLogin($data)
    {
        $result = [
            'status' => true,
            'data' => [],
            'message' => 'OK',
        ];
    
        try {
        
            $this->validateEmail($data['User']);
            $result['data'] = $this->customerSecurityService->doCreateLoginRequest($data);
        
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
    
        return $result;
    }
    
    /**
     * @param $email
     * @param $encryptAccount
     * @return array
     */
    public function getApiContactData($email,$encryptAccount)
    {
        $result = [
            'status' => true,
            'data' => [],
            'message' => 'OK',
        ];
    
        try {
        
            $this->validateEmail($email);
            $result['data'] = $this->contactService->doGetContactDataRequest($email,$encryptAccount);
        
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();
        }
    
        return $result;
    
    }
    
    /**
     * @param $email
     * @param $loginData
     * @return array
     * @throws IncompleteUserException
     * @throws InvalidEmailOrPasswordException
     */
    public function getApiCustomerFullData($email,$loginData)
    {
        $result = $loginData;
        /*if(!isset($loginData['data']['EncryptAccount']) || !$loginData['data']['EncryptAccount']){
            $message = $this->getCustomerErrorMessage();
            $e       = new LocalizedException(__($message));
            $this->_logger->critical($e);
            throw $e;
        }*/
        $contactResult = $this->getApiContactData($email,$loginData['data']['EncryptAccount']);        
        if(!$contactResult['status'] || !isset($contactResult['data']) || empty($result['data'])) {
            // Error
            $e = new LocalizedException(__($contactResult['message']));;
            $this->_logger->critical($e);
            throw $e;
        }
        
        $result = array_merge($result,$contactResult['data']);
        
        // Account Lookup Data
        if($loginData['data']['EncryptAccount'])
        {
            $lookupResult = [];
            $accountKeys = [
                $this->customerSpecialAttributesMapping[ConfigHelper::ATTRIBUTE_MAGNET_ACCOUNT_ID],
                $this->customerSpecialAttributesMapping[ConfigHelper::ATTRIBUTE_ASI_ACCOUNT_ID],
                $this->customerSpecialAttributesMapping[ConfigHelper::ATTRIBUTE_PPAI_ACCOUNT_ID],
                $this->customerSpecialAttributesMapping[ConfigHelper::ATTRIBUTE_SAGE_ACCOUNT_ID],
            ];
            
            foreach($accountKeys as $accountKey) {
                if(isset($result[$accountKey]) && !empty($result[$accountKey])) 
                {
                    $lookupResult = $this->validateApiAccountId($email, $this->accountTypeIds['magnet'],$result[$accountKey]);
                    break;
                }
            }
            
            if(!empty($lookupResult)) {
                if(!$lookupResult['status'] || !isset($lookupResult['data'])) {
                    // Error
                    $e = new InvalidEmailOrPasswordException(__($lookupResult['ErrorMessage']));;
                    $this->_logger->critical($e);
                    throw $e;
                }
                $result = array_merge($result, $lookupResult['data']);
                ksort($result);
            }
        } else {
            $result['EncryptAccount'] = '';
        }

        
        
//        if(empty($lookupResult)) {
//            throw new IncompleteUserException(__('This user should be created and activated on our system.'));
//        }
        
        // FirstName Validation
        if(empty($result['FirstName'])) {
            $parts = explode('@',$result['Email']);
            $result['FirstName'] = ucwords(str_replace(['.','_','-'],' ',$parts[0]));
        }
        
//        echo '<pre>';
//        var_dump($result); die(__METHOD__);
        
        return $result;
        
    }
    
    /******************************************************************************************************************/
    /************************************************************************************* MAGENTO CUSTOMER METHODS ***/
    /******************************************************************************************************************/
    
    /**
     * @param $email
     * @param $pass
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @throws InvalidEmailOrPasswordException
     */
    public function loginMagentoUser($email,$pass)
    {
        $apiLoginResult = $this->loginApiUser($email,$pass);
    
        // API Login
        if(!isset($apiLoginResult['status']) || !$apiLoginResult['status']) {
            $message = isset($apiLoginResult['message'])
                ? __($apiLoginResult['message']) : __('Invalid login or password.') ;
            throw new InvalidEmailOrPasswordException($message);
        }
        
        // Magento Customer Creation & Login
        $this->customerSession->regenerateId();
        
        try {
        
            // Check Magento User
            if(!$customer = $this->getCustomerByEmail($email)) {
                $customer = $this->createMagentoUserFromApiUser($email, $pass, $apiLoginResult);
            } else {
                try {
                    // Update Magento User With API One
                    $this->updateMagentoUserFromApiUser($customer,$apiLoginResult);
                } catch (\Exception $e) {
                    // Add Customer Message send
                    if($e->getMessage() !=  'The email and account combination does not exist.') {
                        throw $e;
                    }
                    // Ignore ...
                }
            }
    
    
//            // Check Magento User
//            if(!$customer = $this->getCustomerByEmail($email)) {
//                $customer = $this->createMagentoUserFromApiUser($email, $pass, $apiLoginResult['data']);
//            } else {
//                try {
//                    // Update Magento User With API One
//                    $this->updateMagentoUserFromApiUser($customer,$apiLoginResult['data']);
//                } catch (\Exception $e) {
//                    // Add Customer Message send
//                    if($e->getMessage() !=  'The email and account combination does not exist.') {
//                        throw $e;
//                    }
//                    // Ignore ...
//                }
//            }
            
            // - DEFAULT - Validations
            $customerId = $customer->getId();
            if ($this->getAuthentication()->isLocked($customerId)) {
                throw new UserLockedException(__('The account is locked.'));
            }
            
//            if ($customer->getConfirmation() && $this->isConfirmationRequired($customer)) {
//                throw new EmailNotConfirmedException(__('This account is not confirmed.'));
//            }

            $customerModel = $this->customerFactory->create()->updateData($customer);
            $this->_eventManager->dispatch(
                'customer_customer_authenticated',
                ['model' => $customerModel, 'password' => $pass]
            );
            
            $this->_eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);
    
            return $customer;
            
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            throw $e;
        }
    
    }
    
    public function getCustomerGroupId(CustomerInterface $customer)
    {
        $store = $this->storeManager->getStore();
        $defaultId = ($this->groupManagement->getDefaultGroup($store->getId())->getId());
        $wholesaleId = $defaultId;
        
        foreach ($this->groupManagement->getLoggedInGroups() as $group) {
            if(strtolower($group->getCode()) == 'wholesale') {
                $wholesaleId = $group->getId();
                break;
            }
        }
        return ($this->isSpecialPriceAuthorized($customer)) ? $wholesaleId : $defaultId;
    }
    
    public function updateMagentoUserFromApiUser(CustomerInterface $customer,$loginData)
    {
        try {

            $apiCustomerData = $this->getApiCustomerFullData($customer->getEmail(),$loginData);
            $customerData = $this->mapApiCustomerData($apiCustomerData);
            
            // Remove Address
            // - We wont update Addresses on login -
            $addresses = $customerData['addresses'];
            unset($customerData['addresses']);
            
            // Update
            $this->dataObjectHelper->populateWithArray(
                $customer,
                $customerData,
                '\Magento\Customer\Api\Data\CustomerInterface'
            );
            
            $customer->setGroupId($this->getCustomerGroupId($customer));
            
            $this->customerRepository->save($customer);
        
        } catch (IncompleteUserException $e) {
            
            // Allow Incomplete customers
        
        } catch (\Exception $e) {
            throw $e;
        }
        
        return $this;
        
    }
    
    /**
     * @param $email
     * @param $pass
     * @param $loginData
     * @return CustomerInterface
     * @throws IncompleteUserException
     * @throws InvalidEmailOrPasswordException
     * @throws LocalizedException
     */
    public function createMagentoUserFromApiUser($email,$pass,$loginData)
    {
        $apiCustomerData = $this->getApiCustomerFullData($email,$loginData);
        $magentoCustomerData = $this->mapApiCustomerData($apiCustomerData);
        $redirectUrl = $this->customerSession->getBeforeAuthUrl();
        $store = $this->storeManager->getStore();
        
        $customerModel = $this->customerFactory->create();
        $customerDataObject = $customerModel->getDataModel();
        
        // Addresses
        $addresses = $this->extractAddresses($magentoCustomerData);
        unset($magentoCustomerData['addresses']);
        
        /* @var $customerDataObject \Magento\Customer\Model\Data\Customer */
        $this->dataObjectHelper->populateWithArray(
            $customerDataObject,
            $magentoCustomerData,
            '\Magento\Customer\Api\Data\CustomerInterface'
        );
    
        // Common Data
        $customerDataObject->setAddresses($addresses);
        $customerDataObject->setGroupId($this->getCustomerGroupId($customerDataObject));
        $customerDataObject->setWebsiteId($store->getWebsiteId());
        $customerDataObject->setStoreId($store->getId());
        
        // Account Create
        /* @var $customerDataObject \Magento\Customer\Model\Customer */
        $customer = $this->accountManagement->createAccount(
            $customerDataObject, 'm4G3NT0_' . $pass, $redirectUrl
        );
        
        $this->_eventManager->dispatch(
            'customer_register_success',
            ['account_controller' => null, 'customer' => $customer]
        );
        
        return $customer;
        
    }
    
    
    /**
     * @param $email
     * @param $pass
     * @param $loginData
     * @return CustomerInterface
     * @throws IncompleteUserException
     * @throws InvalidEmailOrPasswordException
     * @throws LocalizedException
     */
//    public function createMagentoUserFromApiUser($email,$pass,$loginData)
//    {
//        $apiCustomerData = $this->getApiCustomerFullData($email,$loginData);
//        $magentoCustomerData = $this->mapApiCustomerData($apiCustomerData);
//        $redirectUrl = $this->customerSession->getBeforeAuthUrl();
//        $store = $this->storeManager->getStore();
//
//        $customerModel = $this->customerFactory->create();
//        $customerDataObject = $customerModel->getDataModel();
//
//        // Addresses
//        $addresses = $this->extractAddresses($magentoCustomerData);
//        unset($magentoCustomerData['addresses']);
//
//        /* @var $customerDataObject \Magento\Customer\Model\Data\Customer */
//        $this->dataObjectHelper->populateWithArray(
//            $customerDataObject,
//            $magentoCustomerData,
//            '\Magento\Customer\Api\Data\CustomerInterface'
//        );
//
//        // Common Data
//        $customerDataObject->setAddresses($addresses);
//        $customerDataObject->setGroupId($this->getCustomerGroupId($customerDataObject));
//        $customerDataObject->setWebsiteId($store->getWebsiteId());
//        $customerDataObject->setStoreId($store->getId());
//
//        // Account Create
//        /* @var $customerDataObject \Magento\Customer\Model\Customer */
//        $customer = $this->accountManagement->createAccount(
//            $customerDataObject, 'm4G3NT0_' . $pass, $redirectUrl
//        );
//
//        $this->_eventManager->dispatch(
//            'customer_register_success',
//            ['account_controller' => null, 'customer' => $customer]
//        );
//
//        return $customer;
//
//    }
    
    /**
     * @param AddressInterface $address
     * @return array
     */
    public function getApiAddressDataFromMagentoAddress(AddressInterface $address)
    {
        $streetArr = $address->getStreet();
        $street1 = $streetArr[0];
        $street2 = isset($streetArr[1]) ? $streetArr[1] : '';
        $country = ($address->getCountryId() == 'CA') ? 'CAD' : 'USD';
        $regionModel = $this->regionFactory->create()
            ->load($address->getRegion()->getRegionId());
        $regionName = ($regionModel->getId()) ? $regionModel->getName() : '';
        
        $data = [
            'Address1' => $street1,
            'Address2' => $street2,
            'AddressID' => $this->getAddressTmgId($address),
            'BillingAddress' => $address->isDefaultBilling(),
            'City' => $address->getCity(),
            'Country' => $country,
            'PrimaryAddress' => $address->isDefaultBilling(),
            'DefaultBillingAddress' => $address->isDefaultBilling(),
            'DefaultShippingAddress' => $address->isDefaultShipping(),
            'FirstName' => $address->getFirstName(),
            'LastName' => $address->getLastname(),
            'ShippingAddress' => $address->isDefaultShipping(),
            'State' => $regionName,
            'ZipCode' => $address->getPostcode(),
        ];
        
        return $data;
        
    }
    
    /**
     * @param CustomerInterface $customer
     * @param null $password
     * @return array
     */
    public function prepareCreateApiLoginData(CustomerInterface $customer, $password = null)
    {
        // Address Data
        $addressData = $this->getApiAddressDataFromMagentoAddress($customer->getAddresses()[0]);
        
        // Customer Account Data
        switch(true) {
            case (bool)$customer->getCustomAttribute(ConfigHelper::ATTRIBUTE_MAGNET_ACCOUNT_ID)->getValue():
                $accountId = $customer->getCustomAttribute(ConfigHelper::ATTRIBUTE_MAGNET_ACCOUNT_ID)->getValue();
                $idType = $this->getAccountTypeId('magnet');
                break;
            case (bool)$customer->getCustomAttribute(ConfigHelper::ATTRIBUTE_ASI_ACCOUNT_ID)->getValue():
                $accountId = $customer->getCustomAttribute(ConfigHelper::ATTRIBUTE_ASI_ACCOUNT_ID)->getValue();
                $idType = $this->getAccountTypeId('asi');
                break;
            case (bool)$customer->getCustomAttribute(ConfigHelper::ATTRIBUTE_PPAI_ACCOUNT_ID)->getValue():
                $accountId = $customer->getCustomAttribute(ConfigHelper::ATTRIBUTE_PPAI_ACCOUNT_ID)->getValue();
                $idType = $this->getAccountTypeId('ppai');
                break;
            case (bool)$customer->getCustomAttribute(ConfigHelper::ATTRIBUTE_SAGE_ACCOUNT_ID)->getValue():
                $accountId = $customer->getCustomAttribute(ConfigHelper::ATTRIBUTE_SAGE_ACCOUNT_ID)->getValue();
                $idType = $this->getAccountTypeId('sage');
                break;
            default:
                $accountId = '';
                $idType = '';
        }
        
        $data = [
            'Account' => $customer->getCustomAttribute(ConfigHelper::ATTRIBUTE_ACCOUNT_ID)->getValue() ?: '',
            'AccountID' => $accountId,
            'Address1' => $addressData['Address1'],
            'Address2' => $addressData['Address2'],
            'BillingAddress' => $addressData['BillingAddress'],
            'City' => $addressData['City'],
            'CompanyName' => $customer->getCustomAttribute(ConfigHelper::ATTRIBUTE_COMPANY_NAME)->getValue(),
            'Country' => $addressData['Country'],
            'DefaultBillingAddress' => $addressData['DefaultBillingAddress'],
            'DefaultShippingAddress' => $addressData['DefaultShippingAddress'],
            'Fax' => $customer->getCustomAttribute(ConfigHelper::ATTRIBUTE_FAX)->getValue(),
            'FirstName' => $customer->getFirstName(),
            'IDType' => $idType,
            'LastName' => $customer->getLastname(),
            'Password' => $password,
            'ShippingAddress' => $addressData['ShippingAddress'],
            'State' => $addressData['State'],
            'User' => $customer->getEmail(),
            'WorkPhone' => $customer->getCustomAttribute(ConfigHelper::ATTRIBUTE_TELEPHONE)->getValue(),
            'ZipCode' => $addressData['ZipCode'],
        ];
        return $data;
    }
    
    /**
     * @param $customer
     * @param $pass
     * @return array
     * @throws \Exception
     */
    public function createApiLoginFromMagentoUser($customer, $pass)
    {
        $data = $this->prepareCreateApiLoginData($customer,$pass);
        try {
            $result =  $this->createApiLogin($data);
        } catch (\Exception $e) {
            // If customer Exists thrown alreadyExist Exception
            $this->_logger->critical($e);
            throw $e;
        }
        return $result;
    }
    
    /**
     * @param CustomerInterface $customer
     * @return array
     */
    public function prepareUpdateApiAccountData(CustomerInterface $customer)
    {
        
        $addresses = [
            'Address' => []
        ];
        
        $existPrimaryAddress = false;
        
        foreach ($customer->getAddresses() as $address) {
            $mappedAddress = $this->getApiAddressDataFromMagentoAddress($address);
            if($mappedAddress['PrimaryAddress']) {
                // Validate Unique
                if($existPrimaryAddress) {
                    $mappedAddress['PrimaryAddress'] = false;
                    continue;
                }
                $existPrimaryAddress = true;
            }
            $addresses['Address'][] = $mappedAddress;
        }
        
        // Primary Address Error Handling
        if(!$existPrimaryAddress) {
            $addresses['Address'][0]['PrimaryAddress'] = true;
        }
        
        $data = [
            'Addresses' => $addresses,
            'CatalogCounts' => '',
            'DoNotEmail' => false,
            'DoNotSolicit' => false,
            'Email' => $customer->getEmail(),
            'Email2' => '',
            'Email3' => '',
            'EncryptAccount' => $this->getEncryptAccount($customer),
            'Fax' => $this->getCustomAttributeValue(ConfigHelper::ATTRIBUTE_FAX,$customer),
            'FirstName' => $customer->getFirstname(),
            'LastName' => $customer->getLastname(),
            'MobilePhone' => '',
            'SLXContactID' => $this->getCustomAttributeValue(ConfigHelper::ATTRIBUTE_SLX_CONTACT_ID, $customer),
            'WebSite' => '',
            'WorkPhone' => $this->getCustomAttributeValue(ConfigHelper::ATTRIBUTE_TELEPHONE,$customer),
        ];
        
        return $data;
        
    }
    
    /**
     * @param CustomerInterface $customer
     * @return $this
     * @throws IncompleteUserException
     * @throws LocalizedException
     */
    public function updateApiAccountFromMagentoUser(CustomerInterface $customer)
    {
        if(!$encryptAccount = $this->getEncryptAccount($customer)) {
            throw new IncompleteUserException(__('Your account should be approved and configured on our system in order to be updated.'));
        }
        
        $data = $this->prepareUpdateApiAccountData($customer);
        
        $this->contactService->doUpdateContactDataRequest($data);
            
        return $this;
        
    }
    
    
    public function updateApiAddressFromMagentoAddress(AddressInterface $address)
    {
        $customer = $this->customerRepository->getById($address->getCustomerId());
        
        // Prepare Data
        if($addressId = $address->getId()) {
            foreach ($customer->getAddresses() as $k => $customerAddress) {
                if($customerAddress->getId() == $addressId) {
                    $this->dataObjectHelper->mergeDataObjects(AddressInterface::class,$customerAddress,$address);
                }
            }
        } else {
            $customerAddresses = $customer->getAddresses();
            $customerAddresses[] = $address;
            $customer->setAddresses(array_merge($customerAddresses));
        }
        
        // Send To API
        $this->updateApiAccountFromMagentoUser($customer);
        
        // Add ID for New Address
        if(isset($customerAddresses)) {
            // Existent IDs
            $addressTmgIds = [];
            foreach($customerAddresses as $address) {
                if($addressTmgId = $this->getAddressTmgId($address)) {
                    $addressTmgIds[] = $addressTmgId;
                }
            }
            // Call Get Data
            $contactData = $this->getApiContactData($customer->getEmail(),$this->getEncryptAccount($customer));
            foreach ($contactData['data']['Addresses'] as $addressData) {
                if(!in_array($addressData['AddressID'],$addressTmgIds)) {
                    $address->setCustomAttribute(ConfigHelper::ATTRIBUTE_ADDRESS_ID,$addressData['AddressID']);
                }
            }
        }
        
    }
    
    public function compareAddresses($apiAddress, $magentoAddress)
    {
        return true;
    }
   
    
    /**
     * @param $email
     * @param $oldPwd
     * @param $newPwd
     * @return $this
     * @throws \Exception
     */
    public function changeApiUserPassword($email,$oldPwd,$newPwd)
    {
        $data = [
            'NewPassword' => $newPwd,
            'OldPassword' => $oldPwd,
            'User' => $email,
        ];
        try {
    
            $result = $this->customerSecurityService->doChangePwdRequest($data);
            if (@!$result['success']) {
                throw new InvalidEmailOrPasswordException(__($result['ErrorMessage']));
            }
    
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            throw $e;
        }
        
        return $this;
        
    }
    
    public function createApiResetPwdRequest($email)
    {
        // Validate Email
        $this->validateEmail($email);
        
        // Validate Email Account
        if(!$mailExist = $this->customerSecurityService->doValidAccountEmailRequest($email)) {
            throw new NoSuchEntityException(__('Invalid Email Account'));
        }
        
        return $this->customerSecurityService->doResetPwdRequestRequest($email);
        
    }
    
    /**
     * @ToDo
     *
     * @param $requestId
     * @return bool
     */
    public function validateResetPwdRequestIdFormat($requestId)
    {
        return true;
    }
    
    /**
     * @param $requestId
     * @return mixed
     * @throws LocalizedException
     */
    public function validateApiResetPwdRequestId($requestId)
    {
        $this->validateResetPwdRequestIdFormat($requestId);
        return $this->customerSecurityService->doCheckPwdRequest($requestId);
    }
    
    /**
     * @param $requestId
     * @param $password
     * @return bool
     * @throws LocalizedException
     * @throws \TMG\Customer\Exception\CustomerSecurityServiceException
     */
    public function resetApiPwd($requestId,$password)
    {
        return $this->customerSecurityService->doResetPwdRequest($requestId,$password);
    }
    
    public function getCustomerErrorMessage($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'tmg_customer/tmg_customer_error/error_message', ScopeInterface::SCOPE_STORE, $storeId
        );
    }


    
    
}