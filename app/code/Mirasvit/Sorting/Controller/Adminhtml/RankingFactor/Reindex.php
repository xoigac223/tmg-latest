<?php

namespace Mirasvit\Sorting\Controller\Adminhtml\RankingFactor;

use Magento\Framework\App\ObjectManager;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Controller\Adminhtml\RankingFactorAbstract;
use Mirasvit\Sorting\Model\Indexer;

class Reindex extends RankingFactorAbstract
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $model = $this->initModel();

        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('This factor no longer exists.'));

            return $resultRedirect->setPath('*/*/');
        }

        try {
            $objectManager = ObjectManager::getInstance();

            /** @var \Mirasvit\Sorting\Model\Indexer $indexer */
            $indexer = $objectManager->create(Indexer::class);
            $indexer->executeFull([$model->getId()]);

            $this->messageManager->addSuccessMessage(__('Reindex was completed.'));

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', [RankingFactorInterface::ID => $model->getId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath('*/*/edit', [RankingFactorInterface::ID => $model->getId()]);
        }
    }
}
