<?php
/**
 * @copyright: Copyright © 2018 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Import\Product\Type\Grouped;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\ImportExport\Model\Import;

/**
 * Class Downloadable
 */
class Links extends \Magento\GroupedImportExport\Model\Import\Product\Type\Grouped\Links
{

    protected $fireImportFactory;
    /** @var \Firebear\ImportExport\Api\JobRepositoryInterface  */
    protected $importJobRepository;
    /** @var \Magento\Framework\Json\DecoderInterface  */
    protected $jsonDecoder;

    /**
     * Links constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Link $productLink
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ImportFactory $importFactory
     * @param \Firebear\ImportExport\Model\ImportFactory $fireImportFactory
     * @param \Firebear\ImportExport\Api\JobRepositoryInterface $importJobRepository
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Link $productLink,
        ResourceConnection $resource,
        \Magento\ImportExport\Model\ImportFactory $importFactory,
        \Firebear\ImportExport\Model\ImportFactory $fireImportFactory,
        \Firebear\ImportExport\Api\JobRepositoryInterface $importJobRepository,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        parent::__construct($productLink, $resource, $importFactory);
        $this->fireImportFactory = $fireImportFactory;
        $this->importJobRepository = $importJobRepository;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * @return string
     */
    protected function getBehavior()
    {
        if ($this->behavior === null) {
            $this->behavior = $this->fireImportFactory->create()->getFireDataSourceModel()->getBehavior();
        }

        return $this->behavior;
    }

    protected function deleteOldLinks($productIds)
    {
        $jobId = $this->fireImportFactory->create()->getFireDataSourceModel()->getUniqueColumnData('job_id');
        $importJobData = $this->importJobRepository->getById($jobId);
        $sourceData = $this->jsonDecoder->decode($importJobData->getSourceData());
        if ($this->getBehavior() != \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND
            || (isset($sourceData['remove_product_association']) && $sourceData['remove_product_association'] == 1)
        ) {
            $this->connection->delete(
                $this->productLink->getMainTable(),
                $this->connection->quoteInto(
                    'product_id IN (?) AND link_type_id = ' . $this->getLinkTypeId(),
                    $productIds
                )
            );
        }
    }
}
