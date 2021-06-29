<?php

namespace Mirasvit\Sorting\Ui\RankingFactor\Form\Control;

class PreviewButton extends ButtonAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $ns = 'sorting_rankingFactor_form.sorting_rankingFactor_form';

        return [
            'label'          => __('Preview'),
            'class'          => 'preview',
            'sort_order'     => 30,
            'on_click'       => '',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => $ns . '.preview_modal',
                                'actionName' => 'toggleModal',
                            ],
                            [
                                'targetName' => $ns . '.preview_modal.preview_listing',
                                'actionName' => 'render',
                            ],
                            [
                                'targetName' => $ns . '.preview_modal.preview_listing',
                                'actionName' => 'reload',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
