<?php
namespace Mirasvit\Sorting\Ui\RankingFactor\Form\Component;

class HtmlContent extends \Magento\Ui\Component\HtmlContent
{
    /**
     * Compatibility with 2.1.x
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function getBlock()
    {
        return $this->block;
    }
}