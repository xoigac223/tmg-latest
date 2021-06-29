<?php

namespace Visiture\Achtandcss\Block\Info;

class Acht extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_template = 'Visiture_Achtandcss::info/acht.phtml';

    /**
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('Visiture_Achtandcss::info/pdf/acht.phtml');
        return $this->toHtml();
    }
}
