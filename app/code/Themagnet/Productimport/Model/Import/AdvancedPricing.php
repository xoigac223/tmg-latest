<?php
namespace Themagnet\Productimport\Model\Import;
 
class AdvancedPricing extends \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing
{
	public function deleteProductPrices($listSku)
    {
    	parent::deleteProductTierPrices($listSku, \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing::TABLE_TIER_PRICE);
    }
}