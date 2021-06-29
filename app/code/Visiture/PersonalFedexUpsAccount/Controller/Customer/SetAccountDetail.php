<?php

namespace Visiture\PersonalFedexUpsAccount\Controller\Customer;

use Magento\Framework\Controller\ResultFactory;

class SetAccountDetail extends \Magento\Framework\App\Action\Action
{
    protected $_customerSession;
    protected $_checkoutSession;
    protected $_quote;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        
        if(!$this->_customerSession->isLoggedIn()){
            $resultJson->setData(["success"=>false,"msg"=>__("customer session terminated, please login first.")]);
            return $resultJson;
        }

        $post = $this->getRequest()->getParams();

        if (!empty($post)) {

            $this->getQuote()->setData("personal_ac_number",$post['personal_ac_number']);
            $this->getQuote()->setData("personal_ac_type",$post['personal_ac_type']);
            $this->getQuote()->save();
            $resultJson->setData(["success"=>true]);
            return $resultJson;
        }

        $resultJson->setData(["success"=>false,"msg"=>__("Invalid parameters.")]);
        return $resultJson;
    }

    protected function getQuote()
    {
        if (null === $this->_quote) {
            $this->_quote = $this->_checkoutSession->getQuote();
        }
        return $this->_quote;
    }
}