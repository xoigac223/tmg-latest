<?php
/**
 *
 * Copyright © 2016 Ubertheme.com All rights reserved.
 *
 */
namespace Ubertheme\UbMegaMenu\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Ubertheme\UbMegaMenu\Model\Item\Image as ImageModel;
use Ubertheme\UbMegaMenu\Model\Upload;
use Ubertheme\UbMegaMenu\Helper\Data as HelperData;

class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ubertheme_UbMegaMenu::item_save';

    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var ImageModel
     */
    protected $imageModel;

    /**
     * @var Upload
     */
    protected $uploadModel;

    /**
     * @var HelperData
     */
    protected $_helperData;


    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     * @param ImageModel $imageModel
     * @param Upload $uploadModel
     */
    public function __construct(
        Action\Context $context,
        PostDataProcessor $dataProcessor,
        ImageModel $imageModel,
        Upload $uploadModel,
        HelperData $_helperData
    )
    {
        $this->dataProcessor = $dataProcessor;
        $this->imageModel = $imageModel;
        $this->uploadModel = $uploadModel;
        $this->_helperData = $_helperData;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        //get again menu group_id from session because we have disable this filed in form
        $menuGroupId = $this->_objectManager->get('Magento\Backend\Model\Session')->getMenuGroupId();
        $data['group_id'] = $menuGroupId;

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        if ($data) {
            //filter data
            $data = $this->dataProcessor->filter($data);

            //initial model
            $model = $this->_objectManager->create('Ubertheme\UbMegaMenu\Model\Item');
            
            $id = $this->getRequest()->getParam('item_id');
            if ($id) {
                $model->load($id);
            }

            //get menu group
            /* @var \Ubertheme\UbMegaMenu\Model\Group $menuGroup*/
            $menuGroup = $this->_objectManager->create('Ubertheme\UbMegaMenu\Model\Group');
            $menuGroup->load($menuGroupId);

            //get store id of menu group
            $storeIds = $menuGroup->getStores();
            $storeId = isset($storeIds[0]) ? $storeIds[0] : null;

            //update link by link type
            if ($data['link_type'] == \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CATEGORY) {
                $data['link'] = 'dynamically';
            } elseif ($data['link_type'] == \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CMS) {
                /* @var \Magento\Cms\Model\Page $page */
                $page = $this->_objectManager->create('Magento\Cms\Model\Page');
                $page->setStoreId($storeId)->load($data['cms_page']);
                $data['link'] = $this->getUrl(null, ['_direct' => $page->getIdentifier()]);
            }
            //get base url without storecode
            $baseUrl = $this->_helperData->getBaseUrl();
            //remove base url from link or replace by short code
            if ($data['link_type'] == \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CUSTOM) {
                $data['link'] = str_replace($baseUrl, '{base_url}', $data['link']);
            } else {
                $data['link'] = str_replace($baseUrl, '', $data['link']);
            }
            //static_blocks process
            if (isset($data['static_blocks']) AND is_array($data['static_blocks'])) {
                $data['static_blocks'] = implode(',', $data['static_blocks']);
            }

            //visible_in process
            if (isset($data['visible_in']) AND is_array($data['visible_in'])) {
                $data['visible_in'] = implode(',', $data['visible_in']);
            }

            //identifier process
            $data['identifier'] = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($data['title'])), '-');

            //set new data
            $model->setData($data);

            //upload icon image
            $imageName = $this->uploadModel->processUpload('icon_image', $this->imageModel->getBaseDir(), $data, ['jpg', 'jpeg', 'gif', 'png']);
            $model->setIconImage($imageName);

            //validate data
            if (!$this->dataProcessor->validate($data)) {
                return $resultRedirect->setPath('*/*/edit', ['item_id' => $model->getId(), '_current' => true]);
            }

            //save data process
            try {
                $model->save();
                //set cookie of item id
                setcookie("activeMenuItemIds", $model->getId(), time() + (86400 * 30), "/");

                $this->messageManager->addSuccessMessage(__('You saved this menu item.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['item_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/', ['group_id' => $model->getGroupId()]);

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the menu item information').':<br/> '.$e->getMessage() );
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['item_id' => $this->getRequest()->getParam('item_id')]);
        }

        return $resultRedirect->setPath('*/*/', ['group_id' => $data['group_id']]);
    }
}
