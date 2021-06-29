<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio GmbH. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Api\Data;

/**
 * Interface ExportHistoryInterface
 * @package Firebear\ExportExport\Api\Data
 */
interface ExportHistoryInterface
{
    const HISTORY_ID = 'history_id';

    const JOB_ID = 'job_id';

    const STARTED_AT = 'started_at';

    const FINISHED_AT = 'finished_at';

    const TYPE = 'type';

    const FILE = 'file';

    const TEMP_FILE = 'temp_file';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getJobId();

    /**
     * @return date
     */
    public function getStartedAt();

    /**
     * @return date
     */
    public function getFinishedAt();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getFile();

    /**
     * @return string
     */
    public function getTempFile();

    /**
     * @param int $id
     *
     * @return ExportHistoryInterface
     */
    public function setId($id);

    /**
     * @param int $jobId
     *
     * @return ExportHistoryInterface
     */
    public function setJobId($jobId);

    /**
     * @param date $start
     *
     * @return ExportHistoryInterface
     */
    public function setStartedAt($start);

    /**
     * @param date $finish
     *
     * @return ExportHistoryInterface
     */
    public function setFinishedAt($finish);

    /**
     * @param string $type
     *
     * @return ExportHistoryInterface
     */
    public function setType($type);

    /**
     * @param string $file
     *
     * @return ExportHistoryInterface
     */
    public function setFile($file);

    /**
     * @param string $file
     *
     * @return ExportHistoryInterface
     */
    public function setTempFile($file);
}
