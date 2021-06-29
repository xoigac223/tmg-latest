<?php

namespace Themagnet\Orderstatus\Block\Adminhtml\Orderstatus\Edit;

use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData()
    {
        $url = $this->getUrl('themagnet_orderstatus/index/save');
        return [
            'label' => __('Check Order'),
            'class' => 'save primary',
            'on_click' => "setLocation('". $url ."')",
            'sort_order' => 90,
        ];

    }

}