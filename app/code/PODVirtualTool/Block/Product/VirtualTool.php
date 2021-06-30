<?php
declare(strict_types=1);

namespace Bss\PODVirtualTool\Block\Product;

use Magento\Framework\View\Element\Template;

/**
 * Class Virtual.
 */
class VirtualTool extends Template
{
    /**
     * Virtual constructor.
     *
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
}
