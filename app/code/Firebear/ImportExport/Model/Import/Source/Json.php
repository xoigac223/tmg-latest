<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Import\Source;

use Magento\ImportExport\Model\Import\AbstractEntity;

/**
 * CSV import adapter
 */
class Json extends \Magento\ImportExport\Model\Import\AbstractSource
{

    use \Firebear\ImportExport\Traits\Import\Map;

    const CREATE_ATTRIBUTE = 'create_attribute';

    /**
     * @var Resource
     */
    protected $stream;

    /**
     * @var \JsonStreamingParser\Listener\InMemoryListener $listener
     */
    protected $listener;

    protected $maps;
    /**
     * Delimiter.
     *
     * @var string
     */
    protected $delimiter = ',';

    /**
     * @var string
     */
    protected $enclosure = '';

    protected $extension = 'json';

    protected $mimeTypes = [];

    protected $platform;

    protected $entities;


    /**
     * Json constructor.
     *
     * @param array $file
     * @param \Magento\Framework\Filesystem\Directory\Read $directory
     * @param string $delimiter
     * @param string $enclosure
     *
     * @throws \Exception
     */
    public function __construct(
        $file,
        \Magento\Framework\Filesystem\Directory\Read $directory,
        $delimiter = ',',
        $enclosure = '"'
    ) {
        $result = $this->checkMimeType($directory->getAbsolutePath($file));

        if ($result !== true) {
            throw new \Exception($result);
        }
        $this->listener = new \JsonStreamingParser\Listener\InMemoryListener();
        try {
            $this->stream = fopen($directory->getAbsolutePath($file), 'r');
            $parser = new \JsonStreamingParser\Parser($this->stream, $this->listener);
            $parser->parse();
            fclose($this->stream);
            $this->entities = current($this->listener->getJson());
        } catch (\Exception $e) {
            fclose($this->stream);
            throw $e;
        }
        if ($delimiter) {
            $this->delimiter = $delimiter;
        }
        $this->enclosure = $enclosure;

        $this->_construct();
    }

    /**
     * Close file handle
     *
     * @return void
     */
    public function destruct()
    {
        fclose($this->stream);
    }

    /**
     * Read next line from JSON-file
     *
     * @return array|bool
     */
    protected function _getNextRow()
    {
        $key = $this->key();

        if ($key >= count($this->entities)) {
            return false;
        }
        $parsed = [];

        foreach ($this->entities[$this->key()] as $name => $data) {
            if ($name == self::CREATE_ATTRIBUTE) {
                $text = 'attribute';
                $valueText = '';
                foreach ($data as $nameAttribute => $valueAttribute) {
                    if ($nameAttribute != 'value') {
                        $text .= "|" . $nameAttribute . ":" . $valueAttribute;
                    } else {
                        $valueText = $valueAttribute;
                    }
                }
                $parsed[$text] = $valueText;
                continue;
            }
            $value = $data;
            if (is_string($value) && strpos($value, "'") !== false) {
                $this->_foundWrongQuoteFlag = true;
            }
            $parsed[$name] = $value;
        }

        return is_array($parsed) ? $parsed : [];
    }

    protected function _construct()
    {
        for ($this->rewind(); $this->valid(); $this->next()) {
            $colNames = array_keys($this->_getNextRow());
            if (empty($colNames)) {
                throw new \InvalidArgumentException('Empty column names');
            }
            if (count(array_unique($colNames)) != count($colNames)) {
                throw new \InvalidArgumentException('Duplicates found in column names: ' . var_export($colNames, 1));
            }

            $diffArray = array_diff($colNames, $this->_colNames);

            $this->_colNames = array_merge($this->_colNames, $diffArray);
            $this->_colQty = count($this->_colNames);
        }
    }

    /**
     * @return array
     */
    public function current()
    {

        $row = $this->_row;

        $diffArray = array_diff($this->_colNames, array_keys($row));

        if (count($diffArray)) {
            foreach ($diffArray as $name) {
                $row[$name] = '';
            }
        }

        $array = $this->replaceValue($this->changeFields($row));

        return $array;
    }

    /**
     * @return mixed
     */
    public function getColNames()
    {
        return $this->replaceColumns($this->_colNames);
    }

    /**
     * @param $platform
     * @return $this
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlatform()
    {
        return $this->platform;
    }
}
