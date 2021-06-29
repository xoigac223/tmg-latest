<?php

namespace Shreeji\Duplicateimage\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

class FindDuplicate {

    /**
     * 
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_urlBuilder;

    /**
     *
     * @var \Magento\Framework\App\ResourceConnection 
     */
    protected $_resource;

    /**
     *
     * @var \Shreeji\Duplicateimage\Model\ResourceModel\Duplicateimage\CollectionFactory 
     */
    protected $_duplicateImage;

    /**
     *
     * @var \Magento\Eav\Model\Config 
     */
    protected $_eavConfig;

    /**
     *
     * @var connection 
     */
    protected $_connection;

    /**
     * Catalog product media config
     *
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $_catalogProductMediaConfig;

    /**
     *
     * @var \Magento\Framework\App\Filesystem\DirectoryList  
     */
    protected $_directoryList;

    /**
     * 
     * @param \Magento\Backend\Model\UrlInterface $urlinterface
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Shreeji\Duplicateimage\Model\ResourceModel\Duplicateimage\CollectionFactory $duplicateImage
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig
     * @param DirectoryList $directoryList
     */
    public function __construct(
    \Magento\Backend\Model\UrlInterface $urlinterface, \Magento\Framework\App\ResourceConnection $resource, \Shreeji\Duplicateimage\Model\ResourceModel\Duplicateimage\CollectionFactory $duplicateImage, \Magento\Eav\Model\Config $eavConfig, \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig, \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->_urlBuilder = $urlinterface;
        $this->_resource = $resource;
        $this->_duplicateImage = $duplicateImage;
        $this->_eavConfig = $eavConfig;
        $this->_connection = $this->_resource->getConnection();
        $this->_catalogProductMediaConfig = $catalogProductMediaConfig;
        $this->_directoryList = $directoryList;
    }

    /*
     * Main fuction to find duplcate image 
     */

