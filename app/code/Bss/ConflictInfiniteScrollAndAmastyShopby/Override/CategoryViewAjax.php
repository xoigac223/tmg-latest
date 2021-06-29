<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ConflictInfiniteScrollAndAmastyShopby
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConflictInfiniteScrollAndAmastyShopby\Override;

use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\Page;

class CategoryViewAjax extends \Amasty\Shopby\Plugin\Ajax\CategoryViewAjax
{
    /**
     * @param Action $controller
     * @param Page $page
     *
     * @return \Magento\Framework\Controller\Result\Raw|Page
     */
    public function afterExecute(Action $controller, $page)
    {
        $request = $controller->getRequest();
        if (!$this->isAjax($request) || !$request->getParam('shopbyAjax', false) || !$page instanceof Page) {
            return $page;
        }

        $responseData = $this->getAjaxResponseData($page);
        $response = $this->prepareResponse($responseData);
        return $response;
    }
}
