<?php

namespace Amasty\SocialLoginAppleId\Model;

use Magento\Framework\Exception\LocalizedException;

class JWT
{
    public const ASN1_SEQUENCE = '30';
    public const ASN1_INTEGER = '02';
    public const ASN1_LENGTH_2BYTES = '81';
    public const ASN1_BIG_INTEGER_LIMIT = '7f';
    public const ASN1_NEGATIVE_INTEGER = '00';
    public const BYTE_SIZE = 2;
    public const KEY_LENGTH = 64;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;

    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->json = $json;
    }

    /**
     * @param $payload
     * @param $key
     * @param null $keyId
     * @param null $head
     * @return string
     * @throws LocalizedException
     */
    public function encode($payload, $key, $keyId = null, $head = null)
    {
        $header = ['kid' => $keyId, 'alg' => 'ES256'];
        if (isset($head) && is_array($head)) {
            $header = array_merge($head, $header);
        }
        $segments = [];
        $segments[] = $this->urlsafeB64Encode($this->jsonEncode($header));
        $segments[] = $this->urlsafeB64Encode($this->jsonEncode($payload));
        $signingInput = implode('.', $segments);
        $signature = $this->sign($signingInput, $key);

        $segments[] = $this->urlsafeB64Encode($this->fromAsn1($signature));

        return implode('.', $segments);
    }

    /**
     * @param $signature
     * @return false|string
     */
    private function fromAsn1($signature)
    {
        $message  = bin2hex($signature);
        $position = 0;
        if ($this->readAsn1Content($message, $position, self::BYTE_SIZE) !== self::ASN1_SEQUENCE) {
            throw new \InvalidArgumentException('Invalid data. Should start with a sequence.');
        }
        if ($this->readAsn1Content($message, $position, self::BYTE_SIZE) === self::ASN1_LENGTH_2BYTES) {
            $position += self::BYTE_SIZE;
        }
        $pointR = $this->retrievePositiveInteger($this->readAsn1Integer($message, $position));
        $pointS = $this->retrievePositiveInteger($this->readAsn1Integer($message, $position));
        $fullPointR = str_pad($pointR, self::KEY_LENGTH, '0', STR_PAD_LEFT);
        $fullPointS = str_pad($pointS, self::KEY_LENGTH, '0', STR_PAD_LEFT);
        $points = hex2bin($fullPointR . $fullPointS);

        return $points;
    }

    /**
     * @param string $message
     * @param int $position
     * @param int $length
     * @return string
     */
    private function readAsn1Content(string $message, int &$position, int $length): string
    {
        $content = mb_substr($message, $position, $length, '8bit');
        $position += $length;

        return $content;
    }

    /**
     * @param string $message
     * @param int $position
     * @return string
     */
    private function readAsn1Integer(string $message, int &$position): string
    {
        if ($this->readAsn1Content($message, $position, self::BYTE_SIZE) !== self::ASN1_INTEGER) {
            throw new \InvalidArgumentException('Invalid data. Should contain an integer.');
        }
        $length = (int)hexdec($this->readAsn1Content($message, $position, self::BYTE_SIZE));

        return $this->readAsn1Content($message, $position, $length * self::BYTE_SIZE);
    }

    /**
     * @param string $data
     * @return string
     */
    private function retrievePositiveInteger(string $data): string
    {
        while (mb_substr($data, 0, self::BYTE_SIZE, '8bit') === self::ASN1_NEGATIVE_INTEGER
            && mb_substr($data, 2, self::BYTE_SIZE, '8bit') > self::ASN1_BIG_INTEGER_LIMIT
        ) {
            $data = mb_substr($data, 2, null, '8bit');
        }

        return $data;
    }

    /**
     * @param $msg
     * @param $key
     * @return string
     * @throws LocalizedException
     */
    private function sign($msg, $key)
    {
        $signature = '';
        $success = openssl_sign($msg, $signature, $key, OPENSSL_ALGO_SHA256);
        if (!$success) {
            throw new LocalizedException(__('OpenSSL unable to sign data'));
        } else {
            return $signature;
        }
    }

    /**
     * @param $input
     * @return bool|false|string
     */
    private function jsonEncode($input)
    {
        $json = $this->json->serialize($input);

        return $json;
    }

    /**
     * @param $input
     * @return mixed
     */
    private function urlsafeB64Encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }
}
