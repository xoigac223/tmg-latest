<?php
namespace Themagnet\Productimport\Controller\Adminhtml\Import;
 
class Import extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;
    protected $_ftp;
    protected $_importproduct;
    protected $_helper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Themagnet\Productimport\Model\Ftpfiles $ftp,
        \Themagnet\Productimport\Helper\Data $helper,
        \Themagnet\Productimport\Model\Importproduct $importproduct
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_ftp = $ftp;
        $this->_importproduct = $importproduct;
        $this->_helper = $helper;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParam('file');
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->_importproduct->importImages();
        /*if($data == 'simple'){
            $this->_importproduct->createSimpleFile();
            $this->messageManager->addSuccess(__('%1 product successfully imported.',$data)); 
        }elseif($data == 'config'){
            $this->_importproduct->createConfigFile();
            $this->messageManager->addSuccess(__('%1 product successfully imported.',$data)); 
        }else{
            $this->messageManager->addError(__('file not found')); 
        } */
       // return $resultRedirect->setPath('*/*/'); 
    }
}