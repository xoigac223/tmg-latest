<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Firebear\ImportExport\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Validator
 *
 * @api
 * @since 100.0.2
 */
class Validator extends \Magento\CatalogImportExport\Model\Import\Product\Validator
{
    /**
     * @var RowValidatorInterface[]|AbstractValidator[]
     */
    protected $parameters = [];

    public function setParameters($params)
    {
        $this->parameters = $params;
        return $this;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getContext()
    {
        return $this->context;
    }
}
