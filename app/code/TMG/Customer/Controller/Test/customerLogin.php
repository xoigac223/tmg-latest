<?php

namespace TMG\Customer\Controller\Test;

use TMG\Customer\Controller\CustomerTest;
use TMG\Customer\Exception\CustomerSecurityServiceException;

class customerLogin extends CustomerTest
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
            
            $result = $this->customerHelper->loginApiUser($user,$pass);
            print_r($result);
            
            echo '</pre>';
        
        } catch (\Exception $e) {

            print_r("\nERROR:\n");
            echo "<h3>{$e->getMessage()}</h3>";

            echo '<pre>';
            print_r("\nDEBUG:\n");
            print_r("\nREQUEST:\n");
            print_r($this->inventoryService->getRequestString(true,false));
            print_r("\nRESPONSE:\n");
            print_r($this->inventoryService->getResponseString(true,false));
            echo '</pre>';

        }
        
        die("\n" . __METHOD__ . "\n");
        
    }
}