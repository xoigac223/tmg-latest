<?php

namespace Mirasvit\Sorting\Cron;

use Mirasvit\Sorting\Model\Indexer;

class ReindexAllCron
{
    private $indexer;

    public function __construct(
        Indexer $indexer
    ) {
        $this->indexer = $indexer;
    }

    public function execute()
    {
        $this->indexer->executeFull();
    }
}