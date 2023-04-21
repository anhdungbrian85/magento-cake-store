<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
namespace X247Commerce\Yext\Plugin\Frontend;

use Magento\Framework\App\RequestInterface;

class FormKeyValidator
{
    const YEXT_WEBHOOK_CONTROLLER = 'yext_entity_webhook';

    public function afterValidate(
        \Magento\Framework\Data\Form\FormKey\Validator $subject,
        $result,
        RequestInterface $request
    ) {
        
        if ($request->getFullActionName() == self::YEXT_WEBHOOK_CONTROLLER) {
            return true;
        }
        return $result;
    }
}
