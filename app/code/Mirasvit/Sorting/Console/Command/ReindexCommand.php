<?php

namespace Mirasvit\Sorting\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Sorting\Api\Repository\RankingFactorRepositoryInterface;
use Mirasvit\Sorting\Model\Indexer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReindexCommand extends Command
{
    private $objectManager;

    private $appState;

    public function __construct(
        ObjectManagerInterface $objectManager,
        State $appState
    ) {
        $this->objectManager = $objectManager;
        $this->appState      = $appState;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('mirasvit:sorting:reindex')
            ->setDescription('Improved Sorting Reindex');

        $this->addArgument('id', InputArgument::OPTIONAL, 'Ranking Factor Id');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode('frontend');
        } catch (\Exception $e) {
        }

        /** @var RankingFactorRepositoryInterface $repository */
        $repository = $this->objectManager->create(RankingFactorRepositoryInterface::class);

        foreach ($repository->getCollection() as $rankingFactor) {

            if ($input->getArgument('id') && $input->getArgument('id') != $rankingFactor->getId()) {
                $output->writeln(sprintf(
                    'Skip [%s] "%s"',
                    $rankingFactor->getId(),
                    $rankingFactor->getName()
                ));

                continue;
            }

            $output->write(sprintf(
                'Reindex [%s] "%s"...',
                $rankingFactor->getId(),
                $rankingFactor->getName()
            ));

            /** @var Indexer $indexer */
            $indexer = $this->objectManager->create(Indexer::class);

            $ts  = microtime(true);
            $mem = memory_get_usage();

            $indexer->executeFull([$rankingFactor->getId()]);

            $output->writeln(sprintf(
                "<info>done</info> (%s / %s)",
                round(microtime(true) - $ts, 4) . 's',
                round((memory_get_usage() - $mem) / 1024 / 1024, 2) . 'Mb'
            ));
        }
    }
}