    public function findDuplicateImages() {
        $already = $this->_duplicateImage->create()->getData();
        $connection = $this->_connection;
        $productTable = $connection->getTableName('catalog_product_entity');
        $duplicateImageTable = $connection->getTableName('shreeji_duplicateimage');
        $productIdsquery = "SELECT entity_id FROM $productTable";
        $productIds = $connection->fetchAll($productIdsquery);
        $totalProduct = count($productIds);
        $mediaPath = $this->_directoryList->getPath('media') . '/' . $this->_catalogProductMediaConfig->getBaseMediaPath();
        try {
            if ($totalProduct > 0) {
                foreach ($productIds as $productId) {
                    $_images = "";
                    $sku = "";
                    $productname = "";
                    $_md5_values = "";
                    $_images = $this->_getProductImageFromDb($productId['entity_id']);
                    $_images = array_filter($_images);
                    if (count($_images) == 0) {
                        continue;
                    }
                    $sku = $this->_getProductSkuFromDb($productId['entity_id']);
                    $productname = $this->_getProductNameFromDb($productId['entity_id']);
                    $base_image = $this->_getBaseImagePathFromDb($productId['entity_id']);
                    $small_image = $this->_getSmallImagePathFromDb($productId['entity_id']);
                    $thumb_image = $this->_getThumbImagePathFromDb($productId['entity_id']);
                    $swatch_image = $this->_getSwatchImagePathFromDb($productId['entity_id']);
                    $_md5_values = array();
                    if ($base_image != 'no_selection' && $base_image != NULL) {
                        $filepath = $mediaPath . $base_image;
                        if (file_exists($filepath))
                            $_md5_values[] = md5(file_get_contents($filepath));
                    }
                    if ($small_image != 'no_selection' && $small_image != NULL) {
                        $filepath = $mediaPath . $small_image;
                        if (file_exists($filepath))
                            $_md5_values[] = md5(file_get_contents($filepath));
                    }
                    if ($thumb_image != 'no_selection' && $thumb_image != NULL) {
                        $filepath = $mediaPath . $thumb_image;
                        if (file_exists($filepath))
                            $_md5_values[] = md5(file_get_contents($filepath));
                    }
                    if ($swatch_image != 'no_selection' && $swatch_image != NULL) {
                        $filepath = $mediaPath . $swatch_image;
                        if (file_exists($filepath))
                            $_md5_values[] = md5(file_get_contents($filepath));
                    }
                    if ($_images) {
                        foreach ($_images as $_image) {
                            $insert = false;
                            $skusame = false;
                            $skusameimage = "";
                            if ($_image == $base_image || $_image == $small_image || $_image == $thumb_image || $_image == $swatch_image)
                                continue;
                            $filepath = $mediaPath . $_image;
                            if (file_exists($filepath))
                                $md5 = md5(file_get_contents($filepath));
                            else
                                continue;
                            if (in_array($md5, $_md5_values)) {
                                foreach ($already as $alreadysingle) {
                                    if ($alreadysingle['sku'] == $sku) {
                                        $skusame = true;
                                        if ($alreadysingle['filename'] == $_image) {
                                            unset($skusameimage);
                                            $skusame = true;
                                            break;
                                        } else {
                                            $skusameimage = $_image;
                                            $skusame = false;
                                        }
                                    } else {
                                        $skusameimage = $_image;
                                    }
                                }
                                if ((!empty($skusameimage) || empty($already)) && $skusame == false) {
                                    $sql = "Insert Into  $duplicateImageTable  (productname, filename, sku) Values ('$productname','$_image','$sku')";
                                    $connection->query($sql);
                                }
                            } else {
                                $_md5_values[] = $md5;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \LogicException('Could not find duplicate images: ' . $e->getMessage());
        }
    }

    /**
     *      
     * @param type $productId
     * @return array
     */
    protected function _getProductImageFromDb($productId) {
        $valueIds = $this->_getImageValueFromDb($productId);
        $valueIds = array_filter($valueIds);
        if (count($valueIds) == 0) {
            return $valueIds;
        }
        $mediaTable = $this->_connection->getTableName('catalog_product_entity_media_gallery');
        $valueIds = implode(",", $valueIds);
        $querymedia = "SELECT value FROM $mediaTable WHERE value_id IN ($valueIds)";
        $_imagesdb = $this->_connection->fetchAll($querymedia);
        $_images = array();
        foreach ($_imagesdb as $sigleimage) {
            $_images[] = $sigleimage['value'];
        }
        return $_images;
    }

    /**
     * 
     * @param type $productId
     * @return array
     */
    protected function _getImageValueFromDb($productId) {
        $mediaTable = $this->_connection->getTableName('catalog_product_entity_media_gallery_value_to_entity');
        $querymedia = "SELECT value_id FROM $mediaTable WHERE entity_id=" . $productId;
        $_valuedb = $this->_connection->fetchAll($querymedia);
        $_values = array();
        foreach ($_valuedb as $siglevalue) {
            $_values[] = $siglevalue['value_id'];
        }
        return $_values;
    }

    /**
     * 
     * @param type $productId
     * @return string|null
     */
    protected function _getProductSkuFromDb($productId) {
        $skuTable = $this->_connection->getTableName('catalog_product_entity');
        $skuquery = "SELECT sku from $skuTable where entity_id=" . $productId;
        $skudb = $this->_connection->fetchAll($skuquery);
        $sku = "";
        foreach ($skudb as $siglesku) {
            $sku = $siglesku['sku'];
        }
        return $sku;
    }

    /**
     * 
     * @param type $productId
     * @return string|null
     */
    protected function _getProductNameFromDb($productId) {
        $productNameTable = $this->_connection->getTableName('catalog_product_entity_varchar');
        $productNameId = $this->_eavConfig->getAttribute('catalog_product', "name")->getData('attribute_id');
        $namequery = "SELECT value from $productNameTable where attribute_id=$productNameId AND entity_id=" . $productId;
        $namedb = $this->_connection->fetchAll($namequery);
        $productname = "";
        foreach ($namedb as $singlename) {
            $productname = $singlename['value'];
        }
        return $productname;
    }

    /**
     * 
     * @param type $productId
     * @return string|null5
     */
    protected function _getBaseImagePathFromDb($productId) {
        $productNameTable = $this->_connection->getTableName('catalog_product_entity_varchar');
        $baseProductImageId = $this->_eavConfig->getAttribute('catalog_product', "image")->getData('attribute_id');
        $basequery = "SELECT value from $productNameTable where attribute_id=$baseProductImageId AND entity_id=" . $productId;
        $basedb = $this->_connection->fetchAll($basequery);
        $base_image = "";
        foreach ($basedb as $singlebase) {
            $base_image = $singlebase['value'];
        }
        return $base_image;
    }

    /**
     * 
     * @param type $productId
     * @return string|null5
     */
    protected function _getSmallImagePathFromDb($productId) {
        $productNameTable = $this->_connection->getTableName('catalog_product_entity_varchar');
        $smallProductImageId = $this->_eavConfig->getAttribute('catalog_product', "small_image")->getData('attribute_id');
        $smallquery = "SELECT value from $productNameTable where attribute_id=$smallProductImageId AND entity_id=" . $productId;
        $smalldb = $this->_connection->fetchAll($smallquery);
        $small_image = "";
        foreach ($smalldb as $singlesmall) {
            $small_image = $singlesmall['value'];
        }
        return $small_image;
    }

    /**
     * 
     * @param type $productId
     * @return string|null5
     */
    protected function _getThumbImagePathFromDb($productId) {
        $productNameTable = $this->_connection->getTableName('catalog_product_entity_varchar');
        $thumbProductImageId = $this->_eavConfig->getAttribute('catalog_product', "thumbnail")->getData('attribute_id');
        $thumbquery = "SELECT value from $productNameTable where attribute_id=$thumbProductImageId AND entity_id=" . $productId;
        $thumbdb = $this->_connection->fetchAll($thumbquery);
        $thumb_image = "";
        foreach ($thumbdb as $singlethumb) {
            $thumb_image = $singlethumb['value'];
        }
        return $thumb_image;
    }

    /**
     * 
     * @param type $productId
     * @return string|null5
     */
    protected function _getSwatchImagePathFromDb($productId) {
        $productNameTable = $this->_connection->getTableName('catalog_product_entity_varchar');
        $swatchProductImageId = $this->_eavConfig->getAttribute('catalog_product', "swatch_image")->getData('attribute_id');
        $swatchquery = "SELECT value from $productNameTable where attribute_id=$swatchProductImageId AND entity_id=" . $productId;
        $swatchdb = $this->_connection->fetchAll($swatchquery);
        $swatch_image = "";
        foreach ($swatchdb as $singleswatch) {
            $swatch_image = $singleswatch['value'];
        }
        return $swatch_image;
    }
}
