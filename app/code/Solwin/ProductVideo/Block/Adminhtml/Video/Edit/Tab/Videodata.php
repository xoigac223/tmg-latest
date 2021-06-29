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
namespace Solwin\ProductVideo\Block\Adminhtml\Video\Edit\Tab;

use Solwin\ProductVideo\Model\Video\Source\VideoType;

class Videodata
extends \Magento\Backend\Block\Widget\Form\Generic
implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Wysiwyg config
     *
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * Choose Video Type options
     *
     * @var VideoType
     */
    protected $_videoTypeOptions;

    /**
     * @var \Solwin\ProductVideo\Helper\Data
     */
    protected $_helper;

    /**
     * constructor
     *
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param VideoType $videoTypeOptions
     * @param \Solwin\ProductVideo\Model\Video\Source\Status $statusOptions
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        VideoType $videoTypeOptions,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Solwin\ProductVideo\Helper\Data $helper,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->_wysiwygConfig    = $wysiwygConfig;
        $this->_videoTypeOptions = $videoTypeOptions;
        $this->_helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
      $mediaDirectory = $this->_storeManager->getStore()->getBaseUrl(
           \Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        /** @var \Solwin\ProductVideo\Model\Video $video */
        $video = $this->_coreRegistry->registry('solwin_productvideo_video');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('video_');
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        $form->setFieldNameSuffix('video');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Video Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        $fieldset->addType('image',
                'Solwin\ProductVideo\Block\Adminhtml\Video\Helper\Image');
        $fieldset->addType('file',
                'Solwin\ProductVideo\Block\Adminhtml\Video\Helper\File');

        $fieldset->addField(
            'video_type',
            'select',
            [
                'name'  => 'video_type',
                'label' => __('Choose Video Type'),
                'title' => __('Choose Video Type'),
                'required' => true,
                'values' => array_merge(['' => ''],$this->_videoTypeOptions->toOptionArray()),
                'after_element_html' =>
                  "<script>require([
                          'jquery'
                      ], function ($) {
                          $('#video_video_type').change(function() {
                            var selected_val = $(this).val();
                            if(selected_val == 1) {
                              $('.admin__field.field.field-video_file').show();
                              $('.admin__field.field.field-vimeo_video_url').hide();
                              $('.admin__field.field.field-youtube_video_url').hide();
                            } else if(selected_val == 2) {
                              $('.admin__field.field.field-video_file').hide();
                              $('.admin__field.field.field-vimeo_video_url').hide();
                              $('.admin__field.field.field-youtube_video_url').show();
                            } else if(selected_val == 3) {
                              $('.admin__field.field.field-video_file').hide();
                              $('.admin__field.field.field-vimeo_video_url').show();
                              $('.admin__field.field.field-youtube_video_url').hide();
                            }
                          });
                      });
                  </script>",
            ]
        );
        if($video->getYoutubeVideoUrl() && ($video->getVideoType() == '2')) {
          $youtube_video_url =  $video->getYoutubeVideoUrl();
          $youtubeId = $this->_helper->getYoutubeId($youtube_video_url);
          $fieldset->addField(
              'youtube_video_url',
              'text',
              [
                  'name'  => 'youtube_video_url',
                  'label' => __('Youtube Video URL'),
                  'title' => __('Youtube Video URL'),
                  'required' => true,
                  'note' => __('Enter Youtube URL<br><b>Example: </b>https://www.youtube.com/watch?v=C0DPdy98e4c'),
              ]
          )->setAfterElementHtml('<br><br><iframe
              src="//www.youtube.com/embed/'.$youtubeId.'" frameborder="0"></iframe>');
        } else {
          $fieldset->addField(
              'youtube_video_url',
              'text',
              [
                  'name'  => 'youtube_video_url',
                  'label' => __('Youtube Video URL'),
                  'title' => __('Youtube Video URL'),
                  'required' => true,
                  'note' => __('Enter Youtube URL<br><b>Example: </b>https://www.youtube.com/watch?v=C0DPdy98e4c'),
              ]
          );
        }

        if($video->getVimeoVideoUrl() && ($video->getVideoType() == '3')) {
          $vimeo_video_url =  $video->getVimeoVideoUrl();
          $vimeoId = $this->_helper->getVimeoId($vimeo_video_url);
          $fieldset->addField(
              'vimeo_video_url',
              'text',
              [
                  'name'  => 'vimeo_video_url',
                  'label' => __('Vimeo Video URL'),
                  'title' => __('Vimeo Video URL'),
                  'required' => true,
                  'note' => __('Enter Vimeo URL<br><b>Example: </b>https://vimeo.com/8733915'),
              ]
          )->setAfterElementHtml(
        '<br><br><iframe src="http://player.vimeo.com/video/'.$vimeoId.'?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;color=ffffff" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen>
</iframe>');
        } else {
          $fieldset->addField(
              'vimeo_video_url',
              'text',
              [
                  'name'  => 'vimeo_video_url',
                  'label' => __('Vimeo Video URL'),
                  'title' => __('Vimeo Video URL'),
                  'required' => true,
                  'note' => __('Enter Vimeo URL<br><b>Example: </b>https://vimeo.com/8733915'),
              ]
          );
        }

        $fieldset->addField(
            'video_file',
            'file',
            [
                'name'  => 'video_file',
                'label' => __('Upload Video'),
                'title' => __('Upload Video'),
            ]
        )->setAfterElementHtml('<p class="file-note">Allow file format: .mp4</p>
          <script type="text/javascript">
          require(["jquery", "mage/mage"], function(jQuery){
              (function ($) {
                  $(":input[name=video_file]").change(function () {
                      var obj = $(this);
                      var fileExtension = ["mp4"];
                      if ($.inArray(obj.val().split(".").pop().toLowerCase(), fileExtension) == -1) {
                          $(".file-note").css("color","#eb5202");
                          obj.val("");
                      } else {
                        $(".file-note").css("color","");
                      }
                  });
              })(jQuery);
          });
          </script>');

        if($video->getThumbnail()) {
          $video_thumbnail =  $video->getThumbnail();
          $imageUrl = $mediaDirectory.'solwin/productvideo/video/image'.$video_thumbnail;
          $fieldset->addField(
              'thumbnail',
              'image',
              [
                  'name'  => 'thumbnail',
                  'label' => __('Video Thumbnail'),
                  'required' => true,
                  'title' => __('Video Thumbnail'),
              ]
          )->setAfterElementHtml('<style>.admin__field.field.field-thumbnail .admin__field  a:first-child {display: none;}</style><br><a href="'.$imageUrl.'" onclick="imagePreview(video_thumbnail_image); return false;">
              <img src="'.$imageUrl.'" id="video_thumbnail_image" title="'.$video_thumbnail.'" alt="'.$video_thumbnail.'" style="width:350px;height:auto;padding-top:10px;"></a><br>
              <p class="image-note">Allow file format: .jpeg, .jpg, .gif, .png</p>
              <script type="text/javascript">
              require(["jquery", "mage/mage"], function(jQuery){
                  (function ($) {
                      $(":input[name=thumbnail]").change(function () {
                          var obj = $(this);
                          var fileExtension = ["jpeg","jpg","gif","png"];
                          if ($.inArray(obj.val().split(".").pop().toLowerCase(), fileExtension) == -1) {
                              $(".image-note").css("color","#eb5202");
                              obj.val("");
                          } else {
                            $(".image-note").css("color","");
                          }
                      });
                  })(jQuery);
              });
              </script>');
        } else {
          $fieldset->addField(
              'thumbnail',
              'image',
              [
                  'name'  => 'thumbnail',
                  'label' => __('Video Thumbnail'),
                  'title' => __('Video Thumbnail'),
                  'required' => true,
              ]
            )->setAfterElementHtml('
                <p class="image-note">Allow file format: .jpeg, .jpg, .gif, .png</p>
                <script type="text/javascript">
                require(["jquery", "mage/mage"], function(jQuery){
                    (function ($) {
                      $("#video_thumbnail").addClass("required-entry");
                        $(":input[name=thumbnail]").change(function () {
                            var obj = $(this);
                            var fileExtension = ["jpeg","jpg","gif","png"];
                            if ($.inArray(obj.val().split(".").pop().toLowerCase(), fileExtension) == -1) {
                                $(".image-note").css("color","#eb5202");
                                obj.val("");
                            } else {
                              $(".image-note").css("color","");
                            }
                        });
                    })(jQuery);
                });
                </script>');
        }

        $fieldset->addField(
            'content',
            'editor',
            [
                'name'  => 'content',
                'label' => __('Content'),
                'title' => __('Content'),
                'config'    => $this->_wysiwygConfig->getConfig(),
                'note' => __('Content display in product Information tab.'),
            ]
        );

        if (($video->getVideoType()) == '1') {
          echo "<script>require([
                  'jquery'
              ], function ($) {
                    $('.admin__field.field.field-video_file').show();
                    $('.admin__field.field.field-vimeo_video_url').hide();
                    $('.admin__field.field.field-youtube_video_url').hide();
              });
          </script>";
        } else if(($video->getVideoType()) == '2') {
            echo "<script>require([
                    'jquery'
                ], function ($) {
                    $('.admin__field.field.field-video_file').hide();
                    $('.admin__field.field.field-vimeo_video_url').hide();
                    $('.admin__field.field.field-youtube_video_url').show();
                });
            </script>";
        } else {
          echo "<script>require([
                  'jquery'
              ], function ($) {
                    $('.admin__field.field.field-video_file').hide();
                    $('.admin__field.field.field-vimeo_video_url').show();
                    $('.admin__field.field.field-youtube_video_url').hide();
              });
          </script>";
        }

        $videoData = $this->_session
                ->getData('solwin_productvideo_video_data', true);
        if ($videoData) {
            $video->addData($videoData);
        } else {
            if (!$video->getId()) {
                $video->addData($video->getDefaultValues());
            }
        }
        $form->addValues($video->getData());
        $this->setChild(
                'form_after',
                $this->getLayout()->createBlock(
                                'Magento\Backend\Block\Widget\Form\Element\Dependence'
                        )->addFieldMap(
                                "{$htmlIdPrefix}video_type", 'video_type'
                        )
                        ->addFieldMap(
                                "{$htmlIdPrefix}youtube_video_url",
                                        'youtube_video_url'
                        )
                        ->addFieldMap(
                                "{$htmlIdPrefix}vimeo_video_url",
                                        'vimeo_video_url'
                        )
                        ->addFieldMap(
                                "{$htmlIdPrefix}video_file", 'video_file'
                        )
                        ->addFieldDependence(
                                'video_file', 'video_type', 1
                        )
                        ->addFieldDependence(
                                'youtube_video_url', 'video_type', 2
                        )
                        ->addFieldDependence(
                                'vimeo_video_url', 'video_type', 3
                        )
        );
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Video');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}