<?php
/**
 * Copyright © 2016 Firebear Studio. All rights reserved.
 */

namespace Firebear\ConfigurableProducts\Plugin\Model\Checkout;

use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Cart
{
    /**
     * @var Configurable
     */
    protected $configurableModel;

    protected $productFactory;

    /**
     * Cart constructor.
     *
     * @param Configurable   $configurableModel
     * @param ProductFactory $productFactory
     */
    public function __construct(
        Configurable $configurableModel,
        ProductFactory $productFactory
    ) {
        $this->configurableModel = $configurableModel;
        $this->productFactory = $productFactory;
    }

    /**
     * @param CustomerCart $subject
     * @param              $productInfo
     * @param null         $requestInfo
     *
     * @return array
     */
    public function beforeAddProduct(CustomerCart $subject, $productInfo, $requestInfo = null)
    {
        if (isset($requestInfo['super_attribute'])) {
            $productInfo = $this->configurableModel
                ->getProductByAttributes($requestInfo['super_attribute'], $productInfo);
            unset($requestInfo['super_attribute']);
        }

        return [$productInfo, $requestInfo];
    }
}
