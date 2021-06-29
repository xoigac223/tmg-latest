<?php

namespace Shreeji\Duplicateimage\Controller\Adminhtml\Manage;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action {

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Shreeji_Duplicateimage::duplicateimages';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
    Context $context, PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute() {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Shreeji_Duplicateimage::find_duplicate');
        $resultPage->addBreadcrumb(__('Manage Duplicate Images'), __('Manage Duplicate Images'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Duplicate Images'));
        return $resultPage;
    }

}
