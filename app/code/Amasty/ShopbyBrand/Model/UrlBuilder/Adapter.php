<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Model\UrlBuilder;

use Magento\Framework\App\RequestInterface;

class Adapter implements \Amasty\ShopbyBase\Api\UrlBuilder\AdapterInterface
{
    const SELF_ROUTE_PATH = 'ambrand/index/index';
    const SEO_BRAND_MODULES = ['amshopby', 'cms'];
    const MODULE_NAME = 'ambrand';
    const SAME_PAGE_ROUTE = '*/*/*';

    /**
     * @var \Magento\Framework\Url
     */
    private $urlBuilder;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $brandHelper;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        \Magento\Framework\Url $urlBuilder,
        \Amasty\ShopbyBrand\Helper\Data $brandHelper,
        RequestInterface $request
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->brandHelper = $brandHelper;
        $this->request = $request;
    }

    /**
     * @param null $routePath
     * @param null $routeParams
     * @return string|null
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        $brandAttributeCode = $this->brandHelper->getBrandAttributeCode();
        $routePath = trim($routePath, '/');
        if (($routePath == self::SELF_ROUTE_PATH && isset($routeParams['id']))) {
            $aliases = $this->brandHelper->getBrandAliases();
            if (isset($aliases[$routeParams['id']])) {
                $routePath = $aliases[$routeParams['id']];
                unset($routeParams['id']);
                if ($urlKey = $this->brandHelper->getBrandUrlKey()) {
                    $routePath = $urlKey . '/' . $routePath;
                }

                if ($suffix = $this->getSuffix()) {
                    $routePath .= $suffix;
                }
                $routeParams['_direct'] = $routePath;
                $routePath = '';
            }
            return $this->urlBuilder->getUrl($routePath, $routeParams);
        } elseif ($brandAttributeCode
            && ($this->request->has($brandAttributeCode)
                || isset($routeParams['_query'][$brandAttributeCode])
            )
            && ((in_array($this->request->getModuleName(), self::SEO_BRAND_MODULES)
                || ($routePath == self::SAME_PAGE_ROUTE && $this->request->getModuleName() == self::MODULE_NAME))
            )
        ) {
            $brandId = $this->request->getParam($brandAttributeCode) ?: $routeParams['_query'][$brandAttributeCode];
            $aliases = $this->brandHelper->getBrandAliases();
            if (isset($aliases[$brandId])) {
                $routePath = $aliases[$brandId];
                unset($routeParams['_query'][$brandAttributeCode]);
                //@TODO remove this after seofy refectoring
                if ($this->request->getQueryValue($brandAttributeCode)) {
                    $this->request->setParam($brandAttributeCode, $this->request->getQueryValue($brandAttributeCode));
                    $this->request->setQueryValue($brandAttributeCode, null);
                }
                if ($urlKey = $this->brandHelper->getBrandUrlKey()) {
                    $routePath = $urlKey . '/' . $routePath;
                }

                if ($suffix = $this->getSuffix()) {
                    $routePath .= $suffix;
                }
                $routeParams['_direct'] = $routePath;
                $routeParams['_use_rewrite'] = true;
                $routePath = '';
            }
            return $this->urlBuilder->getUrl($routePath, $routeParams);
        }
        return null;
    }

    /**
     * @return null
     */
    public function getSuffix()
    {
        return null;
    }
}
