<?php
namespace Themagnet\Productimport\Model;
 
class Additionalcontent extends \Magento\Framework\Model\AbstractModel
{
	protected $_value;
	protected $_rowValue;
	
	public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context , $registry);
    }

	public function setAdditionalValue($value, $rowValue)
	{
		$this->_value = $value;
		$this->_rowValue = $rowValue;
	}

	public function unsAdditionalValue()
	{
		$this->_value = array();
	}

	public function dropshipchg()
	{
		if(isset($this->_value['AdditionalChargeItemDescription']) && strtolower($this->_value['AdditionalChargeItemDescription']) == strtolower('Drop Shipment Charge')){
		    return isset($this->_value['AdditionalChargeCatalogPrice'])?$this->_value['AdditionalChargeCatalogPrice']:'';
		}
		return '';
	}

	public function handlingfee3rdparty()
	{
		if(isset($this->_value['AdditionalChargeItemDescription']) && strtolower($this->_value['AdditionalChargeItemDescription']) == strtolower('Handling Fee- 3rd Party Shipping')){
		    return isset($this->_value['AdditionalChargeCatalogPrice'])?$this->_value['AdditionalChargeCatalogPrice']:'';
		}
		return '';
	}

	public function less_than_minimum_qty()
	{
		if(isset($this->_value['AdditionalChargeItemDescription']) && strtolower($this->_value['AdditionalChargeItemDescription']) == strtolower('Less than Minimum Qty Chg')){
		    return isset($this->_value['AdditionalChargeCatalogPrice'])?$this->_value['AdditionalChargeCatalogPrice']:'';
		}
		return '';
	}

	public function dropshipnetchg()
	{
		if(isset($this->_value['AdditionalChargeItemDescription']) && strtolower($this->_value['AdditionalChargeItemDescription']) == strtolower('Drop Shipment Charge')){
		    return isset($this->_value['AdditionalChargeNetPrice'])?$this->_value['AdditionalChargeNetPrice']:'';
		}
		return '';
	}

	public function handlingfee3rdpartynetchg()
	{
		if(isset($this->_value['AdditionalChargeItemDescription']) && strtolower($this->_value['AdditionalChargeItemDescription']) == strtolower('Handling Fee- 3rd Party Shipping')){
		    return isset($this->_value['AdditionalChargeNetPrice'])?$this->_value['AdditionalChargeNetPrice']:'';
		}
		return '';
	}

	public function less_than_minimum_qty_netchg()
	{
		if(isset($this->_value['AdditionalChargeItemDescription']) && isset($this->_rowValue['PricingKey']) && strtolower($this->_value['AdditionalChargeItemDescription']) == strtolower('Handling Fee- 3rd Party Shipping') && strtolower($this->_rowValue['PricingKey']) == strtolower('RS')){
		    return $this->_value['AdditionalChargeNetPrice'];
		}
		return '';
	}

	public function additional_stitches_catalog_price_em()
	{
		return '';
	}

	public function digitizing_fee_catalog_price_em()
	{
		if(isset($this->_value['AdditionalChargeItemDescription']) && isset($this->_rowValue['PricingKey']) && strtolower($this->_value['AdditionalChargeItemDescription']) == strtolower('Personalization') && strtolower($this->_rowValue['PricingKey']) == strtolower('EM')){
		    return $this->_value['AdditionalChargeCatalogPrice'];
		}
		return '';
	}

	public function personalization_catalog_price_em()
	{
		if(isset($this->_value['AdditionalChargeItemDescription']) && isset($this->_rowValue['PricingKey']) && strtolower($this->_value['AdditionalChargeItemDescription']) == strtolower('Personalization') && strtolower($this->_rowValue['PricingKey']) == strtolower('EM')){
		    return $this->_value['AdditionalChargeCatalogPrice'];
		}
		return '';
	}

	public function swatchproof_catalog_price_em()
	{
		if(isset($this->_value['AdditionalChargeItemDescription']) && isset($this->_rowValue['PricingKey']) && strtolower($this->_value['AdditionalChargeItemDescription']) == strtolower('Swatch Proof') && strtolower($this->_rowValue['PricingKey']) == strtolower('EM')){
		    return $this->_value['AdditionalChargeCatalogPrice'];
		}
		return '';
	}

	public function additional_stitches_net_price_em()
	{
		return '';
	}

	public function digitizing_fee_net_price_em()
	{
		return '';
	}

	public function personalization_net_price_em()
	{
		if(isset($this->_value['AdditionalChargeItemDescription']) && isset($this->_rowValue['PricingKey']) && strtolower($this->_value['AdditionalChargeItemDescription']) == strtolower('Personalization') && strtolower($this->_rowValue['PricingKey']) == strtolower('EM')){
		    return $this->_value['AdditionalChargeNetPrice'];
		}
		return '';
	}

	public function swatchproof_net_price_em()
	{
		if(isset($this->_value['AdditionalChargeItemDescription']) && isset($this->_rowValue['PricingKey']) && strtolower($this->_value['AdditionalChargeItemDescription']) == strtolower('Swatch Proof') && strtolower($this->_rowValue['PricingKey']) == strtolower('EM')){
		    return $this->_value['AdditionalChargeNetPrice'];
		}
		return '';
	}

	public function setupchargecatalogprice_tp()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('TP')){
		    return $this->_rowValue['SetupChargeCatalogPrice'];
		}
		return '';
	}

	public function setupchargecatalogprice_t4()
	{
		if(isset($this->_rowValue['PricingKey']) && (strtolower($this->_rowValue['PricingKey']) == strtolower('TP') || strtolower($this->_rowValue['PricingKey']) == strtolower('TF') )){
		    return $this->_rowValue['SetupChargeCatalogPrice'];
		}
		return '';
	}

	public function setupchargecatalogprice_em()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('EM')){
		    return $this->_rowValue['SetupChargeCatalogPrice'];
		}
		return '';
	}

	public function setupchargecatalogprice_db()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('DB')){
		    return $this->_rowValue['SetupChargeCatalogPrice'];
		}
		return '';
	}

	public function setupchargecatalogprice_dm()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('DM')){
		    return $this->_rowValue['SetupChargeCatalogPrice'];
		}
		return '';
	}

	public function setupchargecatalogprice_cg()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('CG')){
		    return $this->_rowValue['SetupChargeCatalogPrice'];
		}
		return '';
	}

	public function netsetupcharge_tp()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('TP')){
		    return $this->_rowValue['NetSetupCharge'];
		}
		return '';
	}

	public function netsetupcharge_t4()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('T4')){
		    return $this->_rowValue['NetSetupCharge'];
		}
		return '';
	}

	public function netsetupcharge_em()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('EM')){
		    return $this->_rowValue['NetSetupCharge'];
		}
		return '';
	}

	public function netsetupcharge_dm()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('EM')){
		    return $this->_rowValue['NetSetupCharge'];
		}
		return '';
	}

	public function netsetupcharge_cg()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('CG')){
		    return $this->_rowValue['NetSetupCharge'];
		}
		return '';
	}

	public function netsetupcharge_db()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('DB')){
		    return $this->_rowValue['NetSetupCharge'];
		}
		return '';
	}

	public function setupchargedescription_tp()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('TP')){
		    return $this->_rowValue['SetupChargeDescription'];
		}
		return '';
	}

	public function setupchargedescription_t4()
	{
		if(isset($this->_rowValue['PricingKey']) && (strtolower($this->_rowValue['PricingKey']) == strtolower('T4') || strtolower($this->_rowValue['PricingKey']) == strtolower('TF'))){
		    return $this->_rowValue['SetupChargeDescription'];
		}
		return '';
	}

	public function setupchargedescription_em()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey'] == strtolower('EM'))){
		    return $this->_rowValue['SetupChargeDescription'];
		}
		return '';
		}

	public function setupchargedescription_dm()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey'] == strtolower('DM'))){
		    return $this->_rowValue['SetupChargeDescription'];
		}
		return '';
	}

	public function setupchargedescription_cg()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey'] == strtolower('CG'))){
		    return $this->_rowValue['SetupChargeDescription'];
		}
		return '';
	}

	public function minimumorderquantity_tp()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey'] == strtolower('TP'))){
		    return $this->_rowValue['SetupChargeDescription'];
		}
		return '';
	}

	public function minimumorderquantity_t4()
	{
		if(isset($this->_rowValue['PricingKey']) && (strtolower($this->_rowValue['PricingKey']) == strtolower('T4') || strtolower($this->_rowValue['PricingKey']) == strtolower('TF'))){
		    return $this->_rowValue['MinimumOrderQuantity'];
		}
		return '';
	}

	public function minimumorderquantity_em()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('EM')){
		    return $this->_rowValue['MinimumOrderQuantity'];
		}
		return '';
	}

	public function minimumorderquantity_cg()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('CG')){
		    return $this->_rowValue['MinimumOrderQuantity'];
		}
		return '';
	}

	public function minimumorderquantity_rs()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('RS')){
		    return $this->_rowValue['MinimumOrderQuantity'];
		}
		return '';
	}

	public function minimumorderquantity_bl()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('BL')){
		    return $this->_rowValue['MinimumOrderQuantity'];
		}
		return '';
	}

	public function minimumorderquantity_dm()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('DM')){
		    return $this->_rowValue['MinimumOrderQuantity'];
		}
		return '';
	}

	public function minimumorderquantity_db()
	{
		if(isset($this->_rowValue['PricingKey']) && strtolower($this->_rowValue['PricingKey']) == strtolower('DB')){
		    return $this->_rowValue['MinimumOrderQuantity'];
		}
		return '';
	}
}