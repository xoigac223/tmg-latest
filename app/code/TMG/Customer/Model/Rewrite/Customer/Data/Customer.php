<?php

namespace TMG\Customer\Model\Rewrite\Customer\Data;

class Customer extends \Magento\Customer\Model\Data\Customer
    implements \TMG\Customer\Api\Data\CustomerInterface
{
    
    /**
     * @return mixed
     */
    public function getTmgEncryptAccount()
    {
        return $this->_get(self::TMG_ENCRYPT_ACCOUNT);
    }
    
    /**
     * @param $tmgEncryptAccount
     * @return mixed
     */
    public function setTmgEncryptAccount($tmgEncryptAccount)
    {
        return $this->setData(self::TMG_ENCRYPT_ACCOUNT,$tmgEncryptAccount);
    }
    
    /**
     * @return mixed
     */
    public function getTmgUserId()
    {
        return $this->_get(self::TMG_USER_ID);
    }
    
    /**
     * @param $tmgUserId
     * @return mixed
     */
    public function setTmgUserId($tmgUserId)
    {
        return $this->setData(self::TMG_USER_ID,$tmgUserId);
    }
    
    /**
     * @return mixed
     */
    public function getTmgAccountId()
    {
        return $this->_get(self::TMG_ACCOUNT_ID);
    }
    
    /**
     * @param $tmgAccountId
     * @return mixed
     */
    public function setTmgAccountId($tmgAccountId)
    {
        return $this->setData(self::TMG_ACCOUNT_ID,$tmgAccountId);
    }
    
    /**
     * @return mixed
     */
    public function getTmgSalesTaxRates()
    {
        return $this->_get(self::TMG_SALES_TAX_RATES);
    }
    
    /**
     * @param $tmgSalesTaxRates
     * @return mixed
     */
    public function setTmgSalesTaxRates($tmgSalesTaxRates)
    {
        return $this->setData(self::TMG_SALES_TAX_RATES,$tmgSalesTaxRates);
    }
    
    /**
     * @return mixed
     */
    public function getTmgTelephone()
    {
        return $this->_get(self::TMG_TELEPHONE);
    }
    
    /**
     * @param $tmgTelephone
     * @return mixed
     */
    public function setTmgTelephone($tmgTelephone)
    {
        return $this->setData(self::TMG_TELEPHONE,$tmgTelephone);
    }
    
    /**
     * @return mixed
     */
    public function getTmgFax()
    {
        return $this->_get(self::TMG_FAX);
    }
    
    /**
     * @param $tmgFax
     * @return mixed
     */
    public function setTmgFax($tmgFax)
    {
        return $this->setData(self::TMG_FAX,$tmgFax);
    }
    
    /**
     * @return mixed
     */
    public function getTmgCompanyName()
    {
        return $this->_get(self::TMG_COMPANY_NAME);
    }
    
    /**
     * @param $tmgCompanyName
     * @return mixed
     */
    public function setTmgCompanyName($tmgCompanyName)
    {
        return $this->setData(self::TMG_COMPANY_NAME,$tmgCompanyName);
    }
    
    /**
     * @return mixed
     */
    public function getTmgMagnetAccountId()
    {
        return $this->_get(self::TMG_MAGNET_ACCOUNT_ID);
    }
    
    /**
     * @param $tmgMagnetAccountId
     * @return mixed
     */
    public function setTmgMagnetAccountId($tmgMagnetAccountId)
    {
        return $this->setData(self::TMG_MAGNET_ACCOUNT_ID,$tmgMagnetAccountId);
    }
    
    /**
     * @return mixed
     */
    public function getTmgAsiAccountId()
    {
        return $this->_get(self::TMG_ASI_ACCOUNT_ID);
    }
    
    /**
     * @param $tmgAsiAccountId
     * @return mixed
     */
    public function setTmgAsiAccountId($tmgAsiAccountId)
    {
        return $this->setData(self::TMG_ASI_ACCOUNT_ID,$tmgAsiAccountId);
    }
    
    /**
     * @return mixed
     */
    public function getTmgPpaiAccountId()
    {
        return $this->_get(self::TMG_PPAI_ACCOUNT_ID);
    }
    
    /**
     * @param $tmgPpaiAccountId
     * @return mixed
     */
    public function setTmgPpaiAccountId($tmgPpaiAccountId)
    {
        return $this->setData(self::TMG_PPAI_ACCOUNT_ID,$tmgPpaiAccountId);
    }
    
    /**
     * @return mixed
     */
    public function getTmgSageAccountId()
    {
        return $this->_get(self::TMG_SAGE_ACCOUNT_ID);
    }
    
    /**
     * @param $tmgSageAccountId
     * @return mixed
     */
    public function setTmgSageAccountId($tmgSageAccountId)
    {
        return $this->setData(self::TMG_SAGE_ACCOUNT_ID,$tmgSageAccountId);
    }
    
    /**
     * @return mixed
     */
    public function getTmgFtpAuthorized()
    {
        return $this->_get(self::TMG_FTP_AUTHORIZED);
    }
    
    /**
     * @param $tmgFtpAuthorized
     * @return mixed
     */
    public function setTmgFtpAuthorized($tmgFtpAuthorized)
    {
        return $this->setData(self::TMG_FTP_AUTHORIZED,$tmgFtpAuthorized);
    }
    
    /**
     * @return mixed
     */
    public function getTmgEpayAuthorized()
    {
        return $this->_get(self::TMG_EPAY_AUTHORIZED);
    }
    
    /**
     * @param $tmgEpayAuthorized
     * @return mixed
     */
    public function setTmgEpayAuthorized($tmgEpayAuthorized)
    {
        return $this->setData(self::TMG_EPAY_AUTHORIZED,$tmgEpayAuthorized);
    }
    
    /**
     * @return mixed
     */
    public function getTmgSpecialPricingAuthorized()
    {
        return $this->_get(self::TMG_SPECIAL_PRICING_AUTHORIZED);
    }
    
    /**
     * @param $tmgSpecialPricingAuthorized
     * @return mixed
     */
    public function setTmgSpecialPricingAuthorized($tmgSpecialPricingAuthorized)
    {
        return $this->setData(self::TMG_SPECIAL_PRICING_AUTHORIZED,$tmgSpecialPricingAuthorized);
    }
    
    /**
     * @return mixed
     */
    public function getTmgChargeFreightHandling()
    {
        return $this->_get(self::TMG_CHARGE_FREIGHT_HANDLING);
    }
    
    /**
     * @param $tmgChargeFreightHandling
     * @return mixed
     */
    public function setTmgChargeFreightHandling($tmgChargeFreightHandling)
    {
        return $this->setData(self::TMG_CHARGE_FREIGHT_HANDLING,$tmgChargeFreightHandling);
    }
}