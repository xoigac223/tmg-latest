<?php
namespace TMG\Customer\Controller\Rewrite\Account;


use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

use TMG\Customer\Helper\Customer as CustomerHelper;

class CreatePassword extends \Magento\Customer\Controller\Account\CreatePassword
{
    protected $customerHelper;
    
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        AccountManagementInterface $accountManagement,
        CustomerHelper $customerHelper
    ){
        parent::__construct($context, $customerSession, $resultPageFactory, $accountManagement);
        $this->customerHelper = $customerHelper;
    }
    
    public function execute()
    {
    
//        2723a330-f238-4f92-91f8-4c226cbdcee0
//        http://themagnetgroup.local/default/customer/account/createpassword/?token=2723a330-f238-4f92-91f8-4c226cbdcee0
        
        $resetPasswordToken = (string)$this->getRequest()->getParam('token',false);
        $isDirectLink = $resetPasswordToken != false;
        
        if (!$isDirectLink) {
            $resetPasswordToken = (string)$this->session->getRpToken();
        }
    
        try {
            
            $this->customerHelper->validateApiResetPwdRequestId($resetPasswordToken);
        
            if ($isDirectLink) {
                $this->session->setRpToken($resetPasswordToken);
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/createpassword');
                return $resultRedirect;
            } else {
                /** @var \Magento\Framework\View\Result\Page $resultPage */
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getLayout()->getBlock('resetPassword')
                    ->setResetPasswordLinkToken($resetPasswordToken);
                return $resultPage;
            }
            
        } catch (\Exception $exception) {
            $this->messageManager->addError(__('Your password reset link has expired.'));
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/forgotpassword');
            return $resultRedirect;
        }
    
    }
}