<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 *
 */

namespace Ubertheme\UbMegaMenu\Model;

class Processor
{
    public function __construct(
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Cms\Model\BlockFactory $blockFactory
    ) {
        $this->_filterProvider = $filterProvider;
        $this->_blockFactory = $blockFactory;
    }

    public function filter($content){
        return $this->_filterProvider->getBlockFilter()->filter($content);
    }
}
