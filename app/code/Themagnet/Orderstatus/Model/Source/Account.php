<?php

namespace Themagnet\Orderstatus\Model\Source;

class Account implements \Magento\Framework\Option\ArrayInterface
{

    protected $_customer;
    protected $_customerFactory;

    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Customer $customers
    )
    {
        $this->_customerFactory = $customerFactory;
        $this->_customer = $customers;
    }

    public function getOptionArray()
    {
        $customers = $this->_customerFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                ->load();
        $options = array();
        foreach($customers as $customer){
            if($customer->getTmgEncryptAccount() != ''){
                $options[$customer->getId()] = $customer->getFirstname().' '.$customer->getLastname();
            }
        }
        return $options;
    }
    
    public function getAllOptions()
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);
        return $res;
    }
    
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }
     
    public function toOptionArray()
    {
        return $this->getOptions();
    }

}