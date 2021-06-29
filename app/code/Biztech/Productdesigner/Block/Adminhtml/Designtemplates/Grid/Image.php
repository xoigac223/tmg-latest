<?php

namespace Biztech\Productdesigner\Block\Adminhtml\Designtemplates\Grid;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    protected $_helper;
    public function __construct(
    \Magento\Catalog\Helper\Image $helper, \Magento\Framework\Image\Factory $imageFactory
    ) {
        $this->_helper = $helper;
        
    }
    public function render(\Magento\Framework\DataObject $row) {


        $designtemplate_id = $row->getDesigntemplatesId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $designImages = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designtemplateimages\Collection')->addFieldToFilter('designtemplates_id', Array('eq' => $designtemplate_id))->addFieldToFilter('design_image_type', 'base_high')->getData();

        $product_id = $row->getProductId();
        $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
        $product = $obj_product->load($product_id);
        if(isset($designImages[0]))
        {
        $url = $this->_helper->init($product, 'product_page_image_small')->setImageFile($designImages[0]['image_path'])->resize(135)->getUrl();
        
        $html = '<img ';
        $html .= 'src="' . $url . '"';
        return $html;
        }
    }

}
