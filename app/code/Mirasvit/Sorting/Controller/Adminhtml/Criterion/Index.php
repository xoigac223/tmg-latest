<?php

namespace Mirasvit\Sorting\Controller\Adminhtml\Criterion;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Sorting\Controller\Adminhtml\CriterionAbstract;

class Index extends CriterionAbstract
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage);

        return $resultPage;
    }
}
