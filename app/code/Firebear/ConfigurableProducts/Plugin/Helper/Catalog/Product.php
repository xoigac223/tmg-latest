<?php
/**
 * Copyright © 2016 Firebear Studio. All rights reserved.
 */

namespace Firebear\ConfigurableProducts\Plugin\Helper\Catalog;

use Magento\Catalog\Model\Session;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Product
{
    /**
     * Configurable product resource model.
     *
     * @var Configurable
     */
    protected $configurableResource;

    /**
     * Catalog session.
     *
     * @var Session
     */
    protected $catalogSession;

    /**
     * Core registry.
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Configurable                  $configurableResource
     * @param Session                       $catalogSession
     * @param Registry                      $coreRegistry
     * @param ProductRepositoryInterface    $productRepository
     * @param StoreManagerInterface         $storeManager
     */
    public function __construct(
        Configurable $configurableResource,
        Session $catalogSession,
        Registry $coreRegistry,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->configurableResource = $configurableResource;
        $this->catalogSession = $catalogSession;
        $this->coreRegistry = $coreRegistry;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Catalog\Helper\Product $subject
     * @param $productId
     * @param $controller
     * @param null $params
     * @return array
     */
    public function beforeInitProduct(\Magento\Catalog\Helper\Product $subject, $productId, $controller, $params = null)
    {
        if ($productId) {
            $parentIds = $this->configurableResource->getParentIdsByChild($productId);

            if (!empty($parentIds)) {
                $this->coreRegistry->register('firebear_configurableproducts', [
                    'child_id' => $productId,
                    'parent_id' => $parentIds[0]
                ]);
                $productId = $parentIds[0];
            }
        }

        return [$productId, $controller, $params];
    }

    /**
     * Set configurable meta data based on current simple product.
     *
     * @return mixed
     */
    public function afterInitProduct()
    {
        /**
         *
         */
        $data = $this->coreRegistry->registry('firebear_configurableproducts');
        $product = $this->coreRegistry->registry('product');

        if (isset($data['child_id'])) {
            $productId = $data['child_id'];
            $childProduct = $this->productRepository->getById(
                $productId,
                false,
                $this->storeManager->getStore()->getId()
            );
            $product->setName($childProduct->getName());
            $product->setSku($childProduct->getSku());
            $product->setShortDescription($childProduct->getShortDescription());
            $product->setDescription($childProduct->getDescription());
            $product->setMetaTitle($childProduct->getMetaTitle());
            $product->setMetaKeyword($childProduct->getMetaKeyword());
            $product->setMetaDescription($childProduct->getMetaDescription());

            /**
             * Set product images.
             * If simple product does not have images than parent will be used.
             */
            if ($childProduct->getData('image')) {
                $product->setData('image', $childProduct->getData('image'));
            }
            if ($childProduct->getData('small_image')) {
                $product->setData('small_image', $childProduct->getData('small_image'));
            }
            if ($childProduct->getData('thumbnail')) {
                $product->setData('thumbnail', $childProduct->getData('thumbnail'));
            }

            /**
             * Add updated product to registry.
             */
            $this->coreRegistry->unregister('product');
            $this->coreRegistry->register('product', $product);
        }

        return $product;
    }
}
