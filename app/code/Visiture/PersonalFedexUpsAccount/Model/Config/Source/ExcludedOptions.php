<?php


namespace Visiture\PersonalFedexUpsAccount\Model\Config\Source;

class ExcludedOptions implements \Magento\Framework\Option\ArrayInterface
{
    const ATTR_NAME = "brandname";

	protected $_productAttributeRepository;

    public function __construct(\Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository)
    {
        $this->_productAttributeRepository = $productAttributeRepository;
    }

    public function toOptionArray()
    {
        $options = array();
        $attrOptions = $this->_productAttributeRepository->get(self::ATTR_NAME)->getOptions();
        
        $options[] = array('value' => '', 'label' => __('--No Option--'));
        foreach($attrOptions as $option)
        {
            if(isset($option['value']) && $option['value'])
                $options[] = array('value' => $option['value'], 'label' => $option['label']);
        }

        return $options;
    }

    public function toArray()
    {
    	$data = [];
    	foreach ($this->toOptionArray() as $key => $option) {
    		$data[$option['value']] = $option['label'];
    	}
        return $data;
    }
}
