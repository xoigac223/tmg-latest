<?php

namespace Themagnet\Productimport\Model\Import;

class Product
{
	protected $productEntityLinkField;
	protected $productEntityIdentifierField;
	protected $productEntityTableName;
	protected $mediaGalleryTableName;
	protected $mediaGalleryValueTableName;
	protected $mediaGalleryEntityToValueTableName;
	protected $obj;

	public function beforeGetRowScope(\Magento\CatalogImportExport\Model\Import\Product $subject, $result)
	{
		$this->obj = $subject;
		if(isset($result['sku']) && $result['sku'] != '' && isset($result['base_image']) && $result['base_image'] != '' && isset($result['small_image']) && $result['small_image'] != '' && isset($result['thumbnail_image']) && $result['thumbnail_image'] != ''  ){
			$image_array = array(strtolower($result['base_image']),strtolower($result['small_image']) ,strtolower($result['thumbnail_image']));
			$image_array = array_unique($image_array);
			$images = $this->getExistingImages($result['sku']);
			if(count($images) > 0){
				foreach($images as $key=>$image){
						$imageName = basename(strtolower($image['value']));
						try {
							if(in_array($imageName, $image_array) !== true){
								$this->obj->getConnection()->delete(
			                	$this->mediaGalleryTableName,
			                	$this->obj->getConnection()->quoteInto('value_id = ?', $image['gallery_id'])
			            		);
							}
				        } catch (\Exception $e) {
				            echo $e->getMessage();
				        }
				}
				
			}
		}
		

	}

	protected function initMediaGalleryResources()
    {
        if (null == $this->mediaGalleryTableName) {
            $this->productEntityTableName = $this->obj->getConnection()->getTableName('catalog_product_entity');
            $this->mediaGalleryTableName = $this->obj->getConnection()->getTableName('catalog_product_entity_media_gallery');
            $this->mediaGalleryValueTableName = $this->obj->getConnection()->getTableName(
                'catalog_product_entity_media_gallery_value'
            );
            $this->mediaGalleryEntityToValueTableName = $this->obj->getConnection()->getTableName(
                'catalog_product_entity_media_gallery_value_to_entity'
            );
        }
    }

    protected function getExistingImages($sku)
    {
        $result = [];
        if ($this->obj->getErrorAggregator()->hasToBeTerminated()) {
            return $result;
        }

        $this->initMediaGalleryResources();
        $productSKUs = $sku;
        $select = $this->obj->getConnection()->select()->from(
            ['mg' => $this->mediaGalleryTableName],
            ['value' => 'mg.value','gallery_id'=>'mg.value_id','media_type']
        )->joinInner(
            ['mgvte' => $this->mediaGalleryEntityToValueTableName],
            '(mg.value_id = mgvte.value_id)',
            ['gallery_value_id'=>'mgvte.value_id']
        )->joinInner(
            ['pe' => $this->productEntityTableName],
            "mgvte.entity_id = pe.entity_id",
            ['sku' => 'pe.sku']
        )->where(
            'pe.sku IN (?)',
            $productSKUs
        );
        
        return $this->obj->getConnection()->fetchAll($select);
    }

    public function aroundValidateRow(\Magento\CatalogImportExport\Model\Import\Product $subject, 
                                        \Closure $proceed, 
                                        array $rowData, 
                                        $rowNum
                                    )
    {

        $result = $proceed($rowData, $rowNum);
        if ($subject->getErrorAggregator()->isRowInvalid($rowNum)) {
           $data = $subject->getErrorAggregator()->getErrorByRowNumber($rowNum);
                // mark SCOPE_DEFAULT row as invalid for future child rows if product not in DB already
           $ErrorCode = $data[0]->getErrorCode(); 
           $ErrorLevel = $data[0]->getErrorLevel(); 
           $RowNumber = $data[0]->getRowNumber(); 
           $ErrorMessage = $data[0]->getErrorMessage();
           $ErrorDescription = $data[0]->getErrorDescription();
           //echo get_class($subject->getErrorAggregator()); exit;
           try {
               $subject->getErrorAggregator()->updateError($ErrorCode, $ErrorLevel, $RowNumber, $rowData['sku'], $ErrorMessage, $ErrorDescription);
           } catch (\Exception $e) {
               echo $e->getMessage(); exit;
           }
           
        }
        return $result;
    }
}