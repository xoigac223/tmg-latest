<?php
namespace Firebear\ConfigurableProducts\Block\Adminhtml\Product\Steps;

use Magento\Ui\Block\Component\StepsWizard\StepAbstract;

class DefaultValues extends StepAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Default Values');
    }
}
