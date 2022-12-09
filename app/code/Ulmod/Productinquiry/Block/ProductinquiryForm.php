<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Block;

use Magento\Framework\View\Element\Template;
use Ulmod\Productinquiry\Api\Data\DataInterface;
use Ulmod\Productinquiry\Model\Data as ProductinquiryModel;
use Ulmod\Productinquiry\Model\ResourceModel\Data\Collection as ProductinquiryCollection;
use Magento\Framework\View\Element\Template\Context;
use Ulmod\Productinquiry\Model\ResourceModel\Data\CollectionFactory as ProductinquiryCollectionFactory;
use Ulmod\Productinquiry\Model\ConfigForm;
use Ulmod\Productinquiry\Model\ConfigData;
use Magento\Framework\Registry;
use Magento\Catalog\Helper\Image as HelperImage;
use Magento\Catalog\Api\ProductRepositoryInterface;
       
class ProductinquiryForm extends Template
{
    /**
     * @var ConfigForm
     */
    public $configForm;

    /**
     * @var ConfigData
     */
    public $configData;

    /**
     * @var Registry
     */
    protected $registry;
    
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var HelperImage
     */
    protected $imageHelper;
    
    /**
     * @param Context $context
     * @param ProductinquiryCollectionFactory $productinquiryCollectionFactory
     * @param ConfigForm $configForm
     * @param ConfigData $configData
     * @param Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param HelperImage $imageHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductinquiryCollectionFactory $productinquiryCollectionFactory,
        ConfigForm $configForm,
        ConfigData $configData,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        HelperImage $imageHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_productinquiryCollectionFactory = $productinquiryCollectionFactory;
        $this->configForm = $configForm;
        $this->configData = $configData;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Get Config Data Model
     *
     * @return ConfigData
     */
    public function getConfigData()
    {
        return $this->configData;
    }
    
    /**
     * Get current page url
     *
     * @return string
     */
    public function getCurrentPageUrl()
    {
        return $this->configForm->getCurrentUrl();
    }
    
