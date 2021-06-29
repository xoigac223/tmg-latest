<?php
namespace Themagnet\Productimport\Controller\Adminhtml\Import;
use Magento\Framework\App\Filesystem\DirectoryList;
 
class Save extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;
    protected $filesystem;
    protected $_ftp;
    protected $_importproduct;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_messageManager = $context->getMessageManager();
        // $this->redirectFactory = $context->getRedirectFactory();
    }

    public function execute()
    {
        echo "hello";
        //$this->_importproduct->createFile();       
    }
    
}