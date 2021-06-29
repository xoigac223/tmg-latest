<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Cron;

use Firebear\ImportExport\Model\Job\Processor;

/**
 * Sales entity grids indexing observer.
 *
 * Performs handling cron jobs related to indexing
 * of Order, Invoice, Shipment and Creditmemo grids.
 */
class RunImportJobs
{
    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var \Firebear\ImportExport\Helper\Data
     */
    protected $helper;

    /**
     * RunImportJobs constructor.
     *
     * @param Processor $importProcessor
     */
    public function __construct(
        Processor $importProcessor,
        \Firebear\ImportExport\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $this->processor = $importProcessor;
    }

    /**
     * @param $schedule
     *
     * @return bool
     */
    public function execute($schedule)
    {
        $jobCode = $schedule->getJobCode();

        preg_match('/_id_([0-9]+)/', $jobCode, $matches);
        if (isset($matches[1]) && (int)$matches[1] > 0) {
            $noProblems = 0;
            $jobId = (int)$matches[1];
            $file = $this->helper->beforeRun($jobId);
            $history = $this->helper->createHistory($jobId, $file, 'console');
            $this->processor->debugMode = $this->debugMode = $this->helper->getDebugMode();
            $this->processor->inConsole = 1;
            $this->processor->setLogger($this->helper->getLogger());
            $this->processor->processScope($jobId, $file);
            $this->helper->saveFinishHistory($history);
            $counter = $this->helper->countData($file, $jobId);
            $error = 0;
            for ($i = 0; $i < $counter; $i++) {
                list($count, $result) = $this->helper->processImport($file, $jobId, $i, $error, 0);
                $error += $count;
                if (!$result) {
                    $noProblems = 1;
                    break;
                }
            }
            if (!$noProblems && $this->processor->reindex) {
                $this->processor->processReindex($file, $jobId);
            }
            $this->processor->showErrors();

            return true;
        }

        return false;
    }
}
