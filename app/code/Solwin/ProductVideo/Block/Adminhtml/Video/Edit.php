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
namespace Solwin\ProductVideo\Block\Adminhtml\Video;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * constructor
     * 
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize Video edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'video_id';
        $this->_blockGroup = 'Solwin_ProductVideo';
        $this->_controller = 'adminhtml_video';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Video'));
        $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->update('delete', 'label', __('Delete Video'));
    }
    /**
     * Retrieve text for header element depending on loaded Video
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var \Solwin\ProductVideo\Model\Video $video */
        $video = $this->_coreRegistry->registry('solwin_productvideo_video');
        if ($video->getId()) {
            return __("Edit Video '%1'", $this->escapeHtml($video->getTitle()));
        }
        return __('New Video');
    }
}