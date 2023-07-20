<?php declare(strict_types = 1);
/**
 * @author    JaJuMa GmbH <info@jajuma.de>
 * @copyright Copyright (c) 2020 JaJuMa GmbH <https://www.jajuma.de>. All rights reserved.
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Jajuma\WebpImages\Block;

use Jajuma\WebpImages\Helper\Data as HelperModule;

class Picture extends \Magento\Framework\View\Element\Template
{
    protected $_template = "picture-tag-format.phtml";

    /**
     * @var string
     */
    private $webpImage = '';

    /**
     * @var string
     */
    private $originalImage = '';

    /**
     * @var string
     */
    private $originalTag = '';

    /**
     * @var string
     */
    private $customSrcTag = '';

    /**
     * @var string
     */
    private $customSrcSetTag = '';

    /**
     * Module helper data
     *
     * @var HelperModule
     */
    protected $helper;

    /**
     * Picture constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param HelperModule $helperData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        HelperModule $helperData,
        array $data = []
    ) {
        $this->helper = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * Seti original image
     *
     * @param string $originalImage
     *
     * @return Picture
     */
    public function setOriginalImage($originalImage)
    {
        $this->originalImage = $originalImage;
        return $this;
    }

    /**
     * Get original image
     *
     * @return string
     */
    public function getOriginalImage()
    {
        return $this->originalImage;
    }

    /**
     * Set Webp Image
     *
     * @param string $webpImage
     *
     * @return Picture
     */
    public function setWebpImage($webpImage)
    {
        $this->webpImage = $webpImage;
        return $this;
    }

    /**
     * Get Webp Image
     *
     * @return string
     */
    public function getWebpImage()
    {
        return $this->webpImage;
    }

    /**
     * Set Original Tag
     *
     * @param string $originalTag
     *
     * @return Picture
     */
    public function setOriginalTag($originalTag)
    {
        $this->originalTag = $originalTag;
        return $this;
    }

    /**
     * Get Original Tag
     *
     * @return string
     */
    public function getOriginalTag()
    {
        return $this->originalTag;
    }

    /**
     * Set custom src tag
     *
     * @param string $customSrcTag
     *
     * @return $this
     */
    public function setCustomSrcTag($customSrcTag)
    {
        $this->customSrcTag = $customSrcTag;
        return $this;
    }

    /**
     * Get Custom Src Tag
     *
     * @return string
     */
    public function getCustomSrcTag()
    {
        return $this->customSrcTag;
    }

    /**
     * Set Custom Src Set Tag
     *
     * @param mixed $customSrcSetTag
     *
     * @return $this
     */
    public function setCustomSrcSetTag($customSrcSetTag)
    {
        $this->customSrcSetTag = $customSrcSetTag;
        return $this;
    }

    /**
     * Get Custom Src Set Tag
     *
     * @return mixed
     */
    public function getCustomSrcSetTag()
    {
        return $this->customSrcSetTag;
    }

    /**
     * Get Original Image Type
     *
     * @return string
     */
    public function getOriginalImageType()
    {
        if (preg_match('/\.(jpg|jpeg)$/i', $this->getOriginalImage())) {
            return 'image/jpg';
        }

        if (preg_match('/\.(png)$/i', $this->getOriginalImage())) {
            return 'image/png';
        }

        return '';
    }

    /**
     * Is Native Lazy Loading Enabled
     *
     * @return bool
     */
    public function isNativeLazyLoadingEnabled()
    {
        return $this->helper->isNativeLazyLoadingEnabled();
    }

    /**
     * Get Exclude Native Lazy load Image Attributes
     *
     * @return mixed
     */
    public function getExcludeNativeLazyloadImageAttributes()
    {
        return $this->helper->getExcludeNativeLazyloadImageAttributes();
    }
}
