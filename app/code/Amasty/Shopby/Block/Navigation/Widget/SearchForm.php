<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Navigation\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;

class SearchForm extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'layer/widget/search-form.phtml';
}
