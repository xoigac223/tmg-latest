<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */
namespace Firebear\ImportExport\Model\Export\Adapter;

use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Magento\Framework\Filesystem;
use Magento\Framework\Exception\LocalizedException;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

/**
 * Ods Export Adapter
 */
class Ods extends AbstractAdapter
{
    /**
     * Spreadsheet Writer
     *
     * @var \
     */
    protected $writer;
    
    /**
     * File Path
     *
     * @var string|bool
     */
    protected $filePath;
    
    /**
     * Adapter Data
     *
     * @var []
     */
    protected $_data;
    
    /**
     * Initialize Adapter
     *
     * @param Filesystem $filesystem
     * @param null $destination
     * @param [] $data
     */
    public function __construct(
        Filesystem $filesystem,
        $destination = null,
        array $data = []
    ) {
        $this->_data = $data;
        if (empty($this->_data['export_source']['file_path'])) {
            throw new LocalizedException(__('Export File Path is Empty.'));
        }
        
        $alias = 'Box\Spout\Writer\Common\Helper\CellHelper';
        $original = 'Firebear\ImportExport\Model\Export\Adapter\Spout\CellHelper';
        class_alias($original, $alias);
        
        parent::__construct(
            $filesystem,
            $destination
        );
    }
    
    /**
     * Method called as last step of object instance creation
     *
     * @return AbstractAdapter
     */
    protected function _init()
    {
        $this->writer = WriterFactory::create(Type::ODS);
        $file = $this->_directoryHandle->getAbsolutePath(
            $this->_destination
        );
        $this->writer->openToFile($file);
        return $this;
    }
    
    /**
     * Write row data to source file
     *
     * @param array $rowData
     * @return AbstractAdapter
     */
    public function writeRow(array $rowData)
    {
        $rowData = $this->_prepareRow($rowData);
        if (null === $this->_headerCols) {
            $this->setHeaderCols(array_keys($rowData));
        }
        $rowData = array_merge(
            $this->_headerCols,
            array_intersect_key($rowData, $this->_headerCols)
        );
        $this->writer->addRow($rowData);
        return $this;
    }
    
    /**
     * Prepare Row Data
     *
     * @param array $rowData
     * @return array $rowData
     */
    protected function _prepareRow(array $rowData)
    {
        foreach ($rowData as $key => $value) {
            $rowData[$key] = (string)$value;
        }
        return $rowData;
    }
    
    /**
     * Set column names
     *
     * @param array $headerColumns
     * @return AbstractAdapter
     */
    public function setHeaderCols(array $headerColumns)
    {
        if (null !== $this->_headerCols) {
            throw new LocalizedException(__('The header column names are already set.'));
        }
        if ($headerColumns) {
            foreach ($headerColumns as $columnName) {
                $this->_headerCols[$columnName] = false;
            }
            $this->writer->addRow(array_keys($this->_headerCols));
        }
        return $this;
    }
    
    /**
     * Get contents of export file
     *
     * @return string
     */
    public function getContents()
    {
        $this->writer->close();
        return parent::getContents();
    }
    
    /**
     * MIME-type for 'Content-Type' header
     *
     * @return string
     */
    public function getContentType()
    {
        return 'application/vnd.oasis.opendocument.spreadsheet';
    }
    
    /**
     * Return file extension for downloading
     *
     * @return string
     */
    public function getFileExtension()
    {
        return 'ods';
    }
}
