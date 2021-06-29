<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Biztech\Productdesigner\Ui\Component\Listing\Columns;


use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class ProductActions
 */
class MakeDesign extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            

            foreach ($dataSource['data']['items'] as &$item) {
                $id = $item['entity_id'];
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $isPdEnable = '';
                $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
                $product = $obj_product->load($id);
                //echo $product->getTypeId();
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $ids = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($id);
               
                $isPdEnable = $product->getEnableProductDesigner();
                if($isPdEnable && empty($ids))
                {
                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'productdesigner',
                        ['id' => $item['entity_id']]
                    ),
                    'label' => __('MakeDesign'),
                    'hidden' => false,
                ];
                }
            }
        }

        return $dataSource;
    }
}
