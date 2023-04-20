<?php

namespace Amasty\SocialLoginAppleId\Plugin\SocialLogin\Model;

use Amasty\SocialLogin\Model\SocialData;
use Amasty\SocialLogin\Model\SocialList;

class SocialDataPlugin
{
    /**
     * @param SocialData $subject
     * @param array $result
     * @param $type
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSocialConfig(SocialData $subject, array $result, $type)
    {
        if (SocialList::TYPE_APPLE == $type) {
            $result = ['adapter' => \Amasty\SocialLoginAppleId\Model\Provider::class];
        }

        return $result;
    }
}
