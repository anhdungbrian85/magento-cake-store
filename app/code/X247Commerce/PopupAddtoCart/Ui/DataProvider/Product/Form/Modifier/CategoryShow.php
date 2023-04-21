<?php
namespace X247Commerce\PopupAddtoCart\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Framework\Stdlib\ArrayManager;

class CategoryShow extends AbstractModifier
{
	const FIELD_IS_DELETE = 'is_delete';
	const FIELD_SORT_ORDER_NAME = 'sort_order';
	const FIELD_NAME_SELECT = 'select_field';

	public $arrayManager;
	private $locator;
	private $optionCategory;

	public function __construct(
		LocatorInterface $locator,
		\X247Commerce\Catalog\Model\Config\Product\CategoryArray $optionCategory,
		ArrayManager $arrayManager
	) {
		$this->locator = $locator;
		$this->optionCategory = $optionCategory;
		$this->arrayManager = $arrayManager;
	}

	public function modifyData(array $data)
	{
		if ( !empty($data) > 0 && array_key_exists('category_show_in_popup_crossell', $data[key($data)]["product"]) && $data[key($data)]["product"]["category_show_in_popup_crossell"] != "") {
			$data[key($data)]["product"]["category_show"] = json_decode($data[key($data)]["product"]["category_show_in_popup_crossell"]);
		}

		return $data;
	}

	public function modifyMeta(array $meta)
	{
		$path = $this->arrayManager->findPath('category_show_in_popup_crossell', $meta, null, 'children');
		$meta = $this->arrayManager->set(
			"{$path}/arguments/data/config/visible",
			$meta,
			false
		);

		$meta = array_replace_recursive(
	    	$meta,
	    	[
	        'category_show' => [
	            'arguments' => [
	                'data' => [
	                    'config' => [
	                        'label' => __('Category show in Popup Crossell'),
	                        'componentType' => Fieldset::NAME,
	                        'dataScope' => 'data.product.category_show',
	                        'collapsible' => true,
	                        'sortOrder' => 5,
	                    ],
	                ],
	            ],
	            'children' => [
					"custom_field" => $this->getSelectTypeGridConfig(10)
				],
		    ]
		]);

		return $meta;
	}

	protected function getSelectTypeGridConfig($sortOrder) 
	{
		return [
			'arguments' => [
			    'data' => [
			        'config' => [
			            'addButtonLabel' => __('Add Category'),
			            'componentType' => DynamicRows::NAME,
			            'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
			            'additionalClasses' => 'admin__field-wide',
			            'deleteProperty' => static::FIELD_IS_DELETE,
			            'deleteValue' => '1',
			            'renderDefaultRecord' => false,
			            'sortOrder' => $sortOrder,
			        ],
			    ],
			],
			'children' => [
			    'record' => [
			        'arguments' => [
			            'data' => [
			                'config' => [
			                    'componentType' => Container::NAME,
			                    'component' => 'Magento_Ui/js/dynamic-rows/record',
			                    'positionProvider' => static::FIELD_SORT_ORDER_NAME,
			                    'isTemplate' => true,
			                    'is_collection' => true,
			                ],
			            ],
			        ],
			        'children' => [
			            static::FIELD_NAME_SELECT => $this->getSelectFieldConfig(1),
			            static::FIELD_IS_DELETE => $this->getIsDeleteFieldConfig(3)
			            //Add as many fields as you want
			        ]
			    ]
			]
		];
	}

	protected function getSelectFieldConfig($sortOrder)
	{

		return [
		    'arguments' => [
		        'data' => [
		            'config' => [
		                'label' => __('List Category'),
		                'componentType' => Field::NAME,
		                'formElement' => Select::NAME,
		                'dataScope' => static::FIELD_NAME_SELECT,
		                'dataType' => Text::NAME,
		                'sortOrder' => $sortOrder,
		                'options' => $this->_getOptions(),
		                'visible' => true,
		                'disabled' => false,
		            ],
		        ],
		    ],
		];
	}

	protected function _getOptions()
	{
		return $this->optionCategory->getAllOptions();
	}

	protected function getIsDeleteFieldConfig($sortOrder)
	{
		return [
		    'arguments' => [
		        'data' => [
		            'config' => [
		                'componentType' => ActionDelete::NAME,
		                'fit' => true,
		                'sortOrder' => $sortOrder
		            ],
		        ],
		    ],
		];
	}
}