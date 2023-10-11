<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace X247Commerce\StoreLocator\Model\ResourceModel;

use Amasty\Storelocator\Model\DataCollector\Location\CompositeCollector;
use Amasty\Storelocator\Model\GalleryFactory;
use Amasty\Storelocator\Model\ImageProcessor;
use Amasty\Storelocator\Model\ResourceModel\Gallery;
use Amasty\Storelocator\Model\ResourceModel\Gallery\Collection as GalleryCollection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Amasty\Storelocator\Model\ResourceModel\Attribute;
use Amasty\Storelocator\Model\ResourceModel\Options;

class Location extends \Amasty\Storelocator\Model\ResourceModel\Location
{

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var GalleryCollection
     */
    private $galleryCollection;

    /**
     * @var Gallery
     */
    private $galleryResource;

    /**
     * @var GalleryFactory
     */
    private $galleryFactory;

    /**
     * @var CompositeCollector|null
     */
    private $compositeCollector;

    private $storeManager;

    protected $serializer;

    public function __construct(
        Context $context,
        ImageProcessor $imageProcessor,
        GalleryCollection $galleryCollection,
        GalleryFactory $galleryFactory,
        Gallery $galleryResource,
        CompositeCollector $compositeCollector,
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        $connectionName = null
    ) {
        parent::__construct($context, $imageProcessor, $galleryCollection, $galleryFactory, $galleryResource, $compositeCollector, $connectionName);
        $this->imageProcessor = $imageProcessor;
        $this->galleryCollection = $galleryCollection;
        $this->galleryFactory = $galleryFactory;
        $this->galleryResource = $galleryResource;
        $this->compositeCollector = $compositeCollector;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Serialize\Serializer\Json::class
        );
    }
    
    public function setAttributesData(AbstractModel $object)
    {
        if ($object->getId()) {
            $connection = $this->getConnection();

            $select = $connection->select()
                ->from(
                    ['sa' => $this->getTable('amasty_amlocator_store_attribute')]
                )
                ->joinLeft(
                    ['attr' => $this->getTable(Attribute::TABLE_NAME)],
                    '(sa.attribute_id = attr.attribute_id)'
                )
                ->joinLeft(
                    ['attr_option' => $this->getTable(Options::TABLE_NAME)],
                    '(sa.attribute_id = attr_option.attribute_id)',
                    [
                        'options_serialized' => 'attr_option.options_serialized',
                        'value_id'           => 'attr_option.value_id'
                    ]
                )
                ->where(
                    'store_id = ?',
                    (int)$object->getId()
                )
                ->where(
                    'value <> ""'
                )
                ->where(
                    'attr.frontend_input IN (?)',
                    ['boolean', 'select', 'multiselect', 'text']
                );

            $attributes = $connection->fetchAll($select);

            $preparedAttributes = $this->prepareAttributes($attributes);

            $object->setData('attributes', $preparedAttributes);
        }

        return $object;
    }

    private function prepareAttributes($attributes)
    {
        $result = [];
        
        $storeId = $this->storeManager->getStore(true)->getId();

        foreach ($attributes as $key => $attribute) {
            if (!array_key_exists($attribute['attribute_code'], $result)) {
                $result[$attribute['attribute_code']] = $attribute;
                if($attribute['label_serialized'])
                {
                    $labels = $this->serializer->unserialize($attribute['label_serialized']);
                }else{
                    $labels = '';
                }
                if (!empty($labels[$storeId])) {
                    $result[$attribute['attribute_code']]['frontend_label'] = $labels[$storeId];
                }
            }
            if (isset($attribute['options_serialized']) && $attribute['options_serialized']) {
                $values = explode(',', $attribute['value']);
                if (in_array($attribute['value_id'], $values)) {
                    $options = $this->serializer->unserialize($attribute['options_serialized']);
                    $optionTitle = '';
                    if (!empty($options[$storeId])) {
                        $optionTitle = $options[$storeId];
                    } elseif (isset($options[0])) {
                        $optionTitle = $options[0];
                    }

                    $result[$attribute['attribute_code']]['option_title'][] = $optionTitle;
                }
            }
            if ($attribute['frontend_input'] == 'boolean') {
                if ((int)$attribute['value'] == 1) {
                    $result[$attribute['attribute_code']]['option_title'] = __('Yes')->getText();
                } else {
                    $result[$attribute['attribute_code']]['option_title'] = __('No')->getText();
                }
            }

            if ($attribute['frontend_input'] == 'text') {
                $result[$attribute['attribute_code']]['option_title'] = $attribute['value'];
            }

        }
        
        return $result;
    }

}