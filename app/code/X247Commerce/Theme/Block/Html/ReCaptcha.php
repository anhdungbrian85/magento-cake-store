<?php
namespace X247Commerce\Theme\Block\Html;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;
use Magento\ReCaptchaUi\Model\UiConfigResolverInterface;

class ReCaptcha extends \Magento\ReCaptchaUi\Block\ReCaptcha
{
    protected $jsLayout = [];

    public function __construct(
        Template\Context $context,
        UiConfigResolverInterface $captchaUiConfigResolver,
        IsCaptchaEnabledInterface $isCaptchaEnabled,
        Json $serializer,
        array $data = []
    ) {
        
        $data['jsLayout']['components']['recaptcha']['component'] = 'Magento_ReCaptchaFrontendUi/js/reCaptcha';
        parent::__construct($context, $captchaUiConfigResolver, $isCaptchaEnabled, $serializer, $data);
    }

}
