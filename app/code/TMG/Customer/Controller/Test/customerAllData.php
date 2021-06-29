<?php

namespace TMG\Customer\Controller\Test;

use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use TMG\Customer\Controller\CustomerTest;

class customerAllData extends CustomerTest
{
    public function execute()
    {
        
        // Param
        if(!$user = $this->getRequest()->getParam('user',false)) {
            echo 'NO USER'; die();
        }
        if(!$pass = $this->getRequest()->getParam('pass',false)) {
            echo 'NO PASS'; die();
        }
        
        try {
            
            echo '<pre>';
            
            $apiLoginResult = $this->customerHelper->loginApiUser($user,$pass);
    
            // API Login
            if(!isset($apiLoginResult['status']) || !$apiLoginResult['status']) {
                $message = isset($apiLoginResult['message'])
                    ? __($apiLoginResult['message']) : __('Invalid login or password.') ;
                throw new InvalidEmailOrPasswordException($message);
            }
            
            // All Data
            $result = $this->customerHelper->getApiCustomerFullData($user,$apiLoginResult['data']);
            
            print_r($result); echo '</pre>';
        
        } catch (\Exception $e) {

            print_r("\nERROR:\n");
            echo "<h3>{$e->getMessage()}</h3>";

            echo '<pre>';
            print_r("\nDEBUG:\n");
            echo '</pre>';

        }
        
        die("\n" . __METHOD__ . "\n");
        
    }
}