<?php

namespace X247Commerce\Nutritics\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ProductFactory;
use Psr\Log\LoggerInterface;
use X247Commerce\Nutritics\Service\NutriticsApi;
use X247Commerce\Nutritics\Model\ResourceModel\NutriticsValue\CollectionFactory;

class GetNutriticsInfo extends Action
{
    protected $resultFactory;
    protected $logger;
    protected $productRepository;
    protected $nutriticsApi;
    protected $nutriticsValueCollection;
    protected ProductFactory $productFactory;

    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        LoggerInterface $logger,
        ProductRepository $productRepository,
        NutriticsApi $nutriticsApi,
        CollectionFactory $nutriticsValueCollection,
        ProductFactory $productFactory
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->nutriticsApi = $nutriticsApi;
        $this->nutriticsValueCollection = $nutriticsValueCollection;
        $this->productFactory = $productFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $productId = $this->getRequest()->getParam('selectedProductId');
        $product = $this->productFactory->create()->load($productId);

        if ($productId) {
            $nutriticsInfo = $this->nutriticsValueCollection->create()->addFieldToSelect('*')->addFieldToFilter('row_id', $product->getRowId());            
            $resultData = [];
            if ($nutriticsInfo) {
                $nutriticsInfoHtml = $this->getNutriticsInfoHtml($nutriticsInfo->getData());
                $ingredientsAllergensHtml = $this->getIngredientsAllergensHtml($nutriticsInfo->getData());
                
                $resultData = ['nutriticsHtml' => $nutriticsInfoHtml, 'ingredientsAllergensHtml' => $ingredientsAllergensHtml];
            }

            return $result->setData(json_encode($resultData));
        }
    }

    public function getNutriticsInfoHtml($nutriticsInfo)
    {
        $tmp = array_keys(array_column($nutriticsInfo, 'attribute_name'), 'Energy');        
        $cnt = count($tmp);
        $nutritics = ['Energy Kcal', 'Energy Kj', 'Energy', 'Carbohydrate', 'Sugars', 'Fat', 'Saturated Fat', 'Protein', 'Fibre', 'Sodium (Na)'];
        $html = "<div class='nutritics-info-wraper'>        
                    <table class='table-nutritics-info'>
                    <tr>
                        <th>".__('Nutritionals')."</th>
                        <th>".__('Value/100g')."</th>
                        <th>".__('% RI')."</th>
                    </tr>";
       foreach ($nutriticsInfo as $info) 
       {
            if ($info['value'] && $info['attribute_code'] != 'allergens' && $info['attribute_code'] != 'quid' && (array_search($info['attribute_name'], $nutritics) !== false))
            {
                if ($cnt > 1 && $info['attribute_unit'] == 'kJ') {
                    $attribute_name = '';
                } else {
                    $attribute_name = $info['attribute_name'];
                }
                $value = round($info['value'], 1);
                $html .= "<tr class='nutritics-info-detail'>
                            <td class='attribute_name'>". $attribute_name ."</td>
                            <td class='value'>". $value . $info['attribute_unit'] ."</td>
                            <td class='percent_ri'>". $info['percent_ri'] ."</td>
                        </tr>"  ;                      
            }
        }

        $html .= "</table><small class='notice'>*The average adult needs 2000 kcals per day</small></div>";
        return $html;
    }

    public function getIngredientsAllergensHtml($nutriticsInfo)
    {
        $htmlAll = "<div class='ingredient-allergen-info-wraper'>
                    <div class='ingredient-allergen-info'>";
        $html = '';
        $htmlIngredient = '';

       foreach ($nutriticsInfo as $info) 
       {
            if ($info['attribute_code'] == 'allergens')
            {

                $allergens = json_decode($info['value'], true);
                $html .= "<div class='allergens-info-detail'>";
                        if ($allergens['contains'])
                        {
                            $html .= "<p>
                                        <span class='allergens-title'>". __('This product contains the following allergens') .": </span>
                                        <span class='allergens-detail contains'>". implode(', ', $allergens['contains']) ."</span>
                                    </p>";
                        }
                        if ($allergens['maycontain'])
                        {
                            $html .= "<p>
                                        <span class='allergens-title'>". __('This product may contains the following allergens') .": </span>
                                        <span class='allergens-detail maycontain'>". implode(', ', $allergens['maycontain']) ."</span>
                                    </p>";
                        }
                        if ($allergens['freefrom'])
                        {
                            $html .= "<p>
                                        <span class='allergens-title'>". __('This product free from the following allergens') .": </span>
                                        <span class='allergens-detail freefrom'>". implode(', ', $allergens['freefrom']) ."</span>
                                    </p>";
                        }
                        if ($allergens['suitablefor'])
                        {
                            $html .= "<p>
                                        <span class='allergens-title'>". __('This product suitablefor the following') .": </span>
                                        <span class='allergens-detail suitablefor'>". implode(', ', $allergens['suitablefor']) ."</span>
                                    </p>";
                        };
                $html .= "</div>";
            }

            if ($info['attribute_code'] == 'quid')
                {
                    $htmlIngredient .= "<div class='ingredient-info-detail'><span class='ingredients-title'>Ingredients: </span>".$info['value']."</div>"  ;                      
                }
        }
        $htmlAll .= $html.$htmlIngredient;
        $htmlAll .= "</div></div>";
        return $htmlAll;
    }
}
