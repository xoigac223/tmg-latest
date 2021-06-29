<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */


namespace Amasty\Extrafee\Model\Order\Invoice\Total;

use \Amasty\Extrafee\Model\Order\FeeTotal;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class Fee extends AbstractTotal
{
    use FeeTotal;

    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $this->collectFee($invoice);

        return $this;
    }
}
