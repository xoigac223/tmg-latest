<?php

namespace Biztech\Productdesigner\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

class Product extends \Magento\Catalog\Model\Product
{
    public function getAllMediaGalleryImages()
    {
        $images = array();
        $directory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
        if (!$this->hasData('media_gallery_images') && is_array($this->getMediaGallery('images'))) {
            $images = $this->_collectionFactory->create();
            $images_array = array();
            $lastkey = 0;
            foreach ($this->getMediaGallery('images') as $image) {
                /*if ((isset($image['disabled']) && $image['disabled']) || empty($image['value_id'])) {
                    continue;
                }*/
                $image['url'] = $this->getMediaConfig()->getMediaUrl($image['file']);
                $image['id'] = $image['value_id'];
                $image['path'] = $directory->getAbsolutePath($this->getMediaConfig()->getMediaPath($image['file']));
                if ($image['is_imprintdefaultlocation'] == 1) {
                    $images_array[] = $image;
                    $lastvalue = end($images_array);
                    $lastkey = key($images_array);
                    $arr1 = array($lastkey=>$lastvalue);
                    array_pop($images_array);
                    $images_array = array_merge($arr1,$images_array);
                } else{
                    $images_array[] = $image;
                }
            }
            foreach ($images_array as $value) {
                $images->addItem(new \Magento\Framework\DataObject($value));
            }
          //  $this->setData('media_gallery_images', $images);
        }
        //return $this->getData('media_gallery_images');
        return $images;
    }


}