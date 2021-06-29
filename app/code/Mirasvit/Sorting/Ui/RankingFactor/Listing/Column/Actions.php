<?php

namespace Mirasvit\Sorting\Ui\RankingFactor\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = [
                    'edit'   => [
                        'href'  => $this->urlBuilder->getUrl('sorting/rankingFactor/edit', [
                            RankingFactorInterface::ID => $item[RankingFactorInterface::ID],
                        ]),
                        'label' => __('Edit'),
                    ],
                    'reindex'   => [
                        'href'  => $this->urlBuilder->getUrl('sorting/rankingFactor/reindex', [
                            RankingFactorInterface::ID => $item[RankingFactorInterface::ID],
                        ]),
                        'label' => __('Reindex'),
                    ],
                    'delete' => [
                        'href'    => $this->urlBuilder->getUrl('sorting/rankingFactor/delete', [
                            RankingFactorInterface::ID => $item[RankingFactorInterface::ID],
                        ]),
                        'label'   => __('Delete'),
                        'confirm' => [
                            'title'   => __('Delete "${ $.$data.name }"'),
                            'message' => __('Are you sure you wan\'t to delete a "${ $.$data.name }" record?'),
                        ],
                    ],
                ];
            }
        }

        return $dataSource;
    }
}
