<?php

namespace Mirasvit\Sorting\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Registry;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Api\Repository\CriterionRepositoryInterface;

abstract class CriterionAbstract extends Action
{
    private   $context;

    protected $criterionRepository;

    private   $session;

    protected $resultForwardFactory;

    public function __construct(
        CriterionRepositoryInterface $criterionRepository,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        Context $context
    ) {
        $this->criterionRepository  = $criterionRepository;
        $this->resultForwardFactory = $resultForwardFactory;

        $this->context = $context;
        $this->session = $context->getSession();

        parent::__construct($context);
    }

    /**
     * Initialize page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Mirasvit_Sorting::sorting');

        $resultPage->getConfig()->getTitle()->prepend(__('Improved Sorting'));
        $resultPage->getConfig()->getTitle()->prepend(__('Sorting Criteria'));

        return $resultPage;
    }

    /**
     * @return CriterionInterface|false
     */
    protected function initModel()
    {
        $model = $this->criterionRepository->create();

        if ($this->getRequest()->getParam(CriterionInterface::ID)) {
            $model = $this->criterionRepository->get($this->getRequest()->getParam(CriterionInterface::ID));
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Sorting::sorting');
    }
}
