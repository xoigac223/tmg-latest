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
namespace Blackbird\ContentManager\Model;

use Blackbird\ContentManager\Model\ContentType\CustomField;
use Blackbird\ContentManager\Model\ContentType\CustomField\Option;
use Blackbird\ContentManager\Api\Data\ContentType\Layout\FieldInterface;
use Blackbird\ContentManager\Api\Data\ContentType\Layout\BlockInterface;
use Blackbird\ContentManager\Api\Data\ContentType\Layout\GroupInterface;
use Blackbird\ContentManager\Api\ContentMetadataInterface;
use Blackbird\ContentManager\Api\Data\ContentInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\Reflection\DataObjectProcessor;
use Blackbird\ContentManager\Api\Data\ContentInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Store\Model\Store;
use Blackbird\ContentManager\Model\ResourceModel\Content as ResourceContent;

/**
 * Content Model
 * @method void setCtId() Set Id of the Content Type
 */
class Content extends \Blackbird\ContentManager\Model\AbstractModel implements ContentInterface, IdentityInterface
{
    /**
     * Entity code
     */
    const ENTITY = 'contenttype_content';

    /**
     * Content manager content cache tag
     */
    const CACHE_TAG = 'contentmanager_content';

    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'contenttype_content';

    /**
     * Name of the event object
     *
     * @var string
     */
    protected $_eventObject = 'contenttype_content';

    /**
     * List of errors
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlManager;

    /**
     * @var ContentInterfaceFactory
     */
    protected $contentDataFactory;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Blackbird\ContentManager\Api\ContentMetadataInterface
     */
    protected $metadataService;

    /**
     * @var \Blackbird\ContentManager\Model\ContentType
     */
    protected $contentType;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Blackbird\ContentManager\Model\Factory
     */
    protected $_modelFactory;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Blackbird\ContentManager\Model\Config\Source\Product\Attributes
     */
    protected $_sourceAttributes;

    /**
     * @var \Blackbird\ContentManager\Helper\UrlRewriteGenerator
     */
    protected $_urlRewriteHelper;

    /**
     * @var \Blackbird\ContentManager\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlManager
     * @param \Blackbird\ContentManager\Model\Factory $modelFactory
     * @param \Blackbird\ContentManager\Model\ResourceModel\Content $resource
     * @param ContentInterfaceFactory $contentDataFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param ContentMetadataInterface $metadataService
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param \Blackbird\ContentManager\Model\Config\Source\Product\Attributes $sourceAttributes
     * @param \Blackbird\ContentManager\Helper\UrlRewriteGenerator $urlRewriteHelper
     * @param \Blackbird\ContentManager\Helper\Image $imageHelper
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlManager,
        \Blackbird\ContentManager\Model\Factory $modelFactory,
        \Blackbird\ContentManager\Model\ResourceModel\Content $resource,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        ContentInterfaceFactory $contentDataFactory,
        DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Blackbird\ContentManager\Api\ContentMetadataInterface $metadataService,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Blackbird\ContentManager\Model\Config\Source\Product\Attributes $sourceAttributes,
        \Blackbird\ContentManager\Helper\UrlRewriteGenerator $urlRewriteHelper,
        \Blackbird\ContentManager\Helper\Image $imageHelper,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->metadataService = $metadataService;
        $this->_modelFactory = $modelFactory;
        $this->_storeManager = $storeManager;
        $this->_urlManager = $urlManager;
        $this->contentDataFactory = $contentDataFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->indexerRegistry = $indexerRegistry;
        $this->_blockFactory = $blockFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_sourceAttributes = $sourceAttributes;
        $this->_urlRewriteHelper = $urlRewriteHelper;
        $this->_imageHelper = $imageHelper;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize content model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(ResourceContent::class);
        $this->setIdFieldName(self::ID);

        return parent::_construct();
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [
            self::CACHE_TAG . '_' . $this->getId(),
            ContentType::CACHE_TAG . '_' . $this->getContentType()->getId(),
        ];
    }

    /**
     * Retrieve content model with content data
     *
     * @todo check model data
     * @return \Blackbird\ContentManager\Api\Data\ContentInterface
     */
    public function getDataModel()
    {
        $contentData = $this->getData();
        $contentDataObject = $this->contentDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $contentDataObject,
            $contentData,
            '\Blackbird\ContentManager\Api\Data\ContentInterface'
        );
        $contentDataObject->setId($this->getId());

        return $contentDataObject;
    }

    /**
     * Update content data
     *
     * @todo check model data
     * @param \Blackbird\ContentManager\Api\Data\ContentInterface $content
     * @return $this
     */
    public function updateData($content)
    {
        $contentDataAttributes = $this->dataObjectProcessor->buildOutputDataArray(
            $content,
            '\Blackbird\ContentManager\Api\Data\ContentInterface'
        );

        foreach ($contentDataAttributes as $attributeCode => $attributeData) {
            $this->setDataUsingMethod($attributeCode, $attributeData);
        }

        $customAttributes = $content->getCustomAttributes();
        if ($customAttributes !== null) {
            foreach ($customAttributes as $attribute) {
                $this->setDataUsingMethod($attribute->getAttributeCode(), $attribute->getValue());
            }
        }

        $contentId = $content->getId();
        if ($contentId) {
            $this->setId($contentId);
        }

        // Need to use attribute set or future updates can cause data loss
        if (!$this->getAttributeSetId()) {
            $this->setAttributeSetId(ContentMetadataInterface::ATTRIBUTE_SET_ID_CONTENT);
        }

        return $this;
    }

    /**
     * Retrieve all content attributes
     *
     * @return Attribute[]
     */
    public function getAttributes()
    {
        if (!$this->hasData('attributes')) {
            $this->setData('attributes', $this->getResource()->loadAllAttributes($this)->getSortedAttributes());
        }

        return $this->getData('attributes');
    }

    /**
     * Get content attribute model object
     *
     * @param   string $attributeCode
     * @return  \Blackbird\ContentManager\Model\ResourceModel\Attribute | null
     */
    public function getAttribute($attributeCode)
    {
        $attribute = null;
        $attributes = $this->getAttributes();

        if (isset($attributes[$attributeCode])) {
            $attribute = $attributes[$attributeCode];
        }

        return $attribute;
    }

    /**
     * Retrieve store where content was created
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->getStoreId());
    }

    /**
     * Get the available stores for this content
     *
     * @return array
     */
    public function getStores()
    {
        if (!$this->hasData('stores')) {
            $this->setData('stores', $this->getResource()->getAvailableStores($this->getId()));
        }

        return $this->getData('stores');
    }

    /**
     * Retrieve all store views id of the content
     *
     * @return array
     */
    public function getStoreIds()
    {
        if (!$this->hasData('store_ids')) {
            $this->setData('store_ids', $this->getResource()->lookupStoreIds($this->getId()));
        }

        return $this->getData('store_ids');
    }

    /**
     * Set store to content
     *
     * @param \Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore(\Magento\Store\Model\Store $store)
    {
        $this->setStoreId($store->getId());
        $this->setWebsiteId($store->getWebsite()->getId());

        return $this;
    }

    /**
     * Check if a store exists for this content
     *
     * @param int $storeId
     * @return boolean
     */
    public function existsForStore($storeId)
    {
        return $this->getResource()->existsForStore($this->getId(), $storeId);
    }

    /**
     * Retrieve the url (UrlKey as Request Path)
     *
     * @todo retrieve the request path (url rewrite)
     * @return string
     */
    public function getUrl()
    {
        return $this->getUrlKey();
    }

    /**
     * Retrieve the current content url
     *
     * @todo rename to getContentUrl (discuss)
     * @todo refactor
     * @param Store|int|string $store
     * @return string
     */
    public function getLinkUrl($store = null, $preview = false)
    {
        $query = [];
        if ($preview) {
            $query['preview'] = 1;
        }
        if ($store instanceof \Magento\Store\Model\StoreManagerInterface) {
            $store = $store->getCode();
        } elseif (is_numeric($store)) {
            $store = $this->_storeManager->getStore($store)->getCode();
        }
        if (!empty($store) && is_string($store)) {
            $query['___store'] = $store;
        }

        return $this->_urlManager->getDirectUrl($this->getUrlKey(), ['_query' => $query]);
    }

    /**
     * Validate content attribute values.
     *
     * @return bool|string[]
     */
    public function validate()
    {
        $errors = [];
        if (!empty(trim($this->getCtId()))) {
            $errors[] = __('Please link to a content type ID (attribute ct_id is required).');
        }

        $transport = new \Magento\Framework\DataObject(['errors' => $errors]);
        $this->_eventManager->dispatch('content_validate', ['content' => $this, 'transport' => $transport]);

        return empty($transport->getErrors()) ? true : $transport->getErrors();
    }

    /**
     * Add error
     *
     * @todo is it used ?
     * @param mixed $error
     * @return $this
     */
    public function addError($error)
    {
        $this->_errors[] = $error;

        return $this;
    }

    /**
     * Retrieve errors
     *
     * @todo is it used ?
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Reset errors array
     *
     * @todo is it used ?
     * @return $this
     */
    public function resetErrors()
    {
        $this->_errors = [];

        return $this;
    }

    /**
     * Processing object after save data
     *
     * @return $this
     */
    public function afterSave()
    {
        // Rewrite url generation
        $this->generateUrls();

        // Fulltext indexer
        $this->_getResource()->addCommitCallback([$this, 'reindex']);

        return parent::afterSave();
    }

    /**
     * Processing object before delete data
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeDelete()
    {
        // Delete UrlRewrite
        $this->_urlRewriteHelper->deleteUrlRewrite(self::ENTITY, $this->getId());

        return parent::beforeDelete();
    }

    /**
     * Init indexing process after content delete
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function afterDeleteCommit()
    {
        $this->reindex();

        return parent::afterDeleteCommit();
    }

    /**
     * Init indexing process after content save
     *
     * @return void
     */
    public function reindex()
    {
        /** @var \Magento\Framework\Indexer\IndexerInterface $indexer */
        $indexer = $this->indexerRegistry->get(Indexer\Fulltext::INDEXER_ID);
        if (!$indexer->isScheduled()) {
            $indexer->reindexRow($this->getId());
        }
    }

    /**
     * Get content created at date timestamp
     *
     * @return int|null
     */
    public function getCreatedAtTimestamp()
    {
        $date = $this->getCreatedAt();
        if ($date) {
            return (new \DateTime($date))->getTimestamp();
        }

        return null;
    }

    /**
     * Reset all model data
     *
     * @return $this
     */
    public function reset()
    {
        $this->unsetData();
        $this->setOrigData();

        return $this;
    }

    /**
     * Return Entity Type instance
     *
     * @return \Magento\Eav\Model\Entity\Type
     */
    public function getEntityType()
    {
        return $this->getResource()->getEntityType();
    }

    /**
     * Return Entity Type Id value
     *
     * @return int
     */
    public function getEntityTypeId()
    {
        $entityTypeId = $this->getData(self::ENTITY_TYPE_ID);
        if (!$entityTypeId) {
            $entityTypeId = $this->getEntityType()->getId();
            $this->setData(self::ENTITY_TYPE_ID, $entityTypeId);
        }

        return $entityTypeId;
    }

    /**
     * Delete values for the current store
     *
     * @return void
     */
    public function deleteCurrentStoreAttributes()
    {
        $attributeIds = [];

        foreach ($this->getData() as $key => $value) {
            if (!in_array($key, $this->_protectedAttributes())) {
                $attribute = $this->getAttribute($key);
                if ($attribute) {
                    $attributeIds[] = $attribute->getId();
                }
            }
        }

        $this->getResource()->deleteAttributesByStore($this->getId(), $this->getStoreId(), $attributeIds);
        // Delete UrlRewrite for the current store
        $this->_urlRewriteHelper->deleteUrlRewrite(self::ENTITY, $this->getId(), $this->getStoreId());
    }

    /**
     * Generate the url rewrite
     *
     * @todo move in url manager model
     * @todo refactor whole method
     * @return $this
     */
    public function generateUrls()
    {
        if (empty($this->getId()) || !$this->hasData(self::URL_KEY)) {
            return $this;
        }
        $urls = [];

        if ($this->getStoreId() == Store::DEFAULT_STORE_ID) {
            $this->_urlRewriteHelper->deleteUrlRewrite(self::ENTITY, $this->getId());

            if ($this->isObjectCopied()) {
                foreach ($this->_storeManager->getStores() as $store) {
                    $urls[] = [
                        'entity_type' => self::ENTITY,
                        'entity_id' => $this->getId(),
                        'request_path' => $this->getUrlKey(),
                        'target_path' => 'contentmanager/index/content/content_id/' . $this->getId(),
                        'store_id' => $store->getId(),
                    ];
                }
            } else {
                // todo refactor, do not modify current object instance
                foreach ($this->_storeManager->getStores() as $store) {
                    $this->setStoreId($store->getId());
                    $this->getResource()->load($this, $this->getId());

                    $urls[] = [
                        'entity_type' => self::ENTITY,
                        'entity_id' => $this->getId(),
                        'request_path' => $this->getUrlKey(),
                        'target_path' => 'contentmanager/index/content/content_id/' . $this->getId(),
                        'store_id' => $this->getStoreId(),
                    ];
                }
            }
        } else {
            $this->_urlRewriteHelper->deleteUrlRewrite(self::ENTITY, $this->getId(), $this->getStoreId());
            $urls[] = [
                'entity_type' => self::ENTITY,
                'entity_id' => $this->getId(),
                'request_path' => $this->getUrlKey(),
                'target_path' => 'contentmanager/index/content/content_id/' . $this->getId(),
                'store_id' => $this->getStoreId(),
            ];
        }

        $this->_urlRewriteHelper->addUrlRewrites($urls);

        return $this;
    }

    /**
     * Return the protected attributes
     *
     * @return array
     */
    protected function _protectedAttributes()
    {
        return [
            self::ID,
            self::ENTITY_TYPE_ID,
            self::CT_ID,
            self::CREATED_AT,
            self::UPDATED_AT,
            self::STORE_ID,
        ];
    }

    /**
     * Return list of attributes with associative key(text)/value
     *
     * @return array
     */
    protected function _associativeAttributes()
    {
        return [
            'drop_down',
            'radio',
            'multiple',
            'checkbox',
            'attribute',
            'currency',
            'locale',
        ];
    }

    /**
     * Return the linked Content Type
     *
     * @return \Blackbird\ContentManager\Model\ContentType
     */
    public function getContentType()
    {
        if (!$this->hasData('content_type') && $this->getCtId()) {
            $this->setData('content_type', $this->_modelFactory->create(ContentType::class)->load($this->getCtId()));
        }

        return $this->getData('content_type');
    }

    /**
     * Render anything
     *
     * @param mixed $element the element to render
     * @param array $params extra parameters
     * @return string
     */
    public function render($element, $params = null)
    {
        $customField = null;
        $isPageTitle = false;
        $layout = null;
        $html = '';

        // Is identifier
        if (is_string($element)) {
            $customField = $this->getContentType()->getCustomFieldCollection()
                ->addFieldToFilter(CustomField::IDENTIFIER, $element)
                ->getFirstItem();

            $identifier = $element;

            // Is custom field model
        } elseif ($element instanceof CustomField) {
            $customField = $this->getContentType()->getCustomFieldCollection()
                ->addFieldToFilter(CustomField::IDENTIFIER, $element->getIdentifier())
                ->getFirstItem();

            $identifier = $element->getIdentifier();

            // Is layout field model
        } elseif ($element instanceof FieldInterface) {
            if (!$element->getCustomFieldId()) {
                $isPageTitle = true;
                $identifier = 'title';
            } else {
                $customField = $this->getContentType()->getCustomFieldCollection()
                    ->addFieldToFilter('main_table.' . CustomField::ID, $element->getCustomFieldId())
                    ->getFirstItem();

                $identifier = $customField->getIdentifier();
            }
            $layout = $element;

            // Is layout block model
        } elseif ($element instanceof BlockInterface) {
            $html = $this->renderLayoutBlock($element, ['params' => $params]);

            // Is layout block model
        } elseif ($element instanceof GroupInterface) {
            $html = $this->renderLayoutGroup($element, ['params' => $params]);

        }

        // Render custom field
        if ($customField || $isPageTitle) {
            $html = $this->renderCustomField(
                $identifier,
                [
                    'custom_field' => $customField,
                    'params' => $params,
                    'layout' => $layout,
                ]
            );
        }

        // Return html output
        return $html;
    }

    /**
     * Render a custom field
     *
     * @param string $identifier
     * @param string|array $params
     * @return string
     */
    public function renderCustomField($identifier, $params)
    {
        if (!empty($params['custom_field'])) {
            $customField = $params['custom_field'];
            $type = $customField->getType();
        } else {
            $type = 'field';
        }

        $block = $this->_blockFactory
            ->createBlock(\Blackbird\ContentManager\Block\View\Field::class)
            ->setData(
                [
                    'identifier' => $identifier,
                    'type' => $type,
                    'content' => $this,
                    'params' => $params,
                ]
            );

        return $block->prepareTemplate()->toHtml();
    }

    /**
     * Render a group of layout items
     *
     * @param string $layoutGroup
     * @param string|array $params
     * @return string
     */
    public function renderLayoutGroup($layoutGroup, $params)
    {
        $result = '';

        // Render header
        $block = $this->_blockFactory
            ->createBlock(\Blackbird\ContentManager\Block\View\Group\Header::class)
            ->setData(
                [
                    'layout_group' => $layoutGroup,
                    'content' => $this,
                    'params' => $params,
                ]
            );

        $result .= $block->prepareTemplate()->toHtml();

        // Render childen
        foreach ($layoutGroup->getChildren() as $layoutChild) {
            $result .= $this->render($layoutChild);
        }

        // Render footer
        $block = $this->_blockFactory
            ->createBlock(\Blackbird\ContentManager\Block\View\Group\Footer::class)
            ->setData(
                [
                    'layout_group' => $layoutGroup,
                    'content' => $this,
                    'params' => $params,
                ]
            );

        $result .= $block->prepareTemplate()->toHtml();

        return $result;
    }

    /**
     * Render a layout cms block
     *
     * @param string $layoutBlock
     * @param string|array $params
     * @return string
     */
    public function renderLayoutBlock($layoutBlock, $params)
    {
        $block = $this->_blockFactory
            ->createBlock(\Blackbird\ContentManager\Block\View\Block::class)
            ->setData(
                [
                    'layout_block' => $layoutBlock,
                    'content' => $this,
                    'params' => $params,
                ]
            );

        return $block->prepareTemplate()->toHtml();
    }

    /**
     * Get the url of the given file identifier
     *
     * @param string $identifier
     * @return string
     */
    public function getFile($identifier)
    {
        return $this->retrieveFileUrl($identifier, ContentType::CT_FILE_FOLDER);
    }

    /**
     * Get the url of the given image identifier
     *
     * @param string $identifier
     * @param int $width
     * @param int $height
     * @param bool $keepAspectRatio
     * @param bool $cropped
     * @return string
     */
    public function getImage($identifier, $width = null, $height = null, $keepAspectRatio = false, $cropped = true)
    {
        $imageField = $this->getContentType()
            ->getCustomFieldCollection()
            ->addFieldToFilter(CustomField::IDENTIFIER, $identifier)
            ->addFieldToFilter(CustomField::TYPE, 'image');

        if ($imageField->count()) {
            $imageField = $imageField->getFirstItem();
        } else {
            return '';
        }

        // Use Framework Image Resize
        if (!empty($width) && !empty($height)) {
            $url = $this->getModifiedImage($imageField, $width, $height, $keepAspectRatio, $cropped);
        } else {
            $url = $this->getUnmodifiedImage($imageField, $cropped);
        }

        return $url;
    }

    /**
     * Retrieve the url of the image (resized)
     *
     * @param CustomField $imageField
     * @param int $width
     * @param int $height
     * @param bool $forceKeepAspectRatio
     * @param bool $cropped
     * @return string
     */
    protected function getModifiedImage(CustomField $imageField, $width = null, $height = null, $forceKeepAspectRatio = false, $cropped = true)
    {
        $path = '';

        if ($cropped === true && $imageField->getCrop()) {
            $path .= ContentType::CT_IMAGE_CROPPED_FOLDER;
        }

        $file = $this->getData($imageField->getIdentifier());
        $keepAspectRatio = is_bool($forceKeepAspectRatio) ? $forceKeepAspectRatio : $imageField->getKeepAspectRatio();
        if (!empty($imageField->getFilePath())) {
            $path .= $imageField->getFilePath();
        }

        $this->_imageHelper->init($file, $path);
        $url = $this->_imageHelper->resize($width, $height, $keepAspectRatio);

        return $url;
    }

    /**
     * Retrieve the url of the image (original size)
     *
     * @param CustomField $imageField
     * @param bool $cropped
     * @return string
     */
    protected function getUnmodifiedImage(CustomField $imageField, $cropped = true)
    {
        $identifier = $imageField->getIdentifier();

        if ($cropped === true && $imageField->getCrop()) {
            $path = ContentType::CT_FILE_FOLDER . ContentType::CT_IMAGE_CROPPED_FOLDER;
        } else {
            $path = ContentType::CT_FILE_FOLDER;
        }

        $url = $this->retrieveFileUrl($identifier, $path);
        if (empty($url)) {
            $url = $this->getImage($identifier);
        }

        return $url;
    }

    /**
     * Retrieve an url for a given identifier file and path
     *
     * @param string $identifier
     * @param string $path
     * @return string
     */
    protected function retrieveFileUrl($identifier, $path = '')
    {
        $filename = $this->getData($identifier);
        $customField = $this->getContentType()->getCustomFieldCollection()
            ->addFieldToFilter(CustomField::IDENTIFIER, $identifier)
            ->addFieldToFilter(CustomField::TYPE, ['image', 'file'])
            ->getFirstItem();

        if ($customField && !empty($customField->getFilePath())) {
            $path .= $customField->getFilePath();

            if (substr($path, -1) !== '/') {
                $path .= '/';
            }
        }

        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $path . $filename;
    }

    /**
     * Retrieve the associative text for an attribute value
     *
     * @todo check: used the backend attribute Array for attributes
     * @param string $identifier Attribute identifier
     * @return string
     */
    public function getAttributeText($identifier)
    {
        $result = $this->getData($identifier);
        $customField = $this->getContentType()->getCustomFieldCollection()
            ->addFieldToFilter(CustomField::IDENTIFIER, $identifier)
            ->getFirstItem();

        // If attribute is type of select or product attribute
        if ($customField && in_array($customField->getType(), $this->_associativeAttributes())) {
            $result = [];
            $values = explode(',', $this->getData($identifier));

            if ($customField->getType() === 'attribute') {
                foreach ($values as $value) {
                    $attributeDetails = $this->_sourceAttributes->getProductAttributeCollection()
                        ->addFieldToFilter('attribute_code', $customField->getData(CustomField::ATTRIBUTE))
                        ->getFirstItem();

                    $result[] = $attributeDetails->getSource()->getOptionText($value);
                }
            } else {
                foreach ($values as $value) {
                    $option = $customField->getOptionCollection()
                        ->addFieldToFilter(Option::VALUE, $value)
                        ->getFirstItem();

                    $result[] = ($option && !empty($option->getTitle())) ? $option->getTitle() : $value;
                }
            }

            $result = implode(',', $result);
        }

        return $result;
    }

    /**
     * Retrieve the product collection of a product field
     *
     * @param string $identifier
     * @param string|array $attributes
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection($identifier, $attributes = [])
    {
        return $this->_productCollectionFactory->create()
            ->addAttributeToSelect($attributes)
            ->addAttributeToFilter(Product::SKU, $this->getDataAsArray($identifier));
    }

    /**
     * Retrieve the content collection of a content field
     *
     * @param string $identifier
     * @param string|array $attributes
     * @return \Blackbird\ContentManager\Model\ResourceModel\Content\Collection
     */
    public function getContentCollection($identifier, $attributes = [])
    {
        return $this->getCollection()
            ->addAttributeToSelect($attributes)
            ->addAttributeToFilter(self::ID, $this->getDataAsArray($identifier));
    }

    /**
     * Retrieve the data as an array
     *
     * @param string $identifier
     * @return array
     */
    public function getDataAsArray($identifier)
    {
        return array_map('trim', explode(',', $this->getData($identifier)));
    }
}
