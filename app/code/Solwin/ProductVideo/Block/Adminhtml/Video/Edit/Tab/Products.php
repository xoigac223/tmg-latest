<?php

namespace Solwin\ProductVideo\Block\Adminhtml\Video\Edit\Tab;

use Solwin\ProductVideo\Model\VideoFactory;

/**
 * Class Products
 * @package Solwin\ProductVideo\Block\Adminhtml\Video\Edit\Tab
 */
class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Solwin\ProductVideo\Model\Video
     */
    private $videoModel;

    /**
     * @var VideoFactory
     */
    private $videoFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    protected $productStatus;
    protected $productVisibility;

    /**
     * Products constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $registry
     * @param VideoFactory $videoFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Solwin\ProductVideo\Model\Video $videoModel
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        VideoFactory $videoFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Solwin\ProductVideo\Model\Video $videoModel,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        array $data = []
    ) {
        $this->videoFactory = $videoFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->registry = $registry;
        $this->videoModel = $videoModel;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * _construct
     * @return void
     */
    public function _construct()
    {;
        parent::_construct();
        $this->setId('productsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('video_id')) {
            $this->setDefaultFilter(['in_product' => 1]);
        }
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    public function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_product') {
            $productIds = $this->_getSelectedProducts();

            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * prepare collection
     */
    public function _prepareCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
        $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('price');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    public function _prepareColumns()
    {

        $model = $this->videoModel;

        $this->addColumn(
            'in_product',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_product',
                'align' => 'center',
                'index' => 'entity_id',
                'values' => $this->_getSelectedProducts(),
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('Product ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'names',
            [
                'header' => __('Name'),
                'index' => 'name',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('Sku'),
                'index' => 'sku',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'index' => 'price',
                'width' => '50px',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/productsgrid', ['_current' => true]);
    }

    /**
     * @param  object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }

    public function _getSelectedProducts()
   {
       $video = $this->getVideo();
       return $video->getProducts($video);
   }

    /**
     * Retrieve selected products
     *
     * @return array
     */
    public function getSelectedProducts()
    {
        $video = $this->getVideo();
        $selected = $video->getProducts($video);

        if (!is_array($selected)) {
            $selected = [];
        }
        return $selected;
    }

    public function getVideo()
    {
        $videoId = $this->getRequest()->getParam('video_id');
        $video   = $this->videoFactory->create();
        if ($videoId) {
            $video->load($videoId);
        }
        return $video;
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return true;
    }
}
