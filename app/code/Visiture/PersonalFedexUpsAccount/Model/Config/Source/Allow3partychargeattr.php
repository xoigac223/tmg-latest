<?php


namespace Visiture\PersonalFedexUpsAccount\Model\Config\Source;

class Allow3partychargeattr implements \Magento\Framework\Option\ArrayInterface
{

    protected $_collectionFactory;

    public function __construct(\Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $collectionFactory)
    {
        $this->_collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $options = array();
        $collection = $this->_collectionFactory->create()->addVisibleFilter();

        $options[] = array('value' => '', 'label' => __('--Please Select--'));
        foreach($collection as $item)
        {
            $options[] = array('value' => $item->getAttributeCode(), 'label' => $item->getAttributeCode());
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
