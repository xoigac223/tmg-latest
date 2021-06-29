<?php

namespace TMG\Customer\Controller\Test;

use TMG\Customer\Controller\CustomerTest;
use TMG\Customer\Exception\CustomerSecurityServiceException;

class lookupAccount extends CustomerTest
{
    public function execute()
    {
        
        
        // Param
        if(!$email = $this->getRequest()->getParam('email',false)) {
            echo 'NO EMAIL'; die();
        }
        if(!$accountId = $this->getRequest()->getParam('id',false)) {
            echo 'NO ACCOUNT ID'; die();
        }
        if(!$accountType = $this->getRequest()->getParam('type',false)) {
            echo 'NO ACCOUNT TYPE'; die();
        }
        if(!$accountTypeId = $this->customerHelper->getAccountTypeId($accountType)) {
            echo 'INVALID ACCOUNT TYPE'; die();
        }
        
        try {
            
            echo '<pre>';
            
            $result = $this->customerHelper->validateApiAccountId($email,$accountTypeId,$accountId);
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