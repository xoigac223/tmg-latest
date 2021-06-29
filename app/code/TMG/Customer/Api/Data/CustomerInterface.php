<?php

namespace TMG\Customer\Api\Data;

/**
 * Interface CustomerInterface
 * @package TMG\Customer\Api\Data
 */
interface CustomerInterface extends \Magento\Customer\Api\Data\CustomerInterface
{
    const TMG_ENCRYPT_ACCOUNT = 'tmg_encrypt_account';
    const TMG_USER_ID = 'tmg_user_id';
    const TMG_ACCOUNT_ID = 'tmg_account_id';
    const TMG_TELEPHONE = 'tmg_telephone';
    const TMG_FAX = 'tmg_fax';
    const TMG_COMPANY_NAME = 'tmg_company_name';
    const TMG_MAGNET_ACCOUNT_ID = 'tmg_magnet_account_id';
    const TMG_ASI_ACCOUNT_ID = 'tmg_asi_account_id';
    const TMG_PPAI_ACCOUNT_ID = 'tmg_ppai_account_id';
    const TMG_SAGE_ACCOUNT_ID = 'tmg_sage_account_id';
    const TMG_FTP_AUTHORIZED = 'tmg_ftp_authorized';
    const TMG_EPAY_AUTHORIZED = 'tmg_epay_authorized';
    const TMG_SALES_TAX_RATES = 'tmg_sales_tax_rates';
    const TMG_SPECIAL_PRICING_AUTHORIZED = 'tmg_special_pricing_authorized';
    const TMG_CHARGE_FREIGHT_HANDLING = 'tmg_charge_freight_handling';
    
    /**
     * @return mixed
     */
    public function getTmgEncryptAccount();
    
    /**
     * @param $tmgEncryptAccount
     * @return mixed
     */
    public function setTmgEncryptAccount($tmgEncryptAccount);
    
    /**
     * @return mixed
     */
    public function getTmgUserId();
    
    /**
     * @param $tmgUserId
     * @return mixed
     */
    public function setTmgUserId($tmgUserId);
    
    /**
     * @return mixed
     */
    public function getTmgAccountId();
    
    /**
     * @param $tmgAccountId
     * @return mixed
     */
    public function setTmgAccountId($tmgAccountId);
    
    /**
     * @return mixed
     */
    public function getTmgSalesTaxRates();
    
    /**
     * @param $tmgSalesTaxRates
     * @return mixed
     */
    public function setTmgSalesTaxRates($tmgSalesTaxRates);
    
    /**
     * @return mixed
     */
    public function getTmgTelephone();
    
    /**
     * @param $tmgTelephone
     * @return mixed
     */
    public function setTmgTelephone($tmgTelephone);
    
    /**
     * @return mixed
     */
    public function getTmgFax();
    
    /**
     * @param $tmgFax
     * @return mixed
     */
    public function setTmgFax($tmgFax);
    
    /**
     * @return mixed
     */
    public function getTmgCompanyName();
    
    /**
     * @param $tmgCompanyName
     * @return mixed
     */
    public function setTmgCompanyName($tmgCompanyName);
    
    /**
     * @return mixed
     */
    public function getTmgMagnetAccountId();
    
    /**
     * @param $tmgMagnetAccountId
     * @return mixed
     */
    public function setTmgMagnetAccountId($tmgMagnetAccountId);
    
    /**
     * @return mixed
     */
    public function getTmgAsiAccountId();
    
    /**
     * @param $tmgAsiAccountId
     * @return mixed
     */
    public function setTmgAsiAccountId($tmgAsiAccountId);
    
    /**
     * @return mixed
     */
    public function getTmgPpaiAccountId();
    
    /**
     * @param $tmgPpaiAccountId
     * @return mixed
     */
    public function setTmgPpaiAccountId($tmgPpaiAccountId);
    
    /**
     * @return mixed
     */
    public function getTmgSageAccountId();
    
    /**
     * @param $tmgSageAccountId
     * @return mixed
     */
    public function setTmgSageAccountId($tmgSageAccountId);
    
    /**
     * @return mixed
     */
    public function getTmgFtpAuthorized();
    
    /**
     * @param $tmgFtpAuthorized
     * @return mixed
     */
    public function setTmgFtpAuthorized($tmgFtpAuthorized);
    
    /**
     * @return mixed
     */
    public function getTmgEpayAuthorized();
    
    /**
     * @param $tmgEpayAuthorized
     * @return mixed
     */
    public function setTmgEpayAuthorized($tmgEpayAuthorized);
    
    /**
     * @return mixed
     */
    public function getTmgSpecialPricingAuthorized();
    
    /**
     * @param $tmgSpecialPricingAuthorized
     * @return mixed
     */
    public function setTmgSpecialPricingAuthorized($tmgSpecialPricingAuthorized);
    
    /**
     * @return mixed
     */
    public function getTmgChargeFreightHandling();
    
    /**
     * @param $tmgChargeFreightHandling
     * @return mixed
     */
    public function setTmgChargeFreightHandling($tmgChargeFreightHandling);
    
}