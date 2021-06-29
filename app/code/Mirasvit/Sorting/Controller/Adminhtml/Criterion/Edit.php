<?php

namespace Mirasvit\Sorting\Controller\Adminhtml\Criterion;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Controller\Adminhtml\CriterionAbstract;

class Edit extends CriterionAbstract
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->initModel();
        $id    = $this->getRequest()->getParam(CriterionInterface::ID);

        if ($id && !$model) {
            $this->messageManager->addErrorMessage(__('This criteria no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage)
            ->getConfig()->getTitle()->prepend(
                $model->getId() ? __('Criteria "%1"', $model->getName()) : __('New Criteria')
            );

        return $resultPage;
    }
}
