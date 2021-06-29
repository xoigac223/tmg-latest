<?php
namespace TMG\Customer\Model\Rewrite\Customer\Data;

class Address extends \Magento\Customer\Model\Data\Address
    implements \TMG\Customer\Api\Data\AddressInterface
{
    
    /**
     * @return mixed
     */
    public function getTmgAddressId()
    {
        return $this->_get(SELF::TMG_ADDRESS_ID);
    }
    
    /**
     * @param $tmgAddressId
     * @return mixed
     */
    public function setTmgAddressId($tmgAddressId)
    {
        return $this->setData(self::TMG_ADDRESS_ID,$tmgAddressId);
    }
    
}