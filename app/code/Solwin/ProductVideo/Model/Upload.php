<?php
/**
 * Solwin Infotech
 * Solwin Advanced Product Video Extension
 *
 * @category   Solwin
 * @package    Solwin_ProductVideo
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
namespace Solwin\ProductVideo\Model;

use Magento\Framework\File\Uploader;

class Upload
{
    /**
     * Upload model factory
     * 
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * constructor
     * 
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     */
    public function __construct(
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
    ) {
        $this->_uploaderFactory = $uploaderFactory;
    }

    /**
     * upload file
     *
     * @param $input
     * @param $destinationFolder
     * @param $data
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function uploadFileAndGetName($input, $destinationFolder, $data)
    {
        try {
            $uploader = $this->_uploaderFactory
                    ->create(['fileId' => $input]);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $uploader->setAllowCreateFolders(true);
            $result = $uploader->save($destinationFolder);
            return $result['file'];
        } catch (\Exception $e) {
            if ($e->getCode() != Uploader::TMP_NAME_EMPTY) {
                throw new \Magento\Framework\Exception\LocalizedException(
                        $e->getMessage()
                        );
            } else {
                if (isset($data[$input]['value'])) {
                    return $data[$input]['value'];
                }
            }
        }
        return '';
    }
}