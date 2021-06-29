<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Helper;

use Firebear\ImportExport\Model\Source\Factory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Firebear\ImportExport\Model\Source\Config;
use Firebear\ImportExport\Api\HistoryRepositoryInterface\Proxy as History;
use Firebear\ImportExport\Api\ExHistoryRepositoryInterface\Proxy as ExHistory;
use Firebear\ImportExport\Model\Job\Processor;
use Firebear\ImportExport\Model\ExportJob\Processor as ExportProcessor;
use Firebear\ImportExport\Model\Import\HistoryFactory;
use Firebear\ImportExport\Model\Export\HistoryFactory as ExportFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface\Proxy as Timezone;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Data
 *
 * @package Firebear\ImportExport\Helper
 */
class Data extends AbstractHelper
{
    const GENERAL_DEBUG = 'firebear_importexport/general/debug';

    /**
     * @var Factory
     */
    protected $sourceFactory;

    /**
     * @var Config
     */
    protected $configSource;

    protected $typeInt = [
        'int',
        'smallint',
        'tinyint',
        'mediumint',
        'bigint',
        'bit',
        'float',
        'double',
        'decimal'
    ];

    protected $typeText = [
        'char',
        'varchar',
        'tinytext',
        'text',
        'mediumtext',
        'longtext',
        'json'
    ];

    protected $typeDate = [
        'date',
        'time',
        'year',
        'datetime',
        'timestamp'
    ];

    /**
     * @var ScopeConfigInterface
     */
    protected $coreConfig;

    /**
     * @var \Firebear\ImportExport\Logger\Logger
     */
    protected $logger;

    /**
     * @var HistoryRepositoryInterface
     */
    protected $historyRepository;

    /**
     * @var ExHistoryRepositoryInterface
     */
    protected $historyExRepository;

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var ExportFactory
     */
    protected $exportFactory;

    /**
     * @var TimezoneInterface
     */
    protected $timeZone;

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var ExportProcessor
     */
    protected $exProcessor;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $directory;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $root;

    protected $resultProcess;

    /**
     * @var \Firebear\ImportExport\Model\ResourceModel\Import\DataFactory
     */
    protected $dataFactory;

    protected $indexFactory;

    /**
     * @var array|mixed|null
     */
    protected $platforms;

    /**
     * @var \Firebear\ImportExport\Model\Source\Factory
     */
    protected $factory;
    /** @var \Magento\Framework\Json\DecoderInterface  */
    protected $jsonDecoder;

    /**
     * Data constructor.
     * @param Context $context
     * @param Factory $sourceFactory
     * @param Config $configSource
     * @param \Firebear\ImportExport\Logger\Logger $logger
     * @param History $historyRepository
     * @param ExHistory $historyExRepository
     * @param HistoryFactory $historyFactory
     * @param ExportFactory $exportFactory
     * @param Processor $processor
     * @param ExportProcessor $exProcessor
     * @param Timezone $timezone
     * @param Filesystem $filesystem
     * @param \Firebear\ImportExport\Model\ResourceModel\Import\DataFactory $dataFactory
     * @param \Firebear\ImportExport\Model\Source\Platform\Config $configPlatforms
     * @param Factory $factory
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        Context $context,
        Factory $sourceFactory,
        Config $configSource,
        \Firebear\ImportExport\Logger\Logger $logger,
        History $historyRepository,
        ExHistory $historyExRepository,
        HistoryFactory $historyFactory,
        ExportFactory $exportFactory,
        Processor $processor,
        ExportProcessor $exProcessor,
        Timezone $timezone,
        Filesystem $filesystem,
        \Firebear\ImportExport\Model\ResourceModel\Import\DataFactory $dataFactory,
        \Firebear\ImportExport\Model\Source\Platform\Config $configPlatforms,
        \Firebear\ImportExport\Model\Source\Factory $factory,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->sourceFactory = $sourceFactory;
        $this->configSource = $configSource;
        $this->coreConfig = $context->getScopeConfig();
        $this->historyRepository = $historyRepository;
        $this->historyExRepository = $historyExRepository;
        $this->historyFactory = $historyFactory;
        $this->exportFactory = $exportFactory;
        $this->timeZone = $timezone;
        $this->processor = $processor;
        $this->exProcessor = $exProcessor;
        $this->logger = $logger;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::LOG);
        $this->root = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->dataFactory = $dataFactory;
        $this->platforms = $configPlatforms->get();
        $this->factory = $factory;
        $this->jsonDecoder = $jsonDecoder;

        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getConfigFields()
    {
        $list = [];
        $types = $this->configSource->get();
        foreach ($types as $typeName => $type) {
            foreach ($type['fields'] as $name => $values) {
                if (!isset($list[$name])) {
                    $list[] = $name;
                }
            }
        }

        return array_unique($list);
    }

    /**
     * @param $type
     * @return string
     */
    public function convertTypesTables($type)
    {
        $changed = 0;
        if (in_array($type, $this->typeInt)) {
            $type = 'int';
            $changed = 1;
        }
        if (in_array($type, $this->typeText)) {
            $type = 'text';
            $changed = 1;
        }
        if (in_array($type, $this->typeDate)) {
            $type = 'date';
            $changed = 1;
        }
        if (!$changed) {
            $type = 'not';
        }

        return $type;
    }

