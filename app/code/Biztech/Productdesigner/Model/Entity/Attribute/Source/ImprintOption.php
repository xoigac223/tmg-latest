<?php

namespace Biztech\Productdesigner\Model\Entity\Attribute\Source;

class ImprintOption extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource {

    /**
     * Retrieve all options array
     *
     * @return array
     */
    protected $request;
    protected $_options = [];

    public function __construct(
    \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    public function getAllOptions() {        
        $product_id = $this->request->getParam('id');
        if ($product_id) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
            $product = $obj_product->load($product_id);
            $customOptions = $objectManager->get('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($product);
            foreach ($customOptions as $o) {
                $data = array(
                    'label' => __($o->getDefaultTitle()),
                    'value' => $o->getOptionId()
                );
                array_push($this->_options, $data);
            }
        }
        return $this->_options;
    }
}
