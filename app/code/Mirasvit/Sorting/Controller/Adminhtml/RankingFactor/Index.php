<?php

namespace Mirasvit\Sorting\Controller\Adminhtml\RankingFactor;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Sorting\Controller\Adminhtml\RankingFactorAbstract;

class Index extends RankingFactorAbstract
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
