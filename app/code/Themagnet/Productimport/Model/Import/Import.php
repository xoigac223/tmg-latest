<?php
namespace Themagnet\Productimport\Model\Import;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\ImportExport\Model\Import\AbstractSource;
use Magento\ImportExport\Model\Import\Adapter;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

class Import extends \Magento\ImportExport\Model\Import
{
    public $importedFile;
    public $startedAt;
    public $importWorkingDir;
    public $_output;
    public $_importlogger;

    public function setWorkingDir($dir)
    {
        return $this->_varDirectory = $dir;
    }

    public function getWorkingDir()
    {
        return $this->_varDirectory->getAbsolutePath($this->importWorkingDir);
    }

    public function importSource()
    {
        $this->setData('entity', $this->getDataSourceModel()->getEntityTypeCode());
        $this->setData('behavior', $this->getDataSourceModel()->getBehavior());
        $this->importHistoryModel->updateReport($this);
        $result = $this->processImport();
        if($this->getErrorAggregator()->getErrorsCount()){
            $this->collectErrors();
        }
        //print_r($result); exit;
        if ($result) {
            $this->_output->writeln(
                [
                    __(
                        'Checked rows: %1, checked entities: %2, invalid rows: %3, total errors: %4',
                        $this->getProcessedRowsCount(),
                        $this->getProcessedEntitiesCount(),
                        $this->getErrorAggregator()->getInvalidRowsCount(),
                        $this->getErrorAggregator()->getErrorsCount()
                    ),
                    __('The import was successful.'),
                ]
            );
            $this->importHistoryModel->updateReport($this, true);
        } else {
            $this->importHistoryModel->invalidateReport($this);
        }

        return $result;
    }

    private function collectErrors()
    {
        $errors = $this->getErrorAggregator()->getAllErrors();
        foreach ($errors as $error) {
            $this->_output->writeln('<comment>Row No.'.$error->getRowNumber().' '.$error->getErrorMessage().'</comment>');
        }
    }

    /**
     * Method uploadFileAndGetSource
     *
     * @return AbstractSource
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function uploadFileAndGetSource($csvImportFile = null)
    {
        $this->importedFile = $csvImportFile ? : $this->importedFile;
        $sourceFile = $this->getWorkingDir() . $this->importedFile;
        return Adapter::findAdapterFor(
            $this->uploadSource($sourceFile),
            $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT),
            $this->getData(self::FIELD_FIELD_SEPARATOR)
        );
    }

    public function uploadSource($sourceFile = null)
    {
       
        $entity = $this->getEntity();
        $extension = pathinfo($this->importedFile, PATHINFO_EXTENSION);
        $result['file'] = $this->importedFile;
        $uploadedFile = $sourceFile;
        if (!$extension) {
            $this->_varDirectory->delete($uploadedFile);
            throw new LocalizedException(__('The file you uploaded has no extension.'));
        }
        $sourceFile = $this->getWorkingDir() . $entity;

        $sourceFile .= '.' . $extension;
        $sourceFileRelative = $this->_varDirectory->getRelativePath($sourceFile);
        if (strtolower($uploadedFile) != strtolower($sourceFile)) {
            if ($this->_varDirectory->isExist($sourceFileRelative)) {
                $this->_varDirectory->delete($sourceFileRelative);
            }

            try {
                $this->_varDirectory->renameFile(
                    $this->_varDirectory->getRelativePath($uploadedFile),
                    $sourceFileRelative
                );
            } catch (FileSystemException $e) {
                throw new LocalizedException(__('The source file moving process failed.'));
            }
        }
        $this->_removeBom($sourceFile);
       
        $this->createHistoryReport($sourceFileRelative, $entity, $extension, $result);
        // trying to create source adapter for file and catch possible exception to be convinced in its adequacy
        try {
            $this->_getSourceAdapter($sourceFile);
        } catch (\Exception $e) {
            $this->_varDirectory->delete($sourceFileRelative);
            throw new LocalizedException(__($e->getMessage()));
        }
        return $sourceFile;
    }

    protected function createHistoryReport($sourceFileRelative, $entity, $extension = null, $result = null)
    {
        if ($this->isReportEntityType($entity)) {
            $copyName = $result['file'];
            $this->importHistoryModel->addReport($copyName);
        }
        return $this;
    }
}