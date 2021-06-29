<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

namespace Amasty\Shopby\Helper;

use Magento\Framework\App\Helper\Context;

class State extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Amasty\ShopbyBase\Api\UrlBuilderInterface
     */
    private $amUrlBuilder;

    public function __construct(Context $context, \Amasty\ShopbyBase\Api\UrlBuilderInterface $amUrlBuilder)
    {
        parent::__construct($context);
        $this->amUrlBuilder = $amUrlBuilder;
    }

    public function getCurrentUrl()
    {
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = ['_' => null, 'shopbyAjax' => null];

        $result = str_replace('&amp;', '&', $this->amUrlBuilder->getUrl('*/*/*', $params));
        return $result;
    }
}
