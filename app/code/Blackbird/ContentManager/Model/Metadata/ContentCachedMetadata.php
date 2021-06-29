<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Blackbird\ContentManager\Model\Metadata;

use Blackbird\ContentManager\Api\ContentMetadataInterface;

/**
 * Cached content attribute metadata service
 */
class ContentCachedMetadata extends CachedMetadata implements ContentMetadataInterface
{
    /**
     * Initialize dependencies.
     *
     * @param ContentMetadata $metadata
     */
    public function __construct(ContentMetadata $metadata)
    {
        $this->metadata = $metadata;
    }
}
