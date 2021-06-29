<?php
/**
 * Solwin Infotech
 * Solwin Advanced Product Video Extension
 *
 * @category   Solwin
 * @package    Solwin_ProductVideo
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/
 */
namespace Solwin\ProductVideo\Controller\Adminhtml\Video;

class Delete extends \Solwin\ProductVideo\Controller\Adminhtml\Video
{
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('video_id');

        //for delete attribute value
        $attributeInfo = $this->getAttributeInfo('catalog_product', 'productvideo');
        $attributeId = $attributeInfo->getAttributeId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('catalog_product_entity_varchar');
        $result = $connection->fetchAll("Select * FROM " . $tableName." where attribute_id='".$attributeId."'");
        for($i =0 ; $i<count($result) ; $i++) {
          $value = $result[$i]["value"];
          $entity_id = $result[$i]["entity_id"];
          $_product = $this->getProductById($entity_id);
          $old_array = explode(",",$value);
          $old_array = array_values(array_filter($old_array));
          if(in_array($id,$old_array)) {
            $old_array = array_diff($old_array, array($id));
          }
          if(count($old_array) > 0) {
            $new_array = implode("," , $old_array);
          } else {
            $new_array = "";
          }
          $_product->setProductvideo($new_array);
          $_product->save();
        }
        //for delete attribute value


        if ($id) {
            $title = "";
            try {
                /** @var \Solwin\ProductVideo\Model\Video $video */
                $video = $this->_videoFactory->create();
                $video->load($id);
                $title = $video->getTitle();
                $video->delete();
                $this->messageManager
                        ->addSuccess(__('The Video has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_solwin_productvideo_video_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                $resultRedirect->setPath('solwin_productvideo/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_solwin_productvideo_video_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath(
                        'solwin_productvideo/*/edit', ['video_id' => $id]
                        );
                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('Video to delete was not found.'));
        // go to grid
        $resultRedirect->setPath('solwin_productvideo/*/');
        return $resultRedirect;
    }
}