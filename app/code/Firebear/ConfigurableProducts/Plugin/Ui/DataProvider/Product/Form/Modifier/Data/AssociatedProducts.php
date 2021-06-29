<?php
namespace Firebear\ConfigurableProducts\Plugin\Ui\DataProvider\Product\Form\Modifier\Data;

use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Data\AssociatedProducts
    as OriginalAssociatedProducts;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Firebear\ConfigurableProducts\Model\Product\Defaults;

class AssociatedProducts
{
    /**
     * Catalog locator
     *
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * Default product values
     *
     * @var Defaults
     */
    protected $productDefaults;

    /**
     * AssociatedProducts constructor.
     *
     * @param LocatorInterface $locator
     * @param Defaults         $productDefaults
     */
    public function __construct(
        LocatorInterface $locator,
        Defaults $productDefaults
    ) {
        $this->locator = $locator;
        $this->productDefaults = $productDefaults;
    }

    /**
     * Modify product matrix.
     *
     * @param OriginalAssociatedProducts $subject
     * @param array                      $productMatrix
     *
     * @return array
     * @SuppressWarnings("PMD.UnusedFormalParameter")
     */
    public function afterGetProductMatrix(
        OriginalAssociatedProducts $subject,
        array $productMatrix
    ) {
        if (!empty($productMatrix)) {
            $defaultProductId = $this->productDefaults->getDefaultProductId($this->locator->getProduct());

            foreach ($productMatrix as &$product) {
                $product['default_value'] = $product['id'];
                $product['checked'] = ($defaultProductId == $product['id']) ? 1 : 0;
            }
        }

        return $productMatrix;
    }
}