    /**
     * Get product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }
    
    /**
     * Get form action
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('productinquiry/index/save');
    }
    
    /**
     * Get list action
     *
     * @return string
     */
    public function getListAction()
    {
        return $this->getUrl('productinquiry');
    }
    
    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [ProductinquiryModel::CACHE_TAG . '_' . 'form'];
    }

    /**
     * Get user email
     *
     * @return string
     */
    public function getUserEmail()
    {
        return $this->configForm->getUserEmail();
    }
    
    /**
     * Get user name
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->configForm->getUserName();
    }
    
    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->configForm->getSubject();
    }

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->configForm->getTelephone();
    }
    
    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->configForm->getMessage();
    }

    /**
     * Get extra field one
     *
     * @return string
     */
    public function getExtraFieldOne()
    {
        return $this->configForm->getExtraFieldOne();
    }

    /**
     * Get extra field two
     *
     * @return string
     */
    public function getExtraFieldTwo()
    {
        return $this->configForm->getExtraFieldTwo();
    }

    /**
     * Get extra field three
     *
     * @return string
     */
    public function getExtraFieldThree()
    {
        return $this->configForm->getExtraFieldThree();
    }

    /**
     * Get extra field four
     *
     * @return string
     */
    public function getExtraFieldFour()
    {
        return $this->configForm->getExtraFieldFour();
    }

    /**
     * Get form type config value
     *
     * @return string
     */
    public function getFormType()
    {
        return $this->configData->getFormType();
    }

    /**
     * Get layout form config value
     *
     * @return string
     */
    public function getFormLayout()
    {
        return $this->configData->getFormLayout();
    }
    
    /**
     * Get form title config value for in new page
     *
     * @return string
     */
    public function getFormTitleNewpage()
    {
        return $this->configData->getFormTitleNewpage();
    }

    /**
     * Get form title config value for in popup
     *
     * @return string
     */
    public function getFormTitle()
    {
        return $this->configData->getFormTitle();
    }

    /**
     * Is show product image
     *
     * @return int
     */
    public function isShowProductImage()
    {
        return $this->configData->isShowProductImage();
    }

    /**
     * Is show product name
     *
     * @return int
     */
    public function isShowProductName()
    {
        return $this->configData->isShowProductName();
    }

    /**
     * Is show product sku
     *
     * @return int
     */
    public function isShowProductSku()
    {
        return $this->configData->isShowProductSku();
    }

    /**
     * Check if telephone field is enabled
     *
     * @return int
     */
    public function isPhoneEnabled()
    {
        return $this->configData->isPhoneEnabled();
    }

    /**
     * Check if attachment is enabled
     *
     * @return int
     */
    public function isAttachmentEnabled()
    {
        return $this->configData->isAttachmentEnabled();
    }
  
    /**
     * Check if subject is enabled
     *
     * @return int
     */
    public function isSubjectEnabled()
    {
        return $this->configData->isSubjectEnabled();
    }

    /**
     * Check if extra field one is enabled
     *
     * @return int
     */
    public function isExtraFieldOneEnabled()
    {
        return $this->configData->isExtraFieldOneEnabled();
    }
    
    /**
     * Get extra field one type
     *
     * @return string
     */
    public function getExtraFieldOneType()
    {
        return $this->configData->getExtraFieldOneType();
    }

    /**
     * Get extra field one label
     *
     * @return string
     */
    public function getExtraFieldOneLabel()
    {
        return $this->configData->getExtraFieldOneLabel();
    }

    /**
     * Get extra field one sort order
     *
     * @return int
     */
    public function getExtraFieldOneSortorder()
    {
        return $this->configData->getExtraFieldOneSortorder();
    }

     /**
      * Check if extra field Two is enabled
      *
      * @return int
      */
    public function isExtraFieldTwoEnabled()
    {
        return $this->configData->isExtraFieldTwoEnabled();
    }
    
    /**
     * Get extra field Two type
     *
     * @return string
     */
    public function getExtraFieldTwoType()
    {
        return $this->configData->getExtraFieldTwoType();
    }

    /**
     * Get extra field Two label
     *
     * @return string
     */
    public function getExtraFieldTwoLabel()
    {
        return $this->configData->getExtraFieldTwoLabel();
    }

    /**
     * Get extra field Two sort order
     *
     * @return int
     */
    public function getExtraFieldTwoSortorder()
    {
        return $this->configData->getExtraFieldTwoSortorder();
    }

    /**
     * Check if extra field Three is enabled
     *
     * @return int
     */
    public function isExtraFieldThreeEnabled()
    {
        return $this->configData->isExtraFieldThreeEnabled();
    }
    
    /**
     * Get extra field Three type
     *
     * @return string
     */
    public function getExtraFieldThreeType()
    {
        return $this->configData->getExtraFieldThreeType();
    }

    /**
     * Get extra field Three label
     *
     * @return string
     */
    public function getExtraFieldThreeLabel()
    {
        return $this->configData->getExtraFieldThreeLabel();
    }

    /**
     * Get extra field Three sort order
     *
     * @return int
     */
    public function getExtraFieldThreeSortorder()
    {
        return $this->configData->getExtraFieldThreeSortorder();
    }

    /**
     * Check if extra field Four is enabled
     *
     * @return int
     */
    public function isExtraFieldFourEnabled()
    {
        return $this->configData->isExtraFieldFourEnabled();
    }
    
    /**
     * Get extra field Four type
     *
     * @return string
     */
    public function getExtraFieldFourType()
    {
        return $this->configData->getExtraFieldFourType();
    }

    /**
     * Get extra field Four label
     *
     * @return string
     */
    public function getExtraFieldFourLabel()
    {
        return $this->configData->getExtraFieldFourLabel();
    }

    /**
     * Get extra field Four sort order
     *
     * @return int
     */
    public function getExtraFieldFourSortorder()
    {
        return $this->configData->getExtraFieldFourSortorder();
    }
       
    /**
     * Check if recaptcha is enabled
     *
     * @return bool
     */
    public function isRecaptchaV2CheckboxEnabled()
    {
        return $this->configData->isRecaptchaV2CheckboxEnabled();
    }

    /**
     * Check if Magento CAPTCHA is enabled
     *
     * @return bool
     */
    public function isMagentoCaptchaEnabled()
    {
        return $this->configData->isMagentoCaptchaEnabled();
    }

    /**
     * Check recaptcha v2 script url
     *
     * @return string
     */
    public function getRecaptchaCheckboxV2ScriptsUrl()
    {
        return $this->configData->getRecaptchaCheckboxV2ScriptsUrl();
    }

    /**
     * Check if honeypot enabled
     *
     * @return bool
     */
    public function isHoneypotEnabled()
    {
        return $this->configData->isHoneypotEnabled();
    }
    
    /**
     * Get recaptcha form secret key config value
     *
     * @return string
     */
    public function getRecaptchaSecretKey()
    {
        return $this->configData->getRecaptchaSecretKey();
    }
    
    /**
     * Get recaptcha form site key config value
     *
     * @return string
     */
    public function getRecaptchaSiteKey()
    {
        return $this->configData->getRecaptchaSiteKey();
    }

    /**
     * Get sent message config value
     *
     * @return string
     */
    public function getSentMessage()
    {
        return $this->configData->getSentMessage();
    }

    /**
     * Return hidden new page form class
     *
     * @return string
     */
    public function getHiddenNewPageFormClass()
    {
        $cssClass = '';
        if ($this->getFormType() == 'new_page') {
            $cssClass = 'um-prodinq-hide-popup-form';
        }
        return $cssClass;
    }

    /**
     * Return hidden new page form class
     *
     * @return string
     */
    public function getFormClassSuffix()
    {
        $cssClass = 'popup';
        if ($this->getFormType() == 'new_page') {
            $cssClass = 'new-page';
        }
        return $cssClass;
    }

    /**
     * Check if popup form
     *
     * @return bool
     */
    public function isPopupForm()
    {
        if ($this->getFormType() == 'popup') {
            return true;
        }
        return false;
    }

    /**
     * Check if new page form
     *
     * @return bool
     */
    public function isNewPageForm()
    {
        if ($this->getFormType() == 'new_page') {
            return true;
        }
        return false;
    }

    /**
     * Get product by id
     *
     * @param int $productId
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductById($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * Get product id
     *
     * @return int
     */
    public function getProductId()
    {
        $productId = '';
        if ($this->isNewPageForm()) {
            $productId = $this->getRequest()->getParam('id');
        }
        return $productId;
    }
    
    /**
     * Get product image url
     *
     * @return string
     */
    public function getProductImageUrl()
    {
        $imageUrl = '';
        if ($this->isNewPageForm()) {
            try {
                $productId = $this->getProductId();
                $product = $this->getProductById($productId);
            } catch (NoSuchEntityException $e) {
                return 'product not found';
            }
            $imageUrl = $this->imageHelper->init($product, 'product_thumbnail_image')
                ->getUrl();
        }
        return $imageUrl;
    }

    /**
     * Get product name
     *
     * @return string
     */
    public function getProductName()
    {
        $name = '';
        if ($this->isNewPageForm()) {
            $productId = $this->getProductId();
            $product = $this->getProductById($productId);
            $name = $product->getName();
        }
        return $name;
    }

    /**
     * Get product sku
     *
     * @return string
     */
    public function getProductSku()
    {
        $sku = '';
        if ($this->isNewPageForm()) {
            $productId = $this->getProductId();
            $product = $this->getProductById($productId);
            $sku = $product->getSku();
        }
        return $sku;
    }
}
