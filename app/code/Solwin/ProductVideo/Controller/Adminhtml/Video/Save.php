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

use Magento\Framework\Controller\ResultFactory;

class Save extends \Solwin\ProductVideo\Controller\Adminhtml\Video
{
    /**
     * Upload model
     *
     * @var \Solwin\ProductVideo\Model\Upload
     */
    protected $_uploadModel;

    /**
     * File model
     *
     * @var \Solwin\ProductVideo\Model\Video\File
     */
    protected $_fileModel;

    /**
     * Image model
     *
     * @var \Solwin\ProductVideo\Model\Video\Image
     */
    protected $_imageModel;

    protected $_productRepository;

    /**
     * constructor
     *
     * @param \Solwin\ProductVideo\Model\Upload $uploadModel
     * @param \Solwin\ProductVideo\Model\Video\File $fileModel
     * @param \Solwin\ProductVideo\Model\Video\Image $imageModel
     * @param \Solwin\ProductVideo\Model\VideoFactory $videoFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Solwin\ProductVideo\Model\Upload $uploadModel,
        \Solwin\ProductVideo\Model\Video\File $fileModel,
        \Solwin\ProductVideo\Model\Video\Image $imageModel,
        \Solwin\ProductVideo\Model\VideoFactory $videoFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_uploadModel    = $uploadModel;
        $this->_fileModel      = $fileModel;
        $this->_imageModel     = $imageModel;
        $this->_productRepository = $productRepository;
        parent::__construct(
                $videoFactory,
                $registry,
                $context
                );
    }

    public function getProductById($id)
    {
        return $this->_productRepository->getById($id);
    }
    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $video_id = 0 ;
        $data = $this->getRequest()->getPostValue();
        $products = null;
        if (isset($data['products'])) {
          $products = $data['products'];
        }

        $data = $this->getRequest()->getPost('video');
        if ($products != null) {
          $data['products'] = $products;
        }
        $db_products = "";
        if (isset($data['video_id'])) {
            $video_id = $data['video_id'];
            $collection = $this->_videoFactory->create()->getCollection()
                            ->addFieldToFilter('video_id', $video_id);
            $array=$collection->getData();
            $db_products = $array[0]["products"];
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $video = $this->initVideo();
            $video->setData($data);

            if(isset($_FILES['thumbnail'])){
                if($_FILES['thumbnail']['error'] == 0) {
                    $thumbnailFile = $_FILES['thumbnail']['name'];
                    $allowedThumbnailExt =  ['jpg', 'jpeg', 'gif', 'png'];
                    $fileExt = strtolower(pathinfo($thumbnailFile, PATHINFO_EXTENSION));
                    if (in_array($fileExt, $allowedThumbnailExt)) {
                        $thumbnail = $this->_uploadModel
                                ->uploadFileAndGetName(
                                        'thumbnail',
                                        $this->_imageModel->getBaseDir(),
                                        $data);
                        $video->setThumbnail($thumbnail);
                    } else {
                        $this->messageManager
                            ->addError(__('Please upload a valid image. (jpg, jpeg, gif, png)'));
                        $resultRedirect->setPath($this->_redirect->getRefererUrl());
                        return $resultRedirect;
                    }
                } else if (isset($data['thumbnail']['delete'])) {
                    $video->setThumbnail('');
                } else if (isset($data['thumbnail']['value'])) {
                    $video->setThumbnail($data['thumbnail']['value']);
                }
            }
            if($data['video_type'] == 1) {
                if($_FILES['video_file']['error'] == 0) {
                    $videoFileData = $_FILES['video_file']['name'];
                    $allowedVideoExt =  ['mp4'];
                    $fileExt = strtolower(pathinfo($videoFileData, PATHINFO_EXTENSION));
                    if (in_array($fileExt, $allowedVideoExt)) {
                        $videoFile = $this->_uploadModel
                            ->uploadFileAndGetName(
                                    'video_file',
                                    $this->_fileModel->getBaseDir(),
                                    $data);
                        $video->setVideoFile($videoFile);
                    } else {
                        $this->messageManager
                            ->addError(__('Please upload an mp4 video file.'));
                        $resultRedirect->setPath($this->_redirect->getRefererUrl());
                        return $resultRedirect;
                    }
                } else if (isset($data['video_file']['delete'])) {
                    $video->setVideoFile('');
                } else if (isset($data['video_file']['value'])) {
                    $video->setVideoFile($data['video_file']['value']);
                }
            } else {
              if(isset($data['video_file']['value'])) {
                $video->setVideoFile($data['video_file']['value']);
              }
            }

            $this->_eventManager->dispatch(
                'solwin_productvideo_video_prepare_save',
                [
                    'video' => $video,
                    'request' => $this->getRequest()
                ]
            );

            try {
                $video->save();
                $product_data = explode('&', $products);
                $db_products = explode('&', $db_products);
                $delete_result=array_values(array_diff($db_products,$product_data));
                $add_result=array_values(array_diff($product_data,$db_products));

                $delete_result = array_filter($delete_result);
                $delete_result = array_values($delete_result);
                $add_result = array_filter($add_result);
                $add_result = array_values($add_result);
                if($video_id > 0) {
                  for($i = 0 ;$i<count($delete_result); $i++) {
                    $prod_id = $delete_result[$i];
                    $_product = $this->getProductById($prod_id);
                    $old_productvideo = $_product->getProductvideo();
                    $old_array = explode(",",$old_productvideo);
                    $old_array = array_values(array_filter($old_array));
                    if(in_array($video_id,$old_array)) {
                      $old_array = array_diff($old_array, array($video_id));
                    }
                    if(count($old_array) > 0) {
                      $new_array = implode("," , $old_array);
                    } else {
                      $new_array = "";
                    }
                    $_product->setProductvideo($new_array);
                    $_product->save();
                  }
                }
                $new_video_id = $video->getId();
                for($i = 0 ;$i<count($add_result); $i++) {
                  $prod_id = $add_result[$i];
                  $_product = $this->getProductById($prod_id);
                  $old_productvideo = $_product->getProductvideo();
                  $old_array = explode(",",$old_productvideo);
                  $old_array = array_values(array_filter($old_array));
                  if(!in_array($new_video_id,$old_array)) {
                    $s = count($old_array) + 1;
                    $old_array[$s] = $new_video_id;
                  }
                  $new_array = implode("," , $old_array);
                  $_product->setProductvideo($new_array);
                  $_product->save();
                }
                $this->messageManager
                        ->addSuccess(__('The Video has been saved.'));
                $this->_session->setSolwinProductVideoVideoData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'solwin_productvideo/*/edit',
                        [
                            'video_id' => $video->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('solwin_productvideo/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e,
                        __('Something went wrong while saving the Video.').$e->getMessage());
            }
            $this->_getSession()->setSolwinProductVideoVideoData($data);
            $resultRedirect->setPath(
                'solwin_productvideo/*/edit',
                [
                    'video_id' => $video->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('solwin_productvideo/*/');
        return $resultRedirect;
    }
}