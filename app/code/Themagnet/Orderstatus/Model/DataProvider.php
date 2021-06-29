<?php

namespace Themagnet\Orderstatus\Model;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    protected $loadedData = array();
    protected $rowCollection;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Themagnet\Orderstatus\Model\ResourceModel\Orderstatus\Collection $collection,
        \Themagnet\Orderstatus\Model\ResourceModel\Orderstatus\CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection    = $collection;
        $this->rowCollection = $collectionFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $collection = $this->rowCollection->create();
        $items      = $collection->getItems();
        foreach ($items as $item) {
            $this->loadedData['stores']['orderstatus'][] = $item->getData();
        }
        return $this->loadedData;
    }
}