<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Firebear\ImportExport\Controller\Adminhtml\Job;

use Magento\Framework\Controller\ResultFactory;
use Magento\ImportExport\Block\Adminhtml\Import\Frame\Result;

class Start extends \Magento\ImportExport\Controller\Adminhtml\Import\Start
{
    /**
     * @var \Magento\ImportExport\Model\Import
     */
    protected $importModel;

    /**
     * Start import process action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            /** @var \Magento\Framework\View\Result\Layout $resultLayout */
            $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
            /** @var $resultBlock Result */
            $resultBlock = $resultLayout->getLayout()
                ->createBlock(Result::class)
                ->setTemplate('import/frame/result.phtml');
            $resultBlock
                ->addAction('show', 'import_validation_container')
                ->addAction('innerHTML', 'import_validation_container_header', __('Status'))
                ->addAction('hide', ['edit_form', 'upload_button', 'messages']);

            $this->importModel->setData($data);
            $this->importModel->importSource();
            $errorAggregator = $this->importModel->getErrorAggregator();
            if ($this->importModel->getErrorAggregator()->hasToBeTerminated()) {
                $resultBlock->addError(__('Maximum error count has been reached or system error is occurred!'));
                $this->addErrorMessages($resultBlock, $errorAggregator);
            } else {
                $this->importModel->invalidateIndex();
                $this->addErrorMessages($resultBlock, $errorAggregator);
                $resultBlock->addSuccess(__('Import successfully done'));
            }
            return $this->getResponse()->appendBody($resultBlock->toHtml());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('adminhtml/*/index');
        return $resultRedirect;
    }
}
