<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Controller\Adminhtml\Export\Job;

use Magento\Backend\Model\View\Result\ForwardFactory;
use Firebear\ImportExport\Api\Data\ExportInterface;

class Disable extends \Firebear\ImportExport\Controller\Adminhtml\Export\Job
{
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Firebear\ImportExport\Model\ExportJobFactory $exportJobFactory,
        \Firebear\ImportExport\Api\ExportJobRepositoryInterface $exportRepository,
        ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context, $coreRegistry, $exportJobFactory, $exportRepository);
    }
    
    /**
     * Delete a job
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // check if we know what should be deleted
        $jobId = $this->getRequest()->getParam('entity_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($jobId) {
            try {
                $job = $this->exportRepository->getById($jobId);
                $job->setIsActive(ExportInterface::STATUS_DISABLED);
                $this->exportRepository->save($job);
                $this->messageManager->addSuccessMessage(__('The job changed status.'));
                
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/');
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a job to сhange status.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
