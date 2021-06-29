<?php

namespace TMG\Customer\Controller;

use Magento\Framework\App\Action\Context;

use TMG\Base\Controller\Test;
use TMG\Customer\Helper\Customer as CustomerHelper;
use TMG\Customer\Model\Service\CustomerSecurity as CustomerSecurityService;
use TMG\Customer\Model\Service\Contact as ContactService;

use TMG\Customer\Exception\ContactServiceException;
use TMG\Customer\Exception\CustomerSecurityServiceException;

abstract class CustomerTest extends Test
{
    protected $customerSecurityService;
    
    protected $contactService;
    
    protected $customerHelper;
    
    public function __construct(
        Context $context,
        CustomerHelper $customerHelper,
        CustomerSecurityService $customerSecurityService,
        ContactService $contactService
    )
    {
        parent::__construct($context);
        $this->customerHelper = $customerHelper;
        $this->customerSecurityService = $customerSecurityService;
        $this->contactService = $contactService;
    }

}