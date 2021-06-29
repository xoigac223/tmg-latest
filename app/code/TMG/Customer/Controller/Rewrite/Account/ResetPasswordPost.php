<?php


namespace TMG\Customer\Controller\Rewrite\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use TMG\Customer\Helper\Customer as CustomerHelper;

class ResetPasswordPost extends \Magento\Customer\Controller\Account\ResetPasswordPost
{
    protected $customerHelper;
    
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        CustomerHelper $customerHelper
    ){
        parent::__construct($context, $customerSession, $accountManagement, $customerRepository);
        $this->customerHelper = $customerHelper;
    }
    
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resetPasswordToken = (string)$this->getRequest()->getQuery('token');
        $password = (string)$this->getRequest()->getPost('password');
        $passwordConfirmation = (string)$this->getRequest()->getPost('password_confirmation');
    
        if ($password !== $passwordConfirmation) {
            $this->messageManager->addError(__("New Password and Confirm New Password values didn't match."));
            $resultRedirect->setPath('*/*/createPassword', ['token' => $resetPasswordToken]);
            return $resultRedirect;
        }
        if (iconv_strlen($password) <= 0) {
            $this->messageManager->addError(__('Please enter a new password.'));
            $resultRedirect->setPath('*/*/createPassword', ['token' => $resetPasswordToken]);
            return $resultRedirect;
        }
    
        try {
            
            $email = $this->customerHelper->resetApiPwd($resetPasswordToken,$password);
            
            // Change Magento Password ???
//            $customer = $this->customerRepository->get($email);
            
            $this->session->unsRpToken();
            $this->messageManager->addSuccess(__('You updated your password.'));
            $resultRedirect->setPath('*/*/login');
            return $resultRedirect;
        } catch (InputException $e) {
            $this->messageManager->addError($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addError($error->getMessage());
            }
        } catch (\Exception $exception) {
            $this->messageManager->addError(__('Something went wrong while saving the new password.'));
        }
        $resultRedirect->setPath('*/*/createPassword', ['token' => $resetPasswordToken]);
        return $resultRedirect;
    }
    
}