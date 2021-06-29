<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ExportSource extends Column
{

    protected $options;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Firebear\ImportExport\Ui\Component\Listing\Column\ExportSource\Options $options,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->options     = $options;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * @param $item
     *
     * @return null
     */
    protected function prepareItem($item)
    {
        $result = null;
        $list   = $this->options->toArray();
        $source = $this->jsonDecoder->decode($item['export_source']);
        if (isset($source['export_source_entity']) && isset($list[$source['export_source_entity']])) {
            $result = $list[$source['export_source_entity']];
        }

        return $result;
    }
}
