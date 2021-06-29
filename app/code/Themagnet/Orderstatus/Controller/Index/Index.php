<?php
namespace Themagnet\Orderstatus\Controller\Index;

use Magento\Customer\Model\Session;

class Index extends \Magento\Customer\Controller\AbstractAccount
{
    protected $_orderstatus;

    protected $_customers;

    protected $_helper;
    protected $resultPageFactory;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Themagnet\Orderstatus\Model\Orderstatus $inventory,
        \Magento\Customer\Model\Customer $customers,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Themagnet\Orderstatus\Helper\Data $helper,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->_customers = $customers;
        $this->_orderstatus = $inventory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
    }
   
    public function execute()
    {
        if ($this->_helper->getEnableModule()) {
            $this->resultPage = $this->resultPageFactory->create();
            $this->resultPage->getConfig()->getTitle()->prepend(__('Check Order Status'));
            return $this->resultPage;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('home');
            return $resultRedirect;
        }
    }
}
