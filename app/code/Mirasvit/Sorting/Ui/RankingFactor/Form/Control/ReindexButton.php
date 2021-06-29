<?php

namespace Mirasvit\Sorting\Ui\RankingFactor\Form\Control;

use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

class ReindexButton extends ButtonAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getId()) {
            $data = [
                'label'      => __('Reindex'),
                'class'      => 'apply',
                'on_click'   => 'window.location.href="' . $this->getApplyUrl() . '"',
                'sort_order' => 20,
            ];
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getApplyUrl()
    {
        return $this->getUrl('*/*/reindex', [RankingFactorInterface::ID => $this->getId(), 'back' => true]);
    }
}
