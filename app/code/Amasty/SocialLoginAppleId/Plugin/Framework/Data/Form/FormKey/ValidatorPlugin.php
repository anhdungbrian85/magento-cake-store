<?php

namespace Amasty\SocialLoginAppleId\Plugin\Framework\Data\Form\FormKey;

class ValidatorPlugin
{
    /**
     * @param \Magento\Framework\Data\Form\FormKey\Validator $subject
     * @param \Closure $proceed
     * @param $request
     * @return bool|mixed
     */
    public function aroundValidate(
        \Magento\Framework\Data\Form\FormKey\Validator $subject,
        \Closure $proceed,
        $request
    ) {
        if ($request->getRouteName() == 'amsociallogin') {
            return true;
        } else {
            return $proceed($request);
        }
    }
}
