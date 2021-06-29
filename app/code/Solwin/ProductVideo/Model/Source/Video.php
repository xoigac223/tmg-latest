<?php
/**
 * Solwin Infotech
 * Solwin Advanced Product Video Extension
 *
 * @category   Solwin
 * @package    Solwin_ProductVideo
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
namespace Solwin\ProductVideo\Model\Source;

use Solwin\ProductVideo\Model\VideoFactory;
use Magento\Eav\Model\ResourceModel\Entity\AttributeFactory;

class Video extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @var VideoFactory
     */
    protected $_modelVideoFactory;
    
    /**
     * @var AttributeFactory
     */
    protected $_entityAttributeFactory;

    public function __construct(
        VideoFactory $modelVideoFactory,
        AttributeFactory $entityAttributeFactory
    ) {
        $this->_modelVideoFactory = $modelVideoFactory;
        $this->_entityAttributeFactory = $entityAttributeFactory;
    }

    public function getAllOptions() {
        $this->_options = [];
        if (!$this->_options) {
            $collection = $this->_modelVideoFactory->create()->getCollection();

            foreach ($collection as $val) {
                $this->_options[] = [
                    'value' => $val->getVideoId(),
                    'label' => $val->getTitle(),
                ];
            }
        }
        return $this->_options;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns() {
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => true,
                'default' => null,
                'extra' => null,
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => $attributeCode . ' tax column',
            ],
        ];
    }

    /**
     * Retrieve Select for update attribute value in flat table
     *
     * @param   int $store
     * @return  \Magento\Framework\DB\Select|null
     */
    public function getFlatUpdateSelect($store) {
        return $this->_entityAttributeFactory
                ->create()
                ->getFlatUpdateSelect($this->getAttribute(), $store);
    }

}
