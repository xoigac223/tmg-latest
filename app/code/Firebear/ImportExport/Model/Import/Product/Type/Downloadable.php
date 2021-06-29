<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Import\Product\Type;

use Magento\Framework\File\Uploader;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;

/**
 * Class Downloadable
 */
class Downloadable extends \Magento\DownloadableImportExport\Model\Import\Product\Type\Downloadable
{
    use \Firebear\ImportExport\Traits\Import\Product\Type;

    /**
     * Array of cached import link
     *
     * @var array
     */
    protected $importLink = [];
    
    /**
     * Validation links option
     *
     * @param array $rowData
     * @return bool
     */
    protected function isRowValidLink(array $rowData)
    {
        $result = parent::isRowValidLink($rowData);
        if (!$result && !empty($rowData[self::COL_DOWNLOADABLE_LINKS])) {
            $rowSku = strtolower($rowData[ImportProduct::COL_SKU]);
            $option = $this->prepareLinkData($rowData[self::COL_DOWNLOADABLE_LINKS]);
            $option = $option[0];
            $key = md5(
                $option['link_url'].
                $option['link_file'] .
                $option['link_type'] .
                $option['sample_url'] .
                $option['sample_file'] .
                $option['sample_type'] .
                $rowSku
            );
            if (isset($this->importLink[$key])) {
                $this->_entityModel->addRowError(__('Duplicated downloadable_links attribute.'), $this->rowNum);
                return true;
            }
            $this->importLink[$key] = true;
        }
        return $result;
    }
    
    /**
     * Get fill data options with key link
     *
     * @param array $options
     *
     * @return array
     */
    protected function fillDataTitleLink(array $options)
    {
        $result = [];
        $select = $this->connection->select();
        $select->from(
            ['dl' => $this->_resource->getTableName('downloadable_link')],
            [
                'link_id',
                'product_id',
                'sort_order',
                'number_of_downloads',
                'is_shareable',
                'link_url',
                'link_file',
                'link_type',
                'sample_url',
                'sample_file',
                'sample_type'
            ]
        );
        $select->joinLeft(
            ['dlp' => $this->_resource->getTableName('downloadable_link_price')],
            'dl.link_id = dlp.link_id AND dlp.website_id=' . self::DEFAULT_WEBSITE_ID,
            ['price_id']
        );
        $select->where(
            'product_id in (?)',
            $this->productIds
        );
        $existingOptions = $this->connection->fetchAll($select);
        foreach ($options as $option) {
            $existOption = $this->downloadableHelper->fillExistOptions(
                $this->dataLinkTitle,
                $option,
                $existingOptions
            );
            if (!empty($existOption)) {
                $result['title'][] = $existOption;
            }
            $existOption = $this->downloadableHelper->fillExistOptions(
                $this->dataLinkPrice,
                $option,
                $existingOptions
            );
            if (!empty($existOption)) {
                $result['price'][] = $existOption;
            }
        }

        return $result;
    }

    /**
     * Uploading files into the "downloadable/files" media folder.
     * Return a new file name if the same file is already exists.
     *
     * @param string $fileName
     * @param string $type
     * @param bool $renameFileOff
     *
     * @return string
     */
    protected function uploadDownloadableFiles($fileName, $type = 'links', $renameFileOff = false)
    {
        try {
            if ($this->_entityModel->getSourceType()
                && !in_array(
                    $this->_entityModel->getSourceType()->getCode(),
                    ['url', 'google']
                )
            ) {
                $dispersionPath = Uploader::getDispretionPath($fileName);
                $imageSting = mb_strtolower(
                    $dispersionPath . '/'
                        . preg_replace('/[^a-z0-9\._-]+/i', '', $fileName)
                );
                $this->_entityModel
                    ->getSourceType()
                    ->importImage($fileName, $imageSting);
                $res['file'] = $this->_entityModel
                    ->getSourceType()
                    ->getCode() . $imageSting;
            } else {
                $res = $this->uploaderHelper->getUploader(
                    $type,
                    $this->_entityModel->getParameters()
                )->move($fileName, $renameFileOff);
            }
     
            return $res['file'];
        } catch (\Exception $e) {
            $this->_entityModel->addRowError(
                $this->_messageTemplates[self::ERROR_MOVE_FILE] . '. '
                    . $e->getMessage(),
                $this->rowNum
            );

            return '';
        }
    }

    public function isRowValid(array $rowData, $rowNum, $isNewProduct = true)
    {
        $this->rowNum = $rowNum;
        $error = false;
        if ($this->isRowValidSample($rowData) || $this->isRowValidLink($rowData)) {
            $error = true;
        }
        return !$error;
    }

    /**
     * Save product type specific data.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    public function saveData()
    {
        $newSku = $this->_entityModel->getNewSku();
        while ($bunch = $this->_entityModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->_entityModel->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }

                if (version_compare($this->_entityModel->getProductMetadata()->getVersion(), '2.2.0', '>=')) {
                    $rowSku = strtolower($rowData[ImportProduct::COL_SKU]);
                } else {
                    $rowSku = $rowData[ImportProduct::COL_SKU];
                }
                $productData = $newSku[$rowSku];
                $this->parseOptions($rowData, $productData[$this->getProductEntityLinkField()]);
            }
            if (!empty($this->cachedOptions['sample']) || !empty($this->cachedOptions['link'])) {
                $this->saveOptions();
                $this->clear();
            }
        }
        return $this;
    }
}
