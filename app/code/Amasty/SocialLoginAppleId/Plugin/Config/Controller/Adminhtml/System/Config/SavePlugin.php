<?php

namespace Amasty\SocialLoginAppleId\Plugin\Config\Controller\Adminhtml\System\Config;

use Amasty\SocialLoginAppleId\Model\JWT;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Amasty\SocialLoginAppleId\Model\Provider;
use Amasty\SocialLogin\Model\ConfigData;
use Magento\Framework\Message\ManagerInterface;

class SavePlugin
{
    public const AMASTY_APPLE_LOGIN_KEY = 'amasty/login/apple';
    public const SIX_MOUNTHS = 15552000;
    public const AMSOCIALLOGIN_APPLE_API_SECRET = 'amsociallogin/apple/api_secret';
    public const AMSOCIALLOGIN_APPLE_SECRET_EXP_DATE = 'amsociallogin/apple/secret_exp_date';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var File
     */
    private $file;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $fileSystem;

    /**
     * @var JWT
     */
    private $jwt;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var ConfigData
     */
    private $configData;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        JWT $jwt,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Registry $registry,
        WriterInterface $writer,
        ConfigData $configData,
        ManagerInterface $messageManager,
        File $file
    ) {
        $this->jwt = $jwt;
        $this->date = $date;
        $this->file = $file;
        $this->fileSystem = $fileSystem;
        $this->writer = $writer;
        $this->encryptor = $encryptor;
        $this->configData = $configData;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterExecute($subject, $result)
    {
        $request = $subject->getRequest();
        if ($request->getParam('section') == 'amsociallogin') {
            $this->generateJWT();
        }

        return $result;
    }

    /**
     * @param array $fields
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function generateJWT()
    {
        $keyPath = $this->getKeyPath();
        $teamId = $this->configData->getConfigValue('apple/team_id');
        $apiKey = $this->configData->getConfigValue('apple/api_key');
        if ($this->configData->getConfigValue('apple/enabled') && $keyPath && $teamId && $apiKey) {
            $expirationDate = $this->date->gmtTimestamp(strtotime('+5 months'));
            $now = $this->date->gmtTimestamp();

            $payload = [
                'iss' => $teamId,
                'iat' => $now,
                'exp' => $now + self::SIX_MOUNTHS,
                'aud' => Provider::BASE_URL,
                'sub' => $apiKey,
            ];
            try {
                $secretKey = $this->jwt->encode(
                    $payload,
                    $this->file->read($keyPath),
                    $this->configData->getConfigValue('apple/key_id')
                );
                $this->writer->save(self::AMSOCIALLOGIN_APPLE_API_SECRET, $this->encryptor->encrypt($secretKey));
                $this->writer->save(self::AMSOCIALLOGIN_APPLE_SECRET_EXP_DATE, $expirationDate);
                $this->messageManager->addSuccessMessage(__('Api Secret Key has been sucessfully generated.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Error during Api Secret generation occurred.'));
            }
            $this->file->rm($keyPath);
            $this->messageManager->addNoticeMessage(__('We have removed key file to improve security.'));
            $this->writer->delete('amsociallogin/apple/key');
        }
    }

    /**
     * @param $fileName
     * @return string
     */
    private function getKeyPath()
    {
        $path = '';
        $name = $this->registry->registry('amsocial_apple_key');
        if ($name) {
            $mediaPath = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath();
            $path = $mediaPath . self::AMASTY_APPLE_LOGIN_KEY . '/' . $name;
        }

        return $path;
    }
}
