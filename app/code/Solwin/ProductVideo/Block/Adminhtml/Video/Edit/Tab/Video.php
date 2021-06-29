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
namespace Solwin\ProductVideo\Block\Adminhtml\Video\Edit\Tab;

class Video extends \Magento\Backend\Block\Widget\Form\Generic
implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * Status options
     *
     * @var \Solwin\ProductVideo\Model\Video\Source\Status
     */
    protected $_statusOptions;

    /**
     * constructor
     *
     * @param \Solwin\ProductVideo\Model\Video\Source\Status $statusOptions
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Solwin\ProductVideo\Model\Video\Source\Status $statusOptions,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_statusOptions    = $statusOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Solwin\ProductVideo\Model\Video $video */
        $video = $this->_coreRegistry->registry('solwin_productvideo_video');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('video_');
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        $form->setFieldNameSuffix('video');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('General Setting'),
                'class'  => 'fieldset-wide'
            ]
        );
        $fieldset->addType(
                'image',
                'Solwin\ProductVideo\Block\Adminhtml\Video\Helper\Image'
                );
        $fieldset->addType(
                'file',
                'Solwin\ProductVideo\Block\Adminhtml\Video\Helper\File'
                );
        if ($video->getId()) {
            $fieldset->addField(
                'video_id',
                'hidden',
                ['name' => 'video_id']
            );
        }
        $fieldset->addField(
            'title',
            'text',
            [
                'name'  => 'title',
                'label' => __('Video Title'),
                'title' => __('Video Title'),
                'required' => true,
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->_systemStore
                    ->getStoreValuesForForm(false, true),
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form'
                    . '\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                [
                    'name' => 'stores[]',
                    'value' => $this->_storeManager->getStore(true)->getId()
                    ]
            );
            $video->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField(
            'status',
            'select',
            [
                'name'  => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'values' => array_merge(['' => ''],
                        $this->_statusOptions->toOptionArray()),
            ]
        );

        $videoData = $this->_session
                ->getData('solwin_productvideo_video_data', true);
        if ($videoData) {
            $video->addData($videoData);
        } else {
            if (!$video->getId()) {
                $video->addData($video->getDefaultValues());
            }
        }
        $form->addValues($video->getData());

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}