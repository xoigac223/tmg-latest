<?php

namespace Mirasvit\Sorting\Controller\Adminhtml\RankingFactor;

use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Controller\Adminhtml\RankingFactorAbstract;

class Delete extends RankingFactorAbstract
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(RankingFactorInterface::ID);

        if ($id) {
            try {
                $model = $this->rankingFactorRepository->get($id);
                $this->rankingFactorRepository->delete($model);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            $this->messageManager->addSuccessMessage(
                __('Ranking Factor was removed')
            );
        } else {
            $this->messageManager->addErrorMessage(__('Please select factor'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
