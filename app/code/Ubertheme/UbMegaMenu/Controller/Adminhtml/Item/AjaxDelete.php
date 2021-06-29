<?php
/**
 *
 * Copyright © 2016 Ubertheme.com All rights reserved.
 *
 */
namespace Ubertheme\UbMegaMenu\Controller\Adminhtml\Item;

class AjaxDelete extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ubertheme_UbMegaMenu::item_delete';

    protected $jsonEncoder;

    protected $jsonDecoder;

    protected $rawResult;

    protected $storeManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        \Magento\Framework\Json\Decoder $jsonDecoder,
        \Magento\Framework\Controller\Result\Raw $rawResult,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->rawResult = $rawResult;
        $this->storeManager = $storeManager;

        parent::__construct($context);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('item_id');
        $result = ['success' => false];

        if ($id) {
            try {
                // init model and delete
                /* @var \Ubertheme\UbMegaMenu\Model\Item $model */
                $model = $this->_objectManager->create('Ubertheme\UbMegaMenu\Model\Item');
                $model->load($id);

                //delete item
                $model->delete();

                // display success message
                $result['success'] = true;
                $result['message'] = __('The menu item has been deleted.');

            } catch (\Exception $e) {
                $result['message'] = $e->getMessage();
            }
        } else {
            // display error message
            $result['message'] = __('We can\'t find a menu item to delete.');
        }

        $this->rawResult->setHeader('Content-type', 'application/json');
        return $this->rawResult->setContents($this->jsonEncoder->encode($result));
    }
}
