<?php

namespace Mirasvit\Sorting\Controller\Adminhtml\RankingFactor;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Controller\Adminhtml\RankingFactorAbstract;

class Edit extends RankingFactorAbstract
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->initModel();
        $id    = $this->getRequest()->getParam(RankingFactorInterface::ID);

        if ($id && !$model) {
            $this->messageManager->addErrorMessage(__('This factor no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage)
            ->getConfig()->getTitle()->prepend(
                $model->getId() ? __('Ranking Factor "%1"', $model->getName()) : __('New Ranking Factor')
            );

        return $resultPage;
    }
}