    /**
     * @return bool
     */
    public function getDebugMode()
    {
        return (bool)$this->coreConfig->getValue(
            self::GENERAL_DEBUG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $id
     * @return string
     */
    public function beforeRun($id)
    {
        $date = $this->timeZone->date();
        $timeStamp = $date->getTimestamp();

        return $id . "-" . $timeStamp;
    }

    /**
     * @param $id
     * @param $file
     * @return array
     */
    public function runImport($id, $file)
    {
        try {
            $history = $this->createHistory($id, $file, 'admin');
            $this->processor->debugMode = $this->getDebugMode();
            $this->processor->setLogger($this->logger);
            $result = $this->processor->processScope($id, $file);
            $date = $this->timeZone->date();
            $timeStamp = $date->getTimestamp();
            $history->setFinishedAt($timeStamp);
            $this->setResultProcessor($result);
            $this->historyRepository->save($history);
        } catch (\Exception $e) {
            $this->addLogComment(
                'Job #' . $id . ' can\'t be imported. Check if job exist',
                'error'
            );
            $this->addLogComment(
                $e->getMessage(),
                'error'
            );
            return false;
        }

        return $result;
    }

    /**
     * @param $id
     * @param $file
     * @return array
     */
    public function runExport($id, $file)
    {
        try {
            $res = [];
            $history = $this->createExportHistory($id, $file, 'admin');
            $this->exProcessor->debugMode = $this->getDebugMode();
            $this->exProcessor->setLogger($this->logger);
            $result = $this->exProcessor->process($id);
            $exportJob = $this->exProcessor->getJobModel($id);
            $sourceData = $this->jsonDecoder->decode($exportJob->getExportSource());
            $res = [$result, $sourceData['last_entity_id']];
            $date = $this->timeZone->date();
            $timeStamp = $date->getTimestamp();
            $history->setFinishedAt($timeStamp);
            $this->setResultProcessor($res);
            $this->historyExRepository->save($history);
        } catch (\Exception $e) {
            $this->addLogComment(
                'Job #' . $id . ' can\'t be exported. Check if job exist',
                'error'
            );
            $this->addLogComment(
                $e->getMessage(),
                'error'
            );
        }

        return $this->exProcessor->getExportFile();
    }

    /**
     * @param $debugData
     * @param null $type
     * @return $this
     */
    protected function addLogComment($debugData, $type = null)
    {
        $this->logger->info($debugData);

        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function createHistory($id, $file, $type)
    {
        $history = $this->historyFactory->create();
        $history->setJobId($id);
        $history->setType($type);
        $date = $this->timeZone->date();
        $timeStamp = $date->getTimestamp();
        $history->setStartedAt($timeStamp);
        $this->logger->setFileName($file);
        $history->setFile($file);
        $history = $this->historyRepository->save($history);

        return $history;
    }


    /**
     * @param $id
     * @return \Firebear\ImportExport\Api\Data\ImportHistoryInterface
     */
    public function loadHistory($id)
    {
        $collection = $this->historyFactory->create()->getCollection()->addFieldToFilter('history_id', $id);
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }

        return null;
    }

    /**
     * @param $id
     * @return \Firebear\ImportExport\Api\Data\ImportHistoryInterface
     */
    public function loadExHistory($id)
    {
        $collection = $this->exportFactory->create()->getCollection()->addFieldToFilter('history_id', $id);
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }

        return null;
    }

    /**
     * @param $id
     * @return $this
     */
    public function createExportHistory($id, $file, $type)
    {
        $history = $this->exportFactory->create();
        $history->setJobId($id);
        $history->setType($type);
        $date = $this->timeZone->date();
        $timeStamp = $date->getTimestamp();
        $history->setStartedAt($timeStamp);
        $this->logger->setFileName($file);
        $history->setFile($file);
        $history = $this->historyExRepository->save($history);

        return $history;
    }

    /**
     * @param $file
     * @return array|bool
     */
    public function scopeRun($file, $number)
    {
        $newFile = '';
        if ($this->directory->isFile("/firebear/" . $file . ".log")) {
            foreach ($this->fileLines($file) as $key => $line) {
                if ($number == 0 || $key > $number) {
                    $newFile .= $line;
                }
            }
        }

        return $newFile;
    }

    /**
     * @param $filename
     * @return \Generator
     */
    protected function fileLines($filename)
    {
        $data = explode(PHP_EOL, $this->directory->readFile("/firebear/" . $filename . ".log"));
        foreach ($data as $key => $line) {
            if (strlen($line) > 0) {
                $line = $line . '<span text="item"></span><br/>';
                yield $key => $line;
            }
        }
    }

    /**
     * @param $history
     * @return $this
     */
    public function saveFinishHistory($history)
    {
        $date = $this->timeZone->date();
        $timeStamp = $date->getTimestamp();
        $history->setFinishedAt($timeStamp);
        $this->historyRepository->save($history);

        return $this;
    }

    /**
     * @param $history
     * @return $this
     */
    public function saveFinishExHistory($history)
    {
        $date = $this->timeZone->date();
        $timeStamp = $date->getTimestamp();
        $history->setFinishedAt($timeStamp);
        $this->historyExRepository->save($history);

        return $this;
    }

    /**
     * @return \Firebear\ImportExport\Logger\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return mixed
     */
    public function getResultProcessor()
    {
        return $this->resultProcess;
    }

    /**
     * @param $result
     */
    public function setResultProcessor($result)
    {
        $this->resultProcess = $result;
    }

    /**
     * @param $file
     */
    public function setTypeSource($file)
    {
        $this->processor->setTypeSource($file);
    }

    /**
     * @param $data
     * @return bool
     */
    public function correctData($data)
    {
        return $this->processor->correctData($data);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function processValidate($data)
    {
        return $this->processor->processValidate($data);
    }

    public function revertLocale()
    {
        $this->processor->revertLocale();
    }

    /**
     * @param $file
     * @param $job
     * @param $offset
     * @return mixed
     */
    public function processImport($file, $job, $offset, $error, $show = 1)
    {
        $this->processor->setLogger($this->logger);

        return $this->processor->processImport($file, $job, $offset, $error, $show);
    }

    /**
     * @param $file
     * @param $job
     * @return bool
     */
    public function processReindex($file, $job)
    {
        $this->processor->setLogger($this->logger);

        return $this->processor->processReindex($file, $job);
    }

    /**
     * @param $file
     * @param $job
     * @return mixed
     */
    public function countData($file, $job)
    {
        $data = $this->dataFactory->create()->getCounts($job, $file);

        return $data[0];
    }

    /**
     * @return Processor
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    public function getPlatformModel($model)
    {
        if (isset($this->platforms[$model]['model'])) {
            return $this->factory->create($this->platforms[$model]['model']);
        }

        return null;
    }
}
