<?php

namespace X247Commerce\Delivery\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\Store;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

class DeliveryData extends AbstractHelper
{
	const GOOGLE_API_KEY_PATH = 'amlocator/general/api';	
	const RATE_SHIPPING_CONFIG_PATH = 'delivery/general/distance';	
    const API_RESPONSE_CODE_SUCCESS = 200;
    const API_RESPONSE_CODE_ERROR = 400;

    protected ResponseFactory $responseFactory;
    protected ClientFactory $clientFactory;
    protected TimezoneInterface $timezone;
    protected LoggerInterface $logger;

    public function __construct(
        Context $context,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        TimezoneInterface $timezone,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->timezone = $timezone;
        $this->logger = $logger;
    }

	public function getGoogleApiKey() 
	{
		return $this->scopeConfig->getValue(
			self::GOOGLE_API_KEY_PATH , ScopeInterface::SCOPE_STORE
		);
	}

	public function getRateShipping() 
	{
		return $this->scopeConfig->getValue(
			self::RATE_SHIPPING_CONFIG_PATH , ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * calculate distancse from 2 place by google distancematrix api
	 * @var string $originPostcode
	 * @var string $destinationPostcode
	 * @return float
	 **/

	public function calculateDistance($originPostcode, $destinationPostcode)
	{
		$apiUriEndpoint = $this->getEndpointApiUrl($originPostcode, $destinationPostcode, $this->getGoogleApiKey());

        $response = $this->doRequest(
            $apiUriEndpoint,
            // [], // all params is already included in endpoint with GET method
            // Request::HTTP_METHOD_GET
        );
        $status = $response->getStatusCode();

        if ($status == self::API_RESPONSE_CODE_SUCCESS) {
            $responseBody = $response->getBody();
            $responseContent = $responseBody->getContents();
            return $responseContent;
        }   else {
            $this->logger->error('API getList error: '. $response->getReasonPhrase());
            echo 'API getList error: '. $response->getReasonPhrase();
            return false;
        }
	}

	protected function getEndpointApiUrl($originPostcode, $destinationPostcode, $apiKey)
    {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?destinations=$destinationPostcode&origins=$originPostcode&key=$apiKey";

        return $url;
    }

    protected function doRequest(
        string $uriEndpoint,
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_GET
    ): Response {
        /** @var Client $client */
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => 'https://maps.googleapis.com'
        ]]);
        
        try {
            $response = $client->request(
                $requestMethod,
                $uriEndpoint,
                $params
            );

        } catch (TransferException $exception) {
            /** @var Response $response */
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        } catch (GuzzleException $exception) {
            /** @var Response $response */
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }
}