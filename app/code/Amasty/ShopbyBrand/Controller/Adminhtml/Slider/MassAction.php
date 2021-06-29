<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Controller\Adminhtml\Slider;

use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\Collection;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action;

/**
 * Class MassAction
 * @package Amasty\ShopbyBrand\Controller\Adminhtml\Slider
 * @author Evgeni Obukhovsky
 */
class MassAction extends Action
{
    /** @var Filter */
    protected $filter;

    protected $context;

    /**
     * @param Context $context
     * @param Filter $filter
     */
    public function __construct(Context $context, Filter $filter)
    {
        $this->filter = $filter;
        $this->context = $context;

        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $value = (bool) $this->getRequest()->getParam('value');
        $filters = $this->getRequest()->getParam('filters');
        $storeId = isset($filters['store_id']) ? $filters['store_id'] : 0;
        $rawCollection = $this->_objectManager->create(Collection::class);
        $collection = $this->filter->getCollection($rawCollection);
        $collectionSize = $collection->getSize();

        if ($collectionSize) {
            foreach ($collection as $item) {
                /** @var  \Amasty\ShopbyBase\Model\OptionSetting $model */
                $model = $rawCollection = $this->_objectManager->create(\Amasty\ShopbyBase\Model\OptionSetting::class);
                $model->saveData(
                    $item->getData('filter_code'),
                    $item->getData('value'),
                    $storeId,
                    ['is_featured' => $value]
                );
            }
            if ($value) {
                $message = __('A total of %1 brand(s) have been added to slider.', $collectionSize);
            } else {
                $message = __('A total of %1 brand(s) have been removed from slider.', $collectionSize);
            }

            $this->messageManager->addSuccess($message);
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShopbyBrand::slider');
    }
}
