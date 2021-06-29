<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ConfigurableProducts\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Catalog\Model\Product\Visibility;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;

/**
 * Command prints list of available currencies
 */
class GenerateUrlRewrite extends Command
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * Generates url rewrites for different scopes.
     *
     * @var ProductScopeRewriteGenerator
     */
    protected $rewriteGenerator;

    /**
     * GenerateUrlRewrite constructor.
     *
     * @param CollectionFactory            $collectionFactory
     * @param StoreManagerInterface        $storeManager
     * @param UrlPersistInterface          $urlPersist
     * @param ProductScopeRewriteGenerator $rewriteGenerator
     *
     * @internal param CollectionFactory $сollectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        UrlPersistInterface $urlPersist,
        ProductScopeRewriteGenerator $rewriteGenerator
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->urlPersist = $urlPersist;
        $this->rewriteGenerator = $rewriteGenerator;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('firebear:url-rewrite:generate')
            ->setDescription('Generate Url Rewrites for hidden products');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stores = $this->storeManager->getStores();

        $productCollection = $this->collectionFactory->create();
        $productCollection
            ->addFieldToFilter('type_id', ['eq' => 'simple'])
            ->addFieldToFilter(
                'visibility',
                ['eq' => Visibility::VISIBILITY_NOT_VISIBLE]
            )
            ->addAttributeToSelect(['name', 'url_path', 'url_key', 'visibility']);

        $productsCount = $productCollection->getSize();
        $output->writeln('<info>Found ' . $productsCount . ' products</info>');
        $output->write("\033[0G");

        $rowNumber = 1;
        foreach ($productCollection as $product) {
            $progress = floor($rowNumber++ / 100);
            $output->write("\033[0G");
            $output->write("Progress: $progress% ($rowNumber/$productsCount)");

            foreach ($stores as $store) {
                $filterData = [
                    UrlRewrite::ENTITY_ID   => $product->getId(),
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                    UrlRewrite::STORE_ID    => $store->getId(),
                ];

                $rewrite = $this->urlPersist->findOneByData($filterData);

                if (!$rewrite) {
                    $this->urlPersist->replace($this->generateUrls($product));
                }
            }
        }
        $output->write("\033[0G");
        $output->writeln("Progress: 100% ($productsCount/$productsCount)");
        $output->writeln('<info>Done</info>');
    }

    /**
     * Generate product urls.
     *
     * @param Product $product
     *
     * @return array|UrlRewrite[]
     */
    protected function generateUrls(Product $product)
    {
        $storeId = $product->getStoreId();

        $productCategories = $product->getCategoryCollection()
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('url_path');

        $urls = $this->rewriteGenerator->isGlobalScope($storeId)
            ? $this->rewriteGenerator->generateForGlobalScope($productCategories, $product)
            : $this->rewriteGenerator->generateForSpecificStoreView($storeId, $productCategories, $product);

        return $urls;
    }
}