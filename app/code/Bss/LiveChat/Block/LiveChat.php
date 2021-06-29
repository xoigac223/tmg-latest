<?php
declare(strict_types=1);

namespace Bss\LiveChat\Block;

use Bss\LiveChat\Helper\Data;
use Magento\Framework\View\Element\Template;

/**
 * Class LiveChat
 */
class LiveChat extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * LiveChat constructor.
     *
     * @param Data $helper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Data $helper,
        Template\Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * GetLiveChatConfig
     *
     * @return mixed
     */
    public function getLiveChatConfig()
    {
        return $this->helper->getLiveChatConfig();
    }

    /**
     * Check Module Is Enable
     *
     * @return bool
     */
    public function isEnable()
    {
        return $this->helper->isEnabled();
    }
}
