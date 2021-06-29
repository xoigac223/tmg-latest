<?php

namespace Shreeji\Duplicateimage\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class ImageActions
 */
class ImageActions extends Column {

    /** Url path */
    const DUPLICATE_IMAGE_URL_PATH_DELETE = 'duplicateimage/manage/delete';

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * 
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
    ContextInterface $context, UiComponentFactory $uiComponentFactory, UrlInterface $urlBuilder, array $components = [], array $data = []
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
    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['manageimage_id'])) {
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::DUPLICATE_IMAGE_URL_PATH_DELETE, ['manageimage_id' => $item['manageimage_id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete'),
                            'message' => __('Are you sure you wan\'t to delete this image?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }

}
