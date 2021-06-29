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
namespace Solwin\ProductVideo\Controller\Adminhtml\Video;

use Solwin\ProductVideo\Model\ResourceModel\Video\CollectionFactory;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Mass Action Filter
     * 
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $_filter;

    /**
     * Collection Factory
     * 
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * constructor
     * 
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $filter,
        CollectionFactory $collectionFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_filter            = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }


    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->_filter
                ->getCollection($this->_collectionFactory->create());

        $delete = 0;
        foreach ($collection as $item) {
            /** @var \Solwin\ProductVideo\Model\Video $item */
            $item->delete();
            $delete++;
        }
        $this->messageManager->addSuccess(__(
                'A total of %1 record(s) have been deleted.', 
                $delete
                ));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(
                \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
                );
        return $resultRedirect->setPath('*/*/');
    }
}