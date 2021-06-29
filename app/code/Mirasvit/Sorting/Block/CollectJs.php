<?php

namespace Mirasvit\Sorting\Block;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class CollectJs extends Template
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    private   $registry;

    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry   = $registry;

        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getMageInit()
    {
        $baseUrl = $this->urlBuilder->getUrl('sorting/collect/view');

        $product = $this->registry->registry('current_product');

        $id = $product ? $product->getId() : false;

        return [
            'Mirasvit_Sorting/js/collect' => [
                'url' => $baseUrl,
                'id'  => $id,
            ],
        ];
    }
}
