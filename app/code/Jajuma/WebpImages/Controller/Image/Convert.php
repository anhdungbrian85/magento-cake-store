<?php declare(strict_types = 1);
/**
 * @author    JaJuMa GmbH <info@jajuma.de>
 * @copyright Copyright (c) 2020 JaJuMa GmbH <https://www.jajuma.de>. All rights reserved.
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Jajuma\WebpImages\Controller\Image;

use function GuzzleHttp\json_encode;
use Magento\Framework\App\Action\Context;
use Jajuma\WebpImages\Helper\Data;
use Magento\Framework\Controller\Result\JsonFactory;

class Convert extends \Magento\Framework\App\Action\Action
{
    /**
     * Context
     *
     * @var Context
     */
    protected $context;

    /**
     * Module helper Data
     *
     * @var Data
     */
    protected $helper;

    /**
     * JsonFactory
     *
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Data $helper
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Data $helper,
        JsonFactory $resultJsonFactory
    ) {
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return JsonFactory
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $images = $this->getRequest()->getParam('images');
        $isInProductView = $this->getRequest()->getParam('isInProductView');
        if ($images) {
            $webpUrls = [];
            foreach ($images as $imageUrl) {
                if (array_key_exists('thumb', $imageUrl)) {
                    $webpUrlThumb = $this->helper->convert($imageUrl['thumb']);
                    if ($webpUrlThumb) {
                        $imageUrl['thumb'] = $webpUrlThumb;
                    }
                }

                if (array_key_exists('img', $imageUrl)) {
                    $webpUrlImg = $this->helper->convert($imageUrl['img']);
                    if ($webpUrlImg) {
                        $imageUrl['img'] = $webpUrlImg;
                    }
                }

                if (array_key_exists('full', $imageUrl)) {
                    $webpUrlFull = $this->helper->convert($imageUrl['full']);
                    if ($webpUrlFull) {
                        $imageUrl['full'] = $webpUrlFull;
                    }
                }

                if (!empty($imageUrl)) {
                    array_push($webpUrls, $imageUrl);
                }
            }
        }

        return $resultJson->setData(['webpUrls' => $webpUrls]);
    }
}
