<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\SpecialOffer\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\SessionFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Checkout\Model\Cart;
use Magento\Quote\Api\CartRepositoryInterface;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use X247Commerce\SpecialOffer\Helper\Data as Helper;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResource;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;

/**
 * Class Index
 */
class Post extends Action implements HttpPostActionInterface
{
    protected $checkoutSession;
    protected $cartRepository;
    protected $productRepository;
    protected $resultFactory;
    protected $cart;
    protected $helper;
    protected $configurableResource;
    protected $productAttributeRepository;
    protected $storeLocationContextInterface;
    protected $resouceConnection;
    protected $locatorSourceResolver;
    protected $notAvailableStores = [];

    public function __construct(
        Context                    $context,
        SessionFactory             $checkoutSession,
        CartRepositoryInterface    $cartRepository,
        ProductRepositoryInterface $productRepository,
        ResultFactory              $resultFactory,
        Cart                       $cart,
        Helper                     $helper,
        ConfigurableResource       $configurableResource,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        StoreLocationContextInterface $storeLocationContextInterface,
        ResourceConnection $resourceConnection,
        LocatorSourceResolver $locatorSourceResolver
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->resultFactory = $resultFactory;
        $this->cart = $cart;
        $this->helper = $helper;
        $this->configurableResource = $configurableResource;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->storeLocationContextInterface = $storeLocationContextInterface;
        $this->resouceConnection = $resourceConnection;
        $this->locatorSourceResolver = $locatorSourceResolver;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl('/');
            return $resultRedirect;
    }

    /**
     * @param \Magento\Catalog\Model\Product $parent
     * @param \Magento\Catalog\Model\Product $child
     * @return array
     */
    private function getChildSuperAttribute($parent, $child)
    {
        $_attributes = $parent->getTypeInstance(true)->getConfigurableAttributes($parent);
        $attributesPair = [];
        foreach ($_attributes as $_attribute) {
            $attributeId = (int)$_attribute->getAttributeId();
            $attributeCode = $this->getAttributeCode($attributeId);
            $attributesPair[$attributeId] = (int)$child->getData($attributeCode);
        }
        return $attributesPair;
    }

    /**
     * Get attribute code by attribute id
     * @param int $id
     * @return string
     */
    private function getAttributeCode(int $id)
    {
        return $this->productAttributeRepository->get($id)->getAttributeCode();
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    private function setLocationContext($product)
    {
        if (!$this->storeLocationContextInterface->getStoreLocationId()) {
            $connection = $this->resouceConnection->getConnection();
            $amLocationTbl = $this->resouceConnection->getTableName('amasty_amlocator_location');
            $linkSourceTbl = $this->resouceConnection->getTableName('amasty_amlocator_location_source_link');
            $inventory = $this->resouceConnection->getTableName('inventory_source_item');

            $q = $connection->select()->from(['m' => $amLocationTbl], 'id')
                ->joinLeft(['l' => $linkSourceTbl], "l.location_id = m.id", [])
                ->joinLeft(['i' => $inventory], "i.source_code = l.source_code", [])
                ->where('m.status = 1')
                ->where('m.enable_delivery = 1')
                ->where('m.curbside_enabled = 1')
                ->where('i.sku = \''.$product->getSku().'\'')
                ->where('i.status = 1')
                ->limit(1);

            $firstActiveLocation = $connection->fetchOne($q);
            $this->storeLocationContextInterface->setStoreLocationId($firstActiveLocation);
        }

        if (!$this->storeLocationContextInterface->getDeliveryType()) {
            $this->storeLocationContextInterface->setDeliveryType(StoreLocationContextInterface::FREE_COLLECTION_TYPE_VALUE);
        }
    }
}
