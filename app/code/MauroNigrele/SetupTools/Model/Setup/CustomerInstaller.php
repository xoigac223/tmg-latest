<?php

namespace MauroNigrele\SetupTools\Model\Setup;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\Api\Search\SearchCriteriaFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;


use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

use Psr\Log\LoggerInterface;

/**
 * Class CustomerInstaller
 * @package MauroNigrele\SetupTools\Model\Setup
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerInstaller extends AbstractInstaller
{
    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;
    
    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;
    
    /**
     * @var AttributeSetFactory
     */
    protected $attributeSetFactory;
    
    /**
     * @var CustomerSetup
     */
    protected $customerSetup;
    
    /**
     * @var int
     */
    protected $defaultCustomerAttributeSetId;
    
    /**
     * @var int
     */
    protected $defaultAddressAttributeSetId;
    
    /**
     * @var int
     */
    protected $defaultCustomerAttributeGroupId;
    
    /**
     * @var int
     */
    protected $defaultAddressAttributeGroupId;
    
    
    /**
     * @var CustomerRepository
     */
    protected $customerRepository;
    
    /**
     * @return CustomerRepository
     */
    public function getCustomerRepository()
    {
        return $this->customerRepository;
    }
    
    
    /**
     * @var array
     *
     * - More Options:
     *      'adminhtml_checkout'
     *      'adminhtml_customer'
     *      'adminhtml_customer_address'
     *      'customer_account_edit'
     *      'customer_address_edit'
     *      'customer_register_address'
     */
    protected $customerAttributeDefaultForms = [
        'adminhtml_customer'
    ];
    protected $addressAttributeDefaultForms = [
        'adminhtml_customer_address'
    ];

    public function __construct(
        ObjectManagerInterface $objectManager,
        Registry $registry,
        LoggerInterface $logger,
        ScopeConfigInterface $config,
        WriterInterface $configWriter,
        CustomerSetupFactory $customerSetupFactory,
        AttributeRepository $attributeRepository,
        AttributeSetFactory $attributeSetFactory,
        CustomerRepository $customerRepository,
        SearchCriteriaFactory $searchCriteriaFactory
    ) {
        parent::__construct($objectManager, $registry, $logger, $config, $configWriter);
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeRepository = $attributeRepository;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
    }
    
    protected $searchCriteriaFactory;
    
    /**
     * @return \Magento\Framework\Api\Search\SearchCriteriaInterface
     */
    public function getNewSearchCriteria()
    {
        return $this->searchCriteriaFactory->create();
    }
    
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteAllCustomers()
    {
        foreach ($this->getCustomerRepository()->getList($this->getNewSearchCriteria())->getItems() as $customer) {
            $this->deleteCustomer($customer);
        }
    }
    
    public function deleteCustomer($customer)
    {
        if(is_numeric($customer)) {
            $this->customerRepository->deleteById($customer);
        }
        if($customer instanceof CustomerInterface || $customer instanceof Customer) {
            $this->customerRepository->delete($customer);
        }
        return $this;
    }
    
    /**
     * @return \Magento\Customer\Setup\CustomerSetup
     * @throws \Exception
     */
    public function getCustomerSetup()
    {
        if(!$this->moduleDataSetup) {
            throw new \Exception('Data Setup not defined');
        }
        if(!$this->customerSetup) {
            $this->customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        }
        return $this->customerSetup;
    }
    
    public function getEavConfig()
    {
        return $this->getCustomerSetup()->getEavConfig();
    }
    
    public function getCustomerAttribute($code)
    {
//        $this->attributeRepository->get('customer', $code);
        return $this->getEavConfig()
            ->getAttribute('customer', $code);
    }
    
    public function getAddressAttribute($code)
    {
//        $this->attributeRepository->get('customer_address', $code);
        return $this->getEavConfig()
            ->getAttribute('customer_address', $code);
    }
    
    
    public function getDefaultCustomerAttributeSetId()
    {
        if(!$this->defaultCustomerAttributeSetId) {
            $this->defaultCustomerAttributeSetId = $this->getEavConfig()
                ->getEntityType('customer')
                ->getDefaultCustomerAttributeSetId();
        }
        return $this->defaultCustomerAttributeSetId;
    }
    
    public function getDefaultCustomerAttributeGroupId()
    {
        if(!$this->defaultCustomerAttributeGroupId) {
            /** @var $attributeSet AttributeSet */
            $attributeSet = $this->attributeSetFactory->create();
            $this->defaultCustomerAttributeGroupId = $attributeSet->getDefaultGroupId($this->getDefaultCustomerAttributeSetId());
        }
        return $this->defaultCustomerAttributeGroupId;
    }
    public function getDefaultAddressAttributeSetId()
    {
        if(!$this->defaultAddressAttributeSetId) {
            $this->defaultAddressAttributeSetId = $this->getEavConfig()
                ->getEntityType('customer_address')
                ->getDefaultAttributeSetId();
        }
        return $this->defaultAddressAttributeSetId;
    }
    
    public function getDefaultAddressAttributeGroupId()
    {
        if(!$this->defaultAddressAttributeGroupId) {
            /** @var $attributeSet AttributeSet */
            $attributeSet = $this->attributeSetFactory->create();
            $this->defaultAddressAttributeGroupId = $attributeSet->getDefaultGroupId($this->getDefaultAddressAttributeSetId());
        }
        return $this->defaultAddressAttributeGroupId;
    }
    
    /**
     * @param $code
     * @param $configuration
     * @return $this
     * @throws \Exception
     *
     * @todo remove repetition implement Common Attribute Installer
     */
    public function  addCustomerAttribute($code, $configuration)
    {
        $forms = isset($configuration['used_in_forms']) ? $configuration['used_in_forms']
            : $this->customerAttributeDefaultForms;
        
        $this->getCustomerSetup()->addAttribute(
            'customer',
            $code,
            $configuration
        );

        $attribute = $this->getCustomerAttribute($code);
        $attribute->addData([
            'attribute_set_id' => $this->getDefaultCustomerAttributeSetId(),
            'attribute_group_id' => $this->getDefaultCustomerAttributeGroupId(),
            'used_in_forms' => $forms,
        ]);
        
//        var_dump($attribute->getAttributeId(),$attribute->getAttributeCode());
        
        $attribute->save();
        // Repository save Doesn't work :(
//        $this->attributeRepository->save($attribute);
        return $this;
    }
    
    /**
     * @param $code
     * @param $configuration
     * @return $this
     * @throws \Exception
     *
     * @todo remove repetition implement Common Attribute Installer
     */
    public function addAddressAttribute($code, $configuration)
    {
        $forms = isset($configuration['used_in_forms']) ? $configuration['used_in_forms']
            : $this->addressAttributeDefaultForms;
        
        $this->getCustomerSetup()->addAttribute(
            'customer_address',
            $code,
            $configuration
        );

        $attribute = $this->getAddressAttribute($code);
        $attribute->addData([
            'attribute_set_id' => $this->getDefaultAddressAttributeSetId(),
            'attribute_group_id' => $this->getDefaultAddressAttributeGroupId(),
            'used_in_forms' => $forms,
        ]);
        
        // Repository save Doesn't work :(
        $attribute->save();
//        $this->attributeRepository->save($attribute);
        
        return $this;
    }
    
}