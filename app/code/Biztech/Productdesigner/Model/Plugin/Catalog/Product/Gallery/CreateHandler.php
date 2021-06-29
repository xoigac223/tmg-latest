<?php

namespace Biztech\Productdesigner\Model\Plugin\Catalog\Product\Gallery;

class CreateHandler {

    public function afterExecute(
    \Magento\Catalog\Model\Product\Gallery\CreateHandler $mediaGalleryCreateHandler, \Magento\Catalog\Model\Product $product
    ) {
        $value = $product->getData('media_gallery');        
         if (!empty($value)) {
            $this->processNewAndExistingImages($product, $value);
        }        
        return $product;
    }

    protected function processNewAndExistingImages($product, array &$images) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $galleryvalue = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Gallery');
        foreach ($images['images'] as &$image) {    
            if (empty($image['removed'])) {
                $data = [];
                $galleryvalue->deleteGalleryValueInStore(
                            $image['value_id'], $product->getData('entity_id'), $product->getStoreId()
                    );
                    $data['value_id'] = $image['value_id'];
                    $data['label'] = isset($image['label']) ? $image['label'] : '';
                    $data['position'] = isset($image['position']) ? (int) $image['position'] : 0;
                    $data['disabled'] = isset($image['disabled']) ? (int) $image['disabled'] : 0;
                    $data['store_id'] = (int) $product->getStoreId();
                    $data['entity_id'] = $product->getData('entity_id');
                if (isset($image['image_side'])) {                    
                    $data['image_side'] = $image['image_side'];                    
                } else if(isset($image['image_side_default']))
                {
                    $data['image_side'] = $image['image_side_default'];                   
                }
                if (isset($image['is_imprintdefaultlocation'])) {
                    $data['is_imprintdefaultlocation'] = $image['is_imprintdefaultlocation'];                    
                }
                $galleryvalue->insertGalleryValueInStore($data);
            }
        }
    }

}
