<?php
declare(strict_types=1);

namespace Bss\LiveChat\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Bss\LiveChat\Helper\Data
 */
class Data extends AbstractHelper
{
    const XPATH_LIVE_CHAT_ENABLE = 'livechat/general/enable';
    const XPATH_LIVE_CHAT_CONFIG = 'livechat/general/config';

    /**
     * GetConfig
     *
     * @param  $path
     * @return mixed
     */
    protected function getConfig($path)
    {
        return  $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check module enable
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getConfig(self::XPATH_LIVE_CHAT_ENABLE);
    }

    /**
     * GetLiveChatConfig
     *
     * @return mixed
     */
    public function getLiveChatConfig()
    {
        return $this->getConfig(self::XPATH_LIVE_CHAT_CONFIG);
    }
}