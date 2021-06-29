<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Plugin\Catalog\Product;

class Label
{
    /**
     * @var \Amasty\Label\Model\LabelViewer
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Label constructor.
     * @param \Amasty\Label\Model\LabelViewer $helper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Amasty\Label\Model\LabelViewer $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Catalog\Block\Product\Image $subject
     * @param $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterToHtml(
        \Magento\Catalog\Block\Product\Image $subject,
        $result
    ) {
        $product = $subject->getProduct();
        $moduleName = $this->request->getModuleName();
        if ($product && $moduleName !== 'checkout') {
            $result .= $this->helper->renderProductLabel($product, 'category');
            $this->registry->register('amlabel_category_observer', true, true);
        }

        return $result;
    }
}
