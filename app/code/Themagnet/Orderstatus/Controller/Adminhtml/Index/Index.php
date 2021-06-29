<?php
namespace Themagnet\Orderstatus\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Backend\App\Action
{
    protected $_soap;
    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Themagnet\Orderstatus\Model\Orderstatus $soap
    )
    {
        parent::__construct($context);
        $this->_soap = $soap;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Themagnet_Orderstatus::orderstatus');
        $resultPage->getConfig()->getTitle()->prepend(__('Order Status'));
        $resultPage->getConfig()->getTitle()->prepend(__('Order Status'));
        $resultPage->addBreadcrumb(__('Order Status'), __('Order Status'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Themagnet_Orderstatus::orderstatus');
    }

}