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

abstract class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * JSON Factory
     * 
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_jsonFactory;

    /**
     * Video Factory
     * 
     * @var \Solwin\ProductVideo\Model\VideoFactory
     */
    protected $_videoFactory;

    /**
     * constructor
     * 
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Solwin\ProductVideo\Model\VideoFactory $videoFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Solwin\ProductVideo\Model\VideoFactory $videoFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_jsonFactory  = $jsonFactory;
        $this->_videoFactory = $videoFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->_jsonFactory->create();
        $error = false;
        $messages = [];
        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }
        foreach (array_keys($postItems) as $videoId) {
            /** @var \Solwin\ProductVideo\Model\Video $video */
            $video = $this->_videoFactory->create()->load($videoId);
            try {
                $videoData = $postItems[$videoId];//todo: handle dates
                $video->addData($videoData);
                $video->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithVideoId(
                        $video, 
                        $e->getMessage()
                        );
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithVideoId(
                        $video, 
                        $e->getMessage()
                        );
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithVideoId(
                    $video,
                    __('Something went wrong while saving the Video.')
                );
                $error = true;
            }
        }
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add Video id to error message
     *
     * @param \Solwin\ProductVideo\Model\Video $video
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithVideoId(
        \Solwin\ProductVideo\Model\Video $video,
        $errorText
    ) {
        return '[Video ID: ' . $video->getId() . '] ' . $errorText;
    }
}