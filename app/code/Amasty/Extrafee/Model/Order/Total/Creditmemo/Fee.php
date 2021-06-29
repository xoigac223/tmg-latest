<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */


namespace Amasty\Extrafee\Model\Order\Total\Creditmemo;

use Amasty\Extrafee\Model\Order\FeeTotal;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class Fee extends AbstractTotal
{
    use FeeTotal;

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $this->collectFee($creditmemo);

        return $this;
    }
}
