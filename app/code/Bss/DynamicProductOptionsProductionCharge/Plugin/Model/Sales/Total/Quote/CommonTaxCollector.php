<?php

namespace Bss\DynamicProductOptionsProductionCharge\Plugin\Model\Sales\Total\Quote;

class CommonTaxCollector
{
    public function beforeUpdateItemTaxInfo($subject, $quoteItem, $itemTaxDetails, $baseItemTaxDetails, $store)
    {
        if($quoteItem->getPriceProductionCharge()) {
            $priceProductionCharge = 0;
            foreach($quoteItem->getPriceProductionCharge() as $price) {
                $priceProductionCharge += $price;
            }
            $itemTaxDetails->setRowTotal($itemTaxDetails->getRowTotal() + $priceProductionCharge);
            $itemTaxDetails->setRowTotalInclTax($itemTaxDetails->getRowTotalInclTax() + $priceProductionCharge);
        }
    }
}
