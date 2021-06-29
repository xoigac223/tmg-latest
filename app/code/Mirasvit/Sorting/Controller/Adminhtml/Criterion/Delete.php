<?php

namespace Mirasvit\Sorting\Controller\Adminhtml\Criterion;

use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Controller\Adminhtml\CriterionAbstract;

class Delete extends CriterionAbstract
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(CriterionInterface::ID);

        if ($id) {
            try {
                $model = $this->criterionRepository->get($id);
                $this->criterionRepository->delete($model);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            $this->messageManager->addSuccessMessage(
                __('Criteria was removed')
            );
        } else {
            $this->messageManager->addErrorMessage(__('Please select criteria'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
