<?php
declare(strict_types=1);

namespace TMG\PDOVirtualTool\Controller\Product;

use Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;

class Edit extends Action implements ActionInterface
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * Edit constructor.
     * @param PageFactory $pageFactory
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, PageFactory $pageFactory)
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    /**
     * @return Page
     */
    public function execute()
    {
        return $this->pageFactory->create();
    }
}