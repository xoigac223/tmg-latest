<?php
namespace Themagnet\Orderstatus\Block;

class Ordertstatus extends \Magento\Framework\View\Element\Template
{
    protected $querytype;
    protected $_objectManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManage,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->querytype = $querytype;
        $this->_objectManager = $objectManage;
    }


    public function getCutomerSession(array $data = array())
    {
        return $this->_objectManager->create('\Magento\Customer\Model\Session', $data);
    }

    public function getCustomer()
    {
        return $this->getCutomerSession()->getCustomer();
    }

}
