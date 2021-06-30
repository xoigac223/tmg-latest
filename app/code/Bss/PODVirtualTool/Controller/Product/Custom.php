<?php
declare(strict_types=1);

namespace Bss\PODVirtualTool\Controller\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Custom.
 */
class Custom extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param RequestInterface $request
     */
    public function __construct(Context $context, PageFactory $pageFactory, RequestInterface $request)
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->request = $request;
    }

    /**
     * POD Virtual Page.
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->request->getParams();
        $resultPage = $this->pageFactory->create();
        $block = $resultPage->getLayout()
            ->createBlock(Template::class)
            ->setData($data)
            ->setTemplate('Bss_PODVirtualTool::virtual-tool.phtml')
            ->toHtml();
        $this->getResponse()->setBody($block);
    }
}