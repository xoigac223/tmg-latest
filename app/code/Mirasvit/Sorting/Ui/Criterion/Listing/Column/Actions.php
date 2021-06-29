<?php

namespace Mirasvit\Sorting\Ui\Criterion\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mirasvit\Sorting\Api\Data\CriterionInterface;

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
                        'href'  => $this->urlBuilder->getUrl('sorting/criterion/edit', [
                            CriterionInterface::ID => $item[CriterionInterface::ID],
                        ]),
                        'label' => __('Edit'),
                    ],
                    'delete' => [
                        'href'    => $this->urlBuilder->getUrl('sorting/criterion/delete', [
                            CriterionInterface::ID => $item[CriterionInterface::ID],
                        ]),
                        'label'   => __('Delete'),
                        'confirm' => [
                            'title'   => __('Delete "${ $.$data.title }"'),
                            'message' => __('Are you sure you wan\'t to delete a "${ $.$data.title }" record?'),
                        ],
                    ],
                ];
            }
        }

        return $dataSource;
    }
}
