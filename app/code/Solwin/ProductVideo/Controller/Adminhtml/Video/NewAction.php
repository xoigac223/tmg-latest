<?php
/**
 * Solwin Infotech
 * Solwin Advanced Product Video Extension
 *
 * @category   Solwin
 * @package    Solwin_ProductVideo
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
namespace Solwin\ProductVideo\Controller\Adminhtml\Video;

use Magento\Backend\Model\View\Result\ForwardFactory;

class NewAction extends \Magento\Backend\App\Action
{
    /**
     * Redirect result factory
     * 
     * @var ForwardFactory
     */
    protected $_resultForwardFactory;

    /**
     * constructor
     * 
     * @param ForwardFactory $resultForwardFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        ForwardFactory $resultForwardFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $resultForward = $this->_resultForwardFactory->create();
        $resultForward->forward('edit');
        return $resultForward;
    }
}