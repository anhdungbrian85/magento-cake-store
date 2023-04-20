<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
        
class Categorylist implements ArrayInterface
{
    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @param CategoryFactory $categoryFactory
     * @param CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        CollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Retrieve category collection
     *
     * @param bool $isActive
     * @param bool $level
     * @param bool $sortBy
     * @param bool $pageSize
     * @return array
     */
    public function getCategoryCollection(
        $isActive = true,
        $level = false,
        $sortBy = false,
        $pageSize = false
    ) {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
    }
    
    /**
     * Retrieve category options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $array = $this->_toArray();
        $result = [];

        foreach ($array as $key => $value) {
            $result[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $result;
    }
    
    /**
     * Get parent name
     *
     * @param string $path
     * @return string
     */
    private function _getParentName($path = '')
    {
        $parentName = '';
        $rootCats = [1,2];

        $categoryTree = explode("/", $path);
        
        // Deleting category itself
        array_pop($categoryTree);

        if ($categoryTree && (count($categoryTree) > count($rootCats))) {
            foreach ($categoryTree as $catId) {
                if (!in_array($catId, $rootCats)) {
                    $category = $this->categoryFactory->create()
                        ->load($catId);
                    $categoryName = $category->getName();
                    $parentName .= $categoryName . ' -> ';
                }
            }
        }

        return $parentName;
    }

    /**
     * Return options
     *
     * @return array
     */
    private function _toArray()
    {
        $catagoryList = [];
        
        $categories = $this->getCategoryCollection(true, false, false, false);
        foreach ($categories as $category) {
            $name = $this->_getParentName($category->getPath()) . $category->getName();
            $catagoryList[$category->getEntityId()] = __($name);
        }

        return $catagoryList;
    }
}
