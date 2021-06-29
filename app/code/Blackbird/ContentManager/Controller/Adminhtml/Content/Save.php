<?php
/**
 * Blackbird ContentManager Module
 *
 * NOTICE OF LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 * @category        Blackbird
 * @package         Blackbird_ContentManager
 * @copyright       Copyright (c) 2017 Blackbird (https://black.bird.eu)
 * @author          Blackbird Team
 * @license         https://www.advancedcontentmanager.com/license/
 */
namespace Blackbird\ContentManager\Controller\Adminhtml\Content;

use Blackbird\ContentManager\Model\ContentType;
use Blackbird\ContentManager\Model\Content;
use Blackbird\ContentManager\Model\ContentType\CustomField;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Uploader;

class Save extends \Blackbird\ContentManager\Controller\Adminhtml\Content
{
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Blackbird\ContentManager\Model\Config\Source\ContentType\CustomFields
     */
    protected $_customFieldsSource;
    
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;
    
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_driverFile;
    
    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $_urlDecoder;
    
    /**
     * @var \Blackbird\ContentManager\Helper\Content\Data
     */
    protected $_helperContent;
    
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory $contentTypeCollectionFactory
     * @param \Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory $contentCollectionFactory
     * @param \Blackbird\ContentManager\Model\Factory $modelFactory
     * @param \Magento\Framework\App\Cache\Manager $cacheManager
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Blackbird\ContentManager\Model\Config\Source\ContentType\CustomFields $customFieldsSource
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Blackbird\ContentManager\Helper\Content\Data $helperContent
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory $contentTypeCollectionFactory,
        \Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory $contentCollectionFactory,
        \Blackbird\ContentManager\Model\Factory $modelFactory,
        \Magento\Framework\App\Cache\Manager $cacheManager,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Blackbird\ContentManager\Model\Config\Source\ContentType\CustomFields $customFieldsSource,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Blackbird\ContentManager\Helper\Content\Data $helperContent
    ) {
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_customFieldsSource = $customFieldsSource;
        $this->_filesystem = $filesystem;
        $this->_driverFile = $driverFile;
        $this->_urlDecoder = $urlDecoder;
        $this->_helperContent = $helperContent;
        parent::__construct(
            $context,
            $coreRegistry,
            $datetime,
            $contentTypeCollectionFactory,
            $contentCollectionFactory,
            $modelFactory,
            $cacheManager
        );
    }
    
    /**
     * Returns result of current user permission check on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Blackbird_ContentManager::content_save');
    }
    
    /**
     * Save action
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->_initAction();
        $contentType = $this->_contentTypeModel;
        $content = $this->_contentModel;
        $data = $this->getRequest()->getPostValue();
        $storeId = !empty($this->getRequest()->getParam('store')) ? $this->getRequest()->getParam('store') : 0;
                
        // If request is correctly processed and the contentype exists
        if (is_array($data) && $contentType instanceof ContentType && !empty($contentType->getCtId())) {
            // If we are editing an existing content
            if ($content instanceof Content && is_numeric($content->getId())) {
                // Update the last edit time
                $data[Content::UPDATED_AT] = $this->_datetime->date();
                $data[Content::CT_ID] = $contentType->getCtId();
                $data[Content::ID] = $content->getId();
            } else {
                // ...else we create a new content
                $content = $this->_modelFactory->create(Content::class);
                $data[Content::CREATED_AT] = $this->_datetime->date();
                $data[Content::CT_ID] = $contentType->getCtId();
            }
            
            // Prepare save of the content
            $content = $this->prepareContent($content, $data);
            
            $this->_eventManager->dispatch(
                'contentmanager_content_prepare_save',
                ['post' => $content, 'request' => $this->getRequest()]
            );
            
            // Save content
            try {
                $content->setStoreId($storeId);
                $content->save();
                
                $this->messageManager->addSuccessMessage(__('The content has been saved !'));
                $this->_getSession()->setFormData(false);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the content: %1', $e->getMessage()));
            }
            
            $this->_getSession()->setFormData($data);
            
            /** Redirect Manager */
            switch ($this->getRequest()->getParam('back', 'edit')) {
                case 'new':
                    $path = '*/*/new';
                    $params = ['ct_id' => $content->getCtId()];
                    break;
                case 'duplicate':
                    $path = '*/*/duplicate';
                    $params = ['id' => $content->getId()];
                    break;
                case 'edit':
                    $path = '*/*/edit';
                    $params = ['id' => $content->getId(), '_current' => true];
                    break;
                case 'back':
                default:
                    $path = '*/*/';
                    $params = ['ct_id' => $content->getCtId()];
                    break;
            }

            return $this->resultRedirect->setPath($path, $params);
        }
        
        $this->messageManager->addErrorMessage(__('This content type no longer exists.'));
        return $this->resultRedirect->setPath('*/contenttype/');
    }
    
    /**
     * Prepare the content to save
     * 
     * @param Content $content
     * @param array $data
     * @return Content
     */
    protected function prepareContent(Content $content, array $data)
    {
        // Get all existing attributes for the entity
        $attributes = $this->initAttributes($content->getAttributes());
        
        // Handle file management
        $data = $this->prepareFiles($data);
        
        // Set generic data
        $defaultData = [
            Content::DEFAULT_META_TITLE => 0,
            Content::DEFAULT_DESCR => 0,
            Content::DEFAULT_KEYWORDS => 0,
            Content::DEFAULT_OG_TITLE => 0,
            Content::DEFAULT_OG_DESCR => 0,
            Content::DEFAULT_OG_URL => 0,
            Content::DEFAULT_OG_TYPE => 0,
            Content::DEFAULT_OG_IMAGE => 0,
        ];
        $content->setData(array_merge($defaultData, $data));
        
        // Prepare attributes
        $this->prepareAttributes($content, $attributes);
        
        // Handle replacement patters
        $this->_handlePatterns($content);
        
        return $content;
    }
    
    /**
     * @param type $attributes
     * @return array
     */
    protected function initAttributes($attributes)
    {
        $attributesArray = [];
        
        foreach ($attributes as $attribute) {
            $attributesArray[$attribute->getAttributeCode()] = null;
        }
        
        return $attributes;
    }
    
    /**
     * Prepare attributes to save
     * 
     * @param Content $content
     * @param array $attributes
     */
    protected function prepareAttributes(Content $content, array $attributes)
    {
        /**
         * Empty data (for checkbox and multiple,
         * set to null in oder to remove from DB,
         * otherwise, attribute is keeped)
         */
        $noResetFields = ['created_at'];
        
        foreach ($attributes as $key => $attribute) {
            if (!array_key_exists($key, $content->getData()) && !in_array($key, $noResetFields)) {
                $content->unsetData($key);
            }
        }
        
        // Check for "multiple" values
        foreach ($content->getData() as $key => $value) {
            // If the attribute is an array, we implode it by comma
            if (is_array($value) && $key != 'nodes') {
                $content->setData($key, implode(',', $value));
            }
        }
    }
    
    /**
     * Prepare files to save
     * 
     * @param array $data
     * @return array
     */
    protected function prepareFiles(array $data)
    {
        // Manage files
        $data = $this->manageFiles($data);
        
        // Manage images
        if (!empty($data['content_image']) && is_array($data['content_image'])) {
            $dataImages = $this->manageImages($data['content_image']);
            unset($data['content_image']);
            $data = array_merge($data, $dataImages);
        }
        
        return $data;
    }
    
    /**
     * Replace file if same identifiers are matched
     * 
     * @param array $dataFiles
     * @param array $data
     * @return array
     */
    protected function replaceFiles(array $dataFiles, array $data)
    {
        foreach ($dataFiles as $dataFile) {
            foreach ($dataFile as $identifier => $fileName) {
                if (!empty($data[$identifier]) && $data[$identifier] != $fileName) {
                    $data[$identifier] = $dataFile[$identifier];
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Delete requested files
     * 
     * @param array $data
     * @return array
     */
    protected function deleteFiles(array $data)
    {
        if (is_array($this->getRequest()->getParam('delete'))) {
            foreach ($this->getRequest()->getParam('delete') as $identifier) {
                $this->deleteFile($identifier, $data[$identifier]);
                $data[$identifier] = null;
            }
        }
        
        return $data;
    }
    
    /**
     * Delete the given filename
     * 
     * @param string $filename
     */
    protected function deleteFile($identifier, $filename)
    {
        $fileField = $this->_customFieldsSource->getCustomFieldsByIdentifiers($identifier)->getFirstItem();
        $filepath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fileField->getFilePath());
        $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath(ContentType::CT_FILE_FOLDER . DIRECTORY_SEPARATOR . $filepath);
        
        $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getDriver()->deleteFile($path . DIRECTORY_SEPARATOR . $filename);
    }
    
    /**
     * Save, repace and delete files
     * 
     * @param array $data
     * @return array
     */
    protected function manageFiles(array $data)
    {
        $dataFiles = [];
        $files = (array)$this->getRequest()->getFiles();
        // Prevent images files
        unset($files['content_image']);
        
        // Download files
        try {
            $dataFiles = $this->_uploadFiles($files);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }
        
        foreach ($dataFiles as $dataFile) {
            $data = array_merge($data, $dataFile);
        }
        
        // File replacement
        $data = $this->replaceFiles($dataFiles, $data);
        
        // File deletion
        $data = $this->deleteFiles($data);
        
        return $data;
    }

    /**
     * Save original and croped images
     *
     * @todo check if name with suffix '_orig' does not already exists
     * @param array $data
     * @return array
     */
    protected function manageImages(array $data)
    {
        $dataImage = [];
        $imageFields = $this->_customFieldsSource->getCustomFieldsByIdentifiers(array_keys($data));

        // Contain images data by field identifier
        foreach ($data as $key => $json) {
            // Init vars
            $image = json_decode($json);
            $imageField = $imageFields->getItemByColumnValue(CustomField::IDENTIFIER, $key);
            $path = '';
            $extensions = [];
            
            /**
             * If json_decode return null
             * 
             * @note html5uploadimage: basic script send json limited to 524288 chars
             * provided by the input text, with attribute name '..._values'. Change
             * that input type to hidden in order to have unlimited chars.
             * Warning: check your config for more details about limitation :
             * Apache: LimitRequestBody
             * PHP: post_max_size
             */
            if (!$image) {
                $this->messageManager->addErrorMessage(__('Something went wrong during saving the image of the field "%1"', $key));
                continue;
            }
            
            // Retrieve the additionnal file path
            if (!empty($imageField->getFilePath())) {
                $path = DIRECTORY_SEPARATOR . $imageField->getFilePath();
            }
            
            // Retrieve compatible file extensions
            if (!empty($imageField->getData(CustomField::FILE_EXTENSION))) {
                $extensions = explode(',', $imageField->getData(CustomField::FILE_EXTENSION));
            }
            
            /**
             * The module send a url or a base 64 for the sended picture html5uploader
             */
            
            // Original Image
            if (!empty($image->original)) {
                // Only if the format is not an url and so a base64 data
                if (filter_var($image->original, FILTER_VALIDATE_URL) === false) {
                    try {
                        $dataImage[$key . '_orig'] = $this->_saveImage($image->name, $image->original, $extensions, $path);
                    } catch (\Exception $e) {
                        $this->messageManager->addExceptionMessage($e, __('An error has been occurred for the field "%1" : %2', $key, $e->getMessage()));
                        unset($dataImage[$key . '_orig']);
                        continue;
                    }
                } else {
                    $filename = explode('/', $image->original);
                    $dataImage[$key . '_orig'] = end($filename);
                }
            } else {
                $image->data = null;
                $dataImage[$key . '_orig'] = '';
            }
            // Croped Image
            if (!empty($image->data)) {
                // Only if the croop toll is enabled and value is not an url and so a base64 data
                if ($imageField->getCrop() && filter_var($image->data, FILTER_VALIDATE_URL) === false) {
                    $path = DIRECTORY_SEPARATOR . ContentType::CT_IMAGE_CROPPED_FOLDER . $path;
                    try {
                        $dataImage[$key] = $this->_saveImage($image->name, $image->data, $extensions, $path);
                    } catch (\Exception $e) {
                        $this->messageManager->addExceptionMessage($e, __('An error has been occurred for the field "%1" : %2', $key, $e->getMessage()));
                        unset($dataImage[$key]);
                        continue;
                    }
                } elseif (!empty($dataImage[$key . '_orig'])) {
                    // If the crop tool is disabled, use the original image
                    $dataImage[$key] = $dataImage[$key . '_orig'];
                }
            } else {
                $dataImage[$key] = '';
            }
        }
        
        return $dataImage;
    }
    
    /**
     * Handle patterns replacement
     * {{news_title}} will be replaced by content of the corresponding fields
     * {{news_title|plain}} is used for a plain text value
     * 
     * @param Content $content
     * @return Content
     */
    protected function _handlePatterns(&$content)
    {
        foreach ($content->getData() as $attribute => $value) {
            // Apply pattern
            $value = $this->_helperContent->applyPattern($content, $value);
            $content->setData($attribute, $value);
        }
    }

    /**
     * Save a picture from it's base64
     *
     * @param string $filename
     * @param string $data
     * @param array $extensions
     * @param string $subfolder
     * @return string
     * @throws \Exception
     */
    protected function _saveImage($filename, $data, array $extensions = [], $subfolder = '')
    {
        /**
         * $data is formated like this :
         * data:image/png;base64,iVBORw0KGgoAAAANSU....
         */
        $data = explode(';', $data);
        $mime = explode('/', $data[0]);
        $base64 = explode(',', $data[1]);
        $base64 = $base64[1];
        $directoryWrite = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $filepath = ContentType::CT_FILE_FOLDER . $subfolder;
        
        // Stop save if not a compatible image
        if (!$this->_isImageAllowed($mime, $extensions)) {
            throw new \Exception('The file ' . $filename . ' is not of these allowed formats : ' . implode(', ', $extensions));
        }
        
        // Create the subfolder if it doesn't exists
        try {
            $directoryWrite->create($filepath);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }
        
        // Get new file name if the same is already exists
        $destinationFile = $directoryWrite->getAbsolutePath($filepath . DIRECTORY_SEPARATOR . $filename);
        $filename = Uploader::getNewFileName($destinationFile);
        
        $filepath .= DIRECTORY_SEPARATOR . $filename;
        
        // Save image
        $directoryWrite->writeFile($filepath, $this->_urlDecoder->decode($base64));
        
        return $filename;
    }
    
    /**
     * Check if the file has an image mime type
     * 
     * @param string $mime
     * @return boolean
     */
    protected function _isImage($mime)
    {
        return ($mime === 'image');
    }
    
    /**
     * Check if extensions are allowed
     * 
     * @param string $mime
     * @param array $extensions
     * @return boolean
     */
    protected function _isFileExtensionsAllowed($mime, array $extensions = [])
    {
        $isAllowed = true;
        $extensions = $this->_sanitizeArray($extensions);
        
        // Special case of jpg and svg+xml format
        if (in_array('jpg', $extensions) && !in_array('jpeg', $extensions)) {
            $extensions[] = 'jpeg';
        }
        if (!in_array('jpg', $extensions) && in_array('jpeg', $extensions)) {
            $extensions[] = 'jpg';
        }
        if (in_array('svg', $extensions) && !in_array('svg+xml', $extensions)) {
            $extensions[] = 'svg+xml';
        }
        if (!in_array('svg', $extensions) && in_array('svg+xml', $extensions)) {
            $extensions[] = 'svg';
        }
        
        // If no allowed extensions is given, all extensions are allowed
        if (!empty($extensions)) {
            $isAllowed = in_array($mime, $extensions);
        }
        
        return $isAllowed;
    }
    
    /**
     * Sanitize an array of empty values
     * 
     * @param array $array
     * @return array
     */
    protected function _sanitizeArray(array $array)
    {
        return array_filter(array_map('trim', $array));
    }
    
    /**
     * Check if the format of the image is correct
     * 
     * @param array $mime
     * @param array $extensions
     * @return boolean
     */
    protected function _isImageAllowed($mime, array $extensions = [])
    {
        $mime[0] = str_replace('data:', '', $mime[0]);
        
        return ($this->_isImage($mime[0]) && $this->_isFileExtensionsAllowed($mime[1], $extensions));
    }
    
    /**
     * Upload the files and return an array of datas
     * 
     * @return array
     * @throws \Exception
     */
    protected function _uploadFiles(array $files = [])
    {
        $results = [];
        $files = (!empty($files)) ? $files : (array)$this->getRequest()->getFiles();
        
        foreach ($files as $identifier => $dataFile) {
            if (!empty($dataFile['name'])) {
                if ($this->_driverFile->isExists($dataFile['tmp_name'])) {
                    $fileField = $this->_customFieldsSource->getCustomFieldsByIdentifiers($identifier)->getFirstItem();
                    $filepath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fileField->getFilePath());
                    
                    $allowedExtensions = explode(',', $fileField->getData(CustomField::FILE_EXTENSION));
                    $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)
                        ->getAbsolutePath(ContentType::CT_FILE_FOLDER . DIRECTORY_SEPARATOR . $filepath);
                    
                    try {
                        $uploader = $this->_fileUploaderFactory->create(['fileId' => $identifier]);
                        $uploader->setAllowedExtensions($allowedExtensions);
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setFilesDispersion(false);
                        
                        try {
                            $fileData = $uploader->save($path, $dataFile['name']);
                            $dataFileName = $fileData['file'];
                            
                            $results[] = [$identifier => $dataFileName];
                        } catch (\Exception $e) {
                            $this->messageManager->addWarningMessage($e->getMessage());
                            $results[] = [];
                        }
                    } catch (\Exception $e) {
                        $this->messageManager->addExceptionMessage($e, __('Something went wrong while upload the file.'));
                    }
                    
                } else {
                    throw new \Exception('The file ' . $identifier . ' has encountered a problem during the upload.');
                }
            }
        }
        
        return $results;
    }
    
}
