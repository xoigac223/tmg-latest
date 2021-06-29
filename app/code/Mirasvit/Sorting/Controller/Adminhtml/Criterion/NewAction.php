<?php

namespace Mirasvit\Sorting\Controller\Adminhtml\Criterion;

use Mirasvit\Sorting\Controller\Adminhtml\CriterionAbstract;

class NewAction extends CriterionAbstract
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        return $this->resultRedirectFactory->create()
            ->setPath('*/*/edit');
    }
}
