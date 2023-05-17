<?php
namespace X247Commerce\Theme\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class CategoryOptions implements \Magento\Framework\Option\ArrayInterface
{
    protected $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('is_active', 1);

        $options = [['label' => __('Please Select a Category'), 'value' => '']];

        foreach ($collection as $category) {
            $options[] = [
                'value' => $category->getId(),
                'label' => $category->getName(),
            ];
        }

        return $options;
    }
}

?>