<?php
namespace X247Commerce\PopupAddtoCart\Block\AddtoCart;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Render;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Url\Helper\Data;

class Popup extends Template
{

	protected $productRepository;

	protected $imageBuilder;

	protected $urlHelper;

	protected $optionsData;

	protected $cartHelper;

	public function __construct(
		Template\Context $context,
		\Magento\Catalog\Model\ProductRepository $productRepository,
		\Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
		\Magento\Framework\Url\Helper\Data $urlHelper,
		\Magento\Catalog\ViewModel\Product\OptionsData $optionsData,
		\Magento\Checkout\Helper\Cart $cartHelper,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->productRepository = $productRepository;
		$this->imageBuilder = $imageBuilder;
		$this->urlHelper = $urlHelper;
		$this->optionsData = $optionsData;
		$this->cartHelper = $cartHelper;
	}

	public function getProduct()
	{
		$data = $this->getData();
		return $this->productRepository->getById($data['productId']);
	}

	public function getViewModel()
	{
		return $this->optionsData;
	}


	/**
	 * Get product price.
	 *
	 * @param Product $product
	 * @return string
	 */
	public function getProductPrice(Product $product)
	{
		$priceRender = $this->getPriceRender();

		$price = '';
		if ($priceRender) {
			$price = $priceRender->render(
				FinalPrice::PRICE_CODE,
				$product,
				[
					'include_container' => true,
					'display_minimal_price' => true,
					'zone' => Render::ZONE_ITEM_LIST,
					'list_category_page' => true
				]
			);
		}

		return $price;
	}

	/**
	 * Specifies that price rendering should be done for the list of products.
	 * (rendering happens in the scope of product list, but not single product)
	 *
	 * @return Render
	 */
	protected function getPriceRender()
	{
		return $this->getLayout()->getBlock('product.price.render.default')
			->setData('is_product_list', true);
	}

	/**
	 * Retrieve product image
	 *
	 * @param \Magento\Catalog\Model\Product $product
	 * @param string $imageId
	 * @param array $attributes
	 * @return \Magento\Catalog\Block\Product\Image
	 */
	public function getImage($product, $imageId, $attributes = [])
	{
		return $this->imageBuilder->create($product, $imageId, $attributes);
	}

	/**
	 * Get post parameters
	 *
	 * @param Product $product
	 * @return array
	 */
	public function getAddToCartPostParams(Product $product)
	{
		$url = $this->getAddToCartUrl($product, ['_escape' => false]);
		return [
			'action' => $url,
			'data' => [
				'product' => (int) $product->getEntityId(),
				ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl($url),
			]
		];
	}

	/**
	 * Retrieve url for add product to cart
	 *
	 * Will return product view page URL if product has required options
	 *
	 * @param \Magento\Catalog\Model\Product $product
	 * @param array $additional
	 * @return string
	 */
	public function getAddToCartUrl($product, $additional = [])
	{
		if (!$product->getTypeInstance()->isPossibleBuyFromList($product)) {
			if (!isset($additional['_escape'])) {
				$additional['_escape'] = true;
			}
			if (!isset($additional['_query'])) {
				$additional['_query'] = [];
			}
			$additional['_query']['options'] = 'cart';

			return $this->getProductUrl($product, $additional);
		}
		return $this->cartHelper->getAddUrl($product, $additional);
	}
}
