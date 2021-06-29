<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Index;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class login extends \Magento\Customer\Controller\AbstractAccount {

    /** @var AccountManagementInterface */
    protected $customerAccountManagement;

    /** @var Validator */
    protected $formKeyValidator;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var Session
     */
    protected $session;
    protected $image;    

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerUrl $customerHelperData
     * @param Validator $formKeyValidator
     * @param AccountRedirect $accountRedirect
     */
    public function __construct(
    Context $context,
            Session $customerSession,
            AccountManagementInterface $customerAccountManagement,
            CustomerUrl $customerHelperData,
            Validator $formKeyValidator,
            AccountRedirect $accountRedirect,
            \Magento\Catalog\Helper\Image $image
    ) {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerHelperData;
        $this->formKeyValidator = $formKeyValidator;
        $this->accountRedirect = $accountRedirect;
        $this->image = $image;
        parent::__construct($context);
    }

    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute() {
        if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
//return;
        }
        $result = array('status' => 'fail');
        if ($this->getRequest()->isPost()) {

            /* $params = $this->getRequest()->getParams();
              $login = $params['data'];

              var_dump($login['username']);
              die; */
            $d = $this->getRequest()->getParams();
           
            if (!empty($d['login']['username']) && !empty($d['login']['password'])) {
                try {

                    $customer = $this->customerAccountManagement->authenticate($d['login']['username'],
                            $d['login']['password']);
                    $customer_id = $customer->getId();

                    $message = __('You have loggged in');


                    
                    $this->session->setCustomerDataAsLoggedIn($customer);
                    $this->session->regenerateId();
                        $result['status'] = 'success';
                  
                    /* echo $params['data']['login[username'];
                      echo $params['data']['login[password'];

                      var_dump($customer);

                      die; */
                } catch (EmailNotConfirmedException $e) {
                    $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                    $message = __(
                            'This account is not confirmed.' .
                            ' <a href="%1">Click here</a> to resend confirmation email.',
                            $value
                    );
                    $result['error'] = $message;
                    $result['status'] = 'fail';
                    //$this->session->setUsername($d['login']['username']);
                } catch (AuthenticationException $e) {
                    $message = __('Invalid login or password.');
                    $result['error'] = $message;
                    $result['status'] = 'fail';
                    //$this->session->setUsername($d['login']['username']);
                } catch (\Exception $e) {
                    $message = __('Unknown Error Occured.');
                    $result['error'] = $message;
                    $result['status'] = 'fail';
                }
            } else {
                $message = __('A login and a password are required.');
                $result['error'] = $message;
                $result['status'] = 'fail';
            }
        }

        $this->getResponse()->setBody(json_encode($result));
    }
}
   
