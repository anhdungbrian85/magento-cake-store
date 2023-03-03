<?php

declare(strict_types=1);

namespace Amasty\SocialLoginAppleId\Plugin\SocialLogin\Model\SocialList;

use Amasty\SocialLogin\Model\SocialList;

class AddAppleCode
{
    /**
     * @see SocialList::getList
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(SocialList $subject, array $result): array
    {
        $result[SocialList::TYPE_APPLE] = 'Apple';

        return $result;
    }
}
