<?php

namespace Mirasvit\Sorting\Controller\Adminhtml\RankingFactor;

use Mirasvit\Sorting\Controller\Adminhtml\RankingFactorAbstract;

class NewAction extends RankingFactorAbstract
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
