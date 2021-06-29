<?php

namespace TMG\Customer\Plugin\Customer\Controller\Account;

use Magento\Customer\Controller\Account\EditPost;

use TMG\Customer\Helper\Config as ConfigHelper;

class EditPostPlugin
{
    /**
     * @var ConfigHelper
     */
    protected $configHelper;
    
    /**
     * CustomerRepositoryPlugin constructor.
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ConfigHelper $configHelper
    )
    {
        $this->configHelper = $configHelper;
    }
    
    public function beforeExecute(EditPost $subject)
    {
        $this->configHelper->setIsCustomerEditPost();
        return null;
    }
    
}