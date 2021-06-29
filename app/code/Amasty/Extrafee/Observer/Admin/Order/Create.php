<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */


namespace Amasty\Extrafee\Observer\Admin\Order;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Json\DecoderInterface;
use Amasty\Extrafee\Model\TotalsInformationManagement;

class Create implements ObserverInterface
{
    /** @var TotalsInformationManagement  */
    protected $totalsInformationManagement;

    /** @var DecoderInterface  */
    protected $jsonDecoder;

    public function __construct(
        TotalsInformationManagement $totalsInformationManagement,
        DecoderInterface $jsonDecoder
    ) {
        $this->totalsInformationManagement = $totalsInformationManagement;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $feesJson =  $observer->getRequest('am_extra_fees');
        try {
            $fees = $this->jsonDecoder->decode($feesJson);
        } catch (\Exception $e) {
            return;
        }

        $quote = $observer->getOrderCreateModel()->getQuote();
        foreach ($fees as $feeId => $optionIds) {
            $this->totalsInformationManagement->proceedQuoteOptions($quote, $feeId, $optionIds);
        }
    }
}
