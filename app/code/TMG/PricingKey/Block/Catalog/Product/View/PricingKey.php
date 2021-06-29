<?php

namespace TMG\PricingKey\Block\Catalog\Product\View;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Framework\Json\Encoder;
use TMG\PricingKey\Helper\PricingKey as PricingKeyHelper;

class PricingKey extends AbstractProduct
{
    protected $_template = 'TMG_PricingKey::catalog/product/view/pricing-key.phtml';
    
    protected $jsonEncoder;
    
    protected $pricingKeyHelper;
    
    public function __construct(
        Context $context,
        Encoder $jsonEncoder,
        PricingKeyHelper $pricingKeyHelper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->jsonEncoder = $jsonEncoder;
        $this->pricingKeyHelper = $pricingKeyHelper;
    }
    
    public function getPricingKeyMappingJson()
    {
        return $this->jsonEncoder->encode($this->pricingKeyHelper->getPricingKeyMapping($this->getProduct()));
    }
   
    
}