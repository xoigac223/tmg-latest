<?php

namespace Shreeji\Duplicateimage\Observer;

use Magento\Framework\Event\ObserverInterface;
use Shreeji\Duplicateimage\Model\ResourceModel\Duplicateimage\CollectionFactory;

class RemoveProductImage implements ObserverInterface {

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     *
     * @var \Shreeji\Duplicateimage\Model\Duplicateimage 
     */
    protected $_duplicateImage;

    /**
     * 
     * @param CollectionFactory $collectionFactory
     * @param \Shreeji\Duplicateimage\Model\Duplicateimage $duplicateImage
     */
    public function __construct(
    CollectionFactory $collectionFactory, \Shreeji\Duplicateimage\Model\Duplicateimage $duplicateImage
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->_duplicateImage = $duplicateImage;
    }

    /**
     * Remove duplicate image from storage
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getProduct();
        $sku = $product->getSku();
        try {
            if (!empty($sku)) {                
                $manageimagedata = $this->collectionFactory->create()->addFieldToFilter('sku', $sku);
                if (!empty($manageimagedata)) {
                    foreach ($manageimagedata as $singleimagedata) {
                        if (!empty($singleimagedata['manageimage_id'])) {
                            $this->_duplicateImage->load($singleimagedata['manageimage_id'])->delete();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \LogicException('Could not find duplicate images for product: ' . $e->getMessage());
        }
    }

}
