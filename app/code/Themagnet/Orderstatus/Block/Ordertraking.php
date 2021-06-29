<?php
namespace Themagnet\Orderstatus\Block;

class Ordertraking extends \Magento\Framework\View\Element\Template
{
    protected $querytype;
    protected $_objectManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManage,
        \Themagnet\Orderstatus\Model\Source\Querytype $querytype,
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

    public function getFormUrl()
    {
        return $this->getUrl('themagnet_orderstatus/index/ordercheck');
    }

    public function getCustomer()
    {
        return $this->getCutomerSession()->getCustomer();
    }

    public function getOptionArray()
    {
        return $this->querytype->getOptionArray();
    }
}
