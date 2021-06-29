<?php
namespace TMG\Customer\Controller\Json;

use Magento\Framework\Exception\LocalizedException;
use TMG\Customer\Controller\Json;

class accountIdLookup extends Json
{
    public function execute()
    {
        
        $response = [
            'error' => false,
            'message' => ''
        ];
        
        try {
            
            if(!$content = $this->getRequest()->getContent()) {
                throw new LocalizedException(__('No Params'));
            }
            
            $params = $this->jsonHelper->jsonDecode($this->getRequest()->getContent());
            
            if (!isset($params['email']) || empty($params['email'])) {
                throw new LocalizedException(__('No Email'));
            }
            if (!isset($params['accountType']) || empty($params['accountType'])) {
                throw new LocalizedException(__('No accountType'));
            }
            if (!isset($params['accountId']) || empty($params['accountId'])) {
                throw new LocalizedException(__('No accountId'));
            }
            if(!$accountTypeId = $this->customerHelper->getAccountTypeId(trim($params['accountType']))) {
                throw new LocalizedException(__('INVALID ACCOUNT TYPE'));
            }
            $response['result'] = $this->customerHelper
                ->validateApiAccountId(trim($params['email']),$accountTypeId,trim($params['accountId']));
        
        } catch (\Exception $e) {
            $response = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
        
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
    
    
}