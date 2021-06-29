<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */


namespace Amasty\Extrafee\Model\Order;

use Amasty\Extrafee\Model\ResourceModel\Quote\CollectionFactory as FeeQuoteCollectionFactory;

trait FeeTotal
{
    /**
     * @var FeeQuoteCollectionFactory
     */
    private $feeQuoteCollectionFactory;

    public function __construct(FeeQuoteCollectionFactory $feeQuoteCollectionFactory)
    {
        $this->feeQuoteCollectionFactory = $feeQuoteCollectionFactory;
    }

    /**
     * @param $objectWithOrder
     * @return $this
     */
    public function collectFee($objectWithOrder)
    {
        $order = $objectWithOrder->getOrder();

        $feesQuoteCollection = $this->feeQuoteCollectionFactory->create()
            ->addFieldToFilter('option_id', ['neq' => '0'])
            ->addFieldToFilter('quote_id', $order->getQuoteId());

        $feeAmount = 0;
        $baseFeeAmount = 0;
        $taxAmount = 0;
        $baseTaxAmount = 0;

        foreach ($feesQuoteCollection as $feeOption) {
            $feeAmount += $feeOption->getFeeAmount();
            $baseFeeAmount += $feeOption->getBaseFeeAmount();
            $taxAmount += $feeOption->getTaxAmount();
            $baseTaxAmount += $feeOption->getBaseTaxAmount();
        }

        $objectWithOrder->setGrandTotal($objectWithOrder->getGrandTotal() + $feeAmount + $taxAmount);
        $objectWithOrder->setBaseGrandTotal($objectWithOrder->getBaseGrandTotal() + $baseFeeAmount + $baseTaxAmount);
        $objectWithOrder->setTaxAmount($objectWithOrder->getTaxAmount() + $taxAmount);
        $objectWithOrder->setBaseTaxAmount($objectWithOrder->getBaseTaxAmount() + $baseTaxAmount);

        return $this;
    }
}
