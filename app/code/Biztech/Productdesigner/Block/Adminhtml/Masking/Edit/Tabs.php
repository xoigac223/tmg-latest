<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml\Masking\Edit;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Backend\Block\Widget\Tabs as WigetTabs;

class Tabs extends WigetTabs
{
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,       
        array $data = []
    ) {        
        parent::__construct($context, $jsonEncoder,$authSession,$data); 
         //   get_class_methods($context); 
          //  exit;       
    }
    protected function _construct()
    {
        parent::_construct();
        $this->setId('biztech_productdesigner_masking_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Masking Images'));
    }
}
