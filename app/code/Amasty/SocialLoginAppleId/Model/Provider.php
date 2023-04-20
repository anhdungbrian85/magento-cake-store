<?php

namespace Amasty\SocialLoginAppleId\Model;

use Amasty\SocialLogin\Model\Login;
use Hybridauth\Adapter\OAuth2;
use Hybridauth\Storage\Session;
use Hybridauth\User;

class Provider extends OAuth2
{
    public const BASE_URL = 'https://appleid.apple.com';

    /**
     * @var string
     */
    protected $apiBaseUrl = 'https://appleid.apple.com/auth/';

    /**
     * @var string
     */
    protected $authorizeUrl = 'https://appleid.apple.com/auth/authorize';

    /**
     * @var string
     */
    protected $accessTokenUrl = 'https://appleid.apple.com/auth/token';

    /**
     * @var string
     */
    protected $providerId = 'apple';

    /**
     * @var string
     */
    public $scope = 'name email';

    /**
     * @var int
     */
    protected $AuthorizeUrlParametersEncType = PHP_QUERY_RFC3986;

    /**
     * @var bool
     */
    protected $supportRequestState = false;

    public function initialize()
    {
        parent::initialize();
        $this->AuthorizeUrlParameters['response_mode'] = 'form_post';
        $this->AuthorizeUrlParameters['response_type'] = 'code id_token';
    }

    /**
     * @return User\Profile|void
     * @throws \Hybridauth\Exception\RuntimeException
     */
    public function getUserProfile()
    {
        $storage = new Session();
        $appleParams = $storage->get(Login::AMSOCIAL_LOGIN_PARAMS) ?: [];
        $userProfile = new User\Profile();
        if (isset($appleParams['id_token'])) {
            $claims = explode('.', $appleParams['id_token'])[1];
            $data = $this->unserialize($claims);
            $userProfile->identifier = $data['sub'];
            $userProfile->email = $data['email'];
        }

        return $userProfile;
    }

    /**
     * @param $string
     * @return array
     */
    public function unserialize($string)
    {
        // @codingStandardsIgnoreStart
        $result = json_decode(base64_decode($string), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return json_decode($string, true);
        }
        // @codingStandardsIgnoreEnd

        return $result;
    }
}
