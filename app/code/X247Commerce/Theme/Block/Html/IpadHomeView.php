<?php

namespace X247Commerce\Theme\Block\Html;

use \Magento\Catalog\Block\Category\View;

class IpadHomeView extends View implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_categoryHelper;


    protected $_storeManager;


    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Registry $registry,
        \X247Commerce\Theme\Helper\CategoryHelper $categoryHelper,
        array $data = []
    ) {
        parent::__construct($context, $layerResolver, $registry, $categoryHelper, $data);
        $this->_categoryHelper = $categoryHelper;
        $this->_catalogLayer = $layerResolver->get();
        $this->_coreRegistry = $registry;
    }

    protected function _prepareLayout()
    {
        $idCateIpad = $this->getIpadCate();
        $this->setCurrentCategory($idCateIpad);
        parent::_prepareLayout();

        $category = $this->getCurrentCategory();
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb(
                'category'.$category->getId(),
                [
                    'label' => __($category->getName()),
                ]
            );
        }
        return $this;
    }

    public function getCurrentCategory()
    {
        return $this->_catalogLayer->getCurrentCategory();
    }

    public function setCurrentCategory($category)
    {
       return $this->_catalogLayer->setCurrentCategory($category);
    }

    public function getIpadCate()
    {
       return $this->_categoryHelper->getCateIDIpadHomeView();
    }


}