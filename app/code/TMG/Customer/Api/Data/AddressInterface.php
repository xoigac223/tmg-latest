<?php

namespace TMG\Customer\Api\Data;

interface AddressInterface extends \Magento\Customer\Api\Data\AddressInterface
{
    const TMG_ADDRESS_ID = 'tmg_address_id';
    
    /**
     * @return mixed
     */
    public function getTmgAddressId();
    
    /**
     * @param $tmgAddressId
     * @return mixed
     */
    public function setTmgAddressId($tmgAddressId);
    
}