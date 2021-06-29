<?php

namespace Mirasvit\Sorting\Plugin\Indexer\Model\Indexer;

use Magento\Framework\Mview\ConfigInterface;
use Magento\Framework\Mview\ViewInterface;
use Magento\Framework\Mview\ViewInterfaceFactory;
use Magento\Indexer\Model\Indexer;
use Mirasvit\Sorting\Model\Indexer as SortingIndexer;

class SwitchMviewStatePlugin
{
    /**
     * @var ConfigInterface
     */
    private $mviewConfig;

    /**
     * @var ViewInterface
     */
    private $viewFactory;

    public function __construct(ConfigInterface $mviewConfig, ViewInterfaceFactory $viewFactory)
    {
        $this->mviewConfig = $mviewConfig;
        $this->viewFactory = $viewFactory;
    }

    /**
     * Activate mview indexers for Improved Sorting criteria.
     *
     * @param Indexer $subject
     * @param bool    $scheduled
     */
    public function beforeSetScheduled(Indexer $subject, $scheduled)
    {
        if ($subject->getId() === SortingIndexer::INDEXER_ID) {
            foreach ($this->getCriteriaViews() as $view) {
                if ($scheduled) {
                    $view->subscribe();
                } else {
                    $view->unsubscribe();
                }
            }
        }
    }

    /**
     * Get mviews associated with the Improved Sorting criteria.
     * @return \Generator|ViewInterface[]
     */
    private function getCriteriaViews()
    {
        foreach ($this->mviewConfig->getViews() as $viewId => $viewData) {
            if (strpos($viewId, SortingIndexer::INDEXER_ID . '_') !== false) {
                yield $this->viewFactory->create()->load($viewId);
            }
        }
    }
}
