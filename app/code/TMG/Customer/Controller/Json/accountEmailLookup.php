<?php

namespace TMG\Customer\Controller\Json;

use Magento\Framework\Exception\LocalizedException;
use TMG\Customer\Controller\Json;

class accountEmailLookup extends Json
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
            
            $response['result'] = $this->customerHelper
                ->validateApiAccountEmail(trim($params['email']),true);

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