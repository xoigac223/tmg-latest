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
namespace Blackbird\ContentManager\Model\Content;

class Copier
{
    /**
     * @var \Blackbird\ContentManager\Model\ContentFactory
     */
    protected $_contentFactory;

    /**
     * @param \Blackbird\ContentManager\Model\ContentFactory $contentFactory
     */
    public function __construct(
        \Blackbird\ContentManager\Model\ContentFactory $contentFactory
    ) {
        $this->_contentFactory = $contentFactory;
    }

    /**
     * Create content duplicate
     *
     * @param \Blackbird\ContentManager\Model\Content $content
     * @return \Blackbird\ContentManager\Model\Content
     */
    public function copy(\Blackbird\ContentManager\Model\Content $content)
    {
        /** @var \Blackbird\ContentManager\Model\Content */
        $duplicate = $this->_contentFactory->create();

        foreach ($content->getStoreIds() as $storeId) {
            $content->setStoreId($storeId);
            $content = $content->load($content->getId());
            $duplicateId = $duplicate->getId();

            // Duplicate the content
            $duplicate->setData($content->getData());
            $duplicate->setStatus(0);
            $duplicate->setId($duplicateId);
            $duplicate->isObjectCopied(true);

            if ($duplicate->isObjectNew()) {
                $duplicate->setCreatedAt(null);
                $duplicate->setUpdatedAt(null);
            }

            // Generated a new url key
            $isDuplicateSaved = false;
            do {
                $urlKey = $duplicate->getUrlKey();
                $urlKey = preg_match('/(.*)-(\d+)$/', $urlKey, $matches)
                    ? $matches[1] . '-' . ($matches[2] + 1)
                    : $urlKey . '-1';
                $duplicate->setUrlKey($urlKey);
                try {
                    $duplicate->save();
                    $isDuplicateSaved = true;
                } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
                    // Silence is golden
                }
            } while (!$isDuplicateSaved);
        }

        return $duplicate;
    }
}
