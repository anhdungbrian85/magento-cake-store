<?php declare(strict_types = 1);
/**
 * @author    JaJuMa GmbH <info@jajuma.de>
 * @copyright Copyright (c) 2020 JaJuMa GmbH <https://www.jajuma.de>. All rights reserved.
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Jajuma\WebpImages\Plugin\Product\View;

use Jajuma\WebpImages\Helper\Data;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class GalleryPlugin
{
    /**
     * Module helper data
     *
     * @var Data
     */
    protected $helperWebp;

    /**
     * Json Helper
     *
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * Constructor
     *
     * @param Data $webpImagesHelper
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        Data $webpImagesHelper,
        JsonHelper $jsonHelper
    ) {
        $this->helperWebp = $webpImagesHelper;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * After Get Gallery Images Json
     *
     * @param \Magento\Catalog\Block\Product\View\Gallery $subject
     * @param mixed $result
     */
    public function afterGetGalleryImagesJson(\Magento\Catalog\Block\Product\View\Gallery $subject, $result)
    {

        $newImagesItems = [];
        $imagesItems = $this->jsonHelper->jsonDecode($result);
        foreach ($imagesItems as $itemImage) {
            $thumbImage = $itemImage['thumb'];
            $imgImage = $itemImage['img'];
            $fullImage = $itemImage['full'];
            $webpThumbImageUrl = $this->helperWebp->convert($thumbImage);
            $itemImage['thumb_webp'] = $webpThumbImageUrl;
            $webpImgImageUrl = $this->helperWebp->convert($imgImage);
            $itemImage['img_webp'] = $webpImgImageUrl;
            $webpFullImageUrl = $this->helperWebp->convert($fullImage);
            $itemImage['full_webp'] = $webpFullImageUrl;
            $newImagesItems[] = $itemImage;
        }

        return $this->jsonHelper->jsonEncode($newImagesItems);
    }
}
