<?php
namespace Bss\ConfigurableProductWholesaleCustomize\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

class Limit implements ObserverInterface
{
    protected $cart;

    protected $registry;

    protected $request;

    protected $productRepository;

    protected $helperprice;

    protected $categoryFactory;

    protected $messageManager;

    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Pricing\Helper\Data $helperprice,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->cart = $cart;
        $this->registry = $registry;
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->helperprice = $helperprice;
        $this->categoryFactory = $categoryFactory;
        $this->messageManager = $messageManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $cartItems = $this->cart->getQuote()->getAllVisibleItems();
        $total_qty = [];
        $haserror = false;
        foreach ($cartItems as $item){
            $product = $this->productRepository->getById($item->getProductId());
            if ($this->isOrderSample($product,$item)) {
                if (isset($total_qty[$item->getProductId()])) {
                    $total_qty[$item->getProductId()] += (int)$item->getQty();
                } else {
                    $total_qty[$item->getProductId()]  = $item->getQty();
                }
            }
        }

        if (!empty($total_qty)) {
            foreach ($total_qty as $productId => $qty) {
                $_product = $this->productRepository->getById($productId);
                if ($_product->getQtyOrderSample() && $qty > $_product->getQtyOrderSample()) {
                    $message = __('The maximum sample quantity can be ordered for %1 is %2',$_product->getName(),$_product->getQtyOrderSample());
                    $this->messageManager->addError($message);
                    $haserror = true;
                }
            }
            if ($haserror) {
                $this->cart->getQuote()->setHasError(true);
            }
        }
    }

    /**
     * @param $product
     * @param $item
     * @return int
     */
    protected function isOrderSample($product, $item)
    {
        $options = $product->getOptions();
        foreach ($item->getBuyRequest()->getOptions() as $code => $option) {
            $customOptionItem[$code] = $option;
        }
        foreach ($options as $option) {
            if (!isset($customOptionItem[$option->getId()])) {
                continue;
            }
            if ($option->getType() === 'drop_down') {
                $values = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Catalog\Model\Product\Option\Value')->getValuesCollection($option);
                foreach ($values as $value) {
                    if ($value->getId() == $customOptionItem[$option->getId()] && $value->getTitle() == 'Order Sample') {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}