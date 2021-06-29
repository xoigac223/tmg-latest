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

/**
 * @method Video setTitle($title)
 * @method Video setVideoType($videoType)
 * @method Video setVideoUrl($videoUrl)
 * @method Video setVideoFile($videoFile)
 * @method Video setThumbnail($thumbnail)
 * @method Video setContent($content)
 * @method Video setStatus($status)
 * @method mixed getTitle()
 * @method mixed getVideoType()
 * @method mixed getVideoUrl()
 * @method mixed getVideoFile()
 * @method mixed getThumbnail()
 * @method mixed getContent()
 * @method mixed getStatus()
 * @method Video setCreatedAt(\string $createdAt)
 * @method string getCreatedAt()
 * @method Video setUpdatedAt(\string $updatedAt)
 * @method string getUpdatedAt()
 */
class Video extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'solwin_productvideo_video';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'solwin_productvideo_video';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'solwin_productvideo_video';


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Solwin\ProductVideo\Model\ResourceModel\Video');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * get entity default values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];
        $values['status'] = '1';
        return $values;
    }

    public function getProducts(\Solwin\ProductVideo\Model\Video $object)
    {
        $tbl = $this->getResource()->getTable("solwin_productvideo_video");
        $select = $this->getResource()->getConnection()->select()->from(
            $tbl,
            ['products']
        )
        ->where(
            'video_id = ?',
            (int)$object->getId()
        );
        $products = $this->getResource()->getConnection()->fetchCol($select);
        if ($products) {
            $products = explode('&', $products[0]);
	}
        return $products;
    }
}
