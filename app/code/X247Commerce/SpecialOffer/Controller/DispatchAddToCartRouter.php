<?php
/**
 * Router for Seo Module.
 *
 * @author    Zakaria KLIOUEL <zakli@smile.fr>
 * @copyright 2018 Smile
 */

namespace X247Commerce\SpecialOffer\Controller;

use Magento\Framework\App\Action\Redirect;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Controller\ResultFactory;
use X247Commerce\SpecialOffer\Helper\Data as Helper;

class DispatchAddToCartRouter implements RouterInterface
{
    protected Helper $helper;
    protected ActionFactory $actionFactory;
    protected ResponseInterface $response;

    public function __construct(
        Helper $helper,
        ActionFactory $actionFactory,
        ResponseInterface $response

    ) {
        $this->helper = $helper;
        $this->actionFactory = $actionFactory;
        $this->response = $response;
    }

    public function match(RequestInterface $request)
    {
        $enable = $this->helper->isEnable();
        $coupon = $this->helper->getSpecialCoupon();
        if (!$enable || !$coupon) {
            return null;
        }
        $path = $request->getOriginalPathInfo();
        $path = trim($path, '/');
        if (strtolower($coupon) == strtolower($path)) {
            $redirectUrl = '/cakeboxoffer/?c='.$path;
            $this->response->setRedirect($redirectUrl);
            return $this->actionFactory->create(Redirect::class);
        }
        return null;
    }

}
