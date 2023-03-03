<?php
/**
 * Nutritics API Call
 */
namespace X247Commerce\Nutritics\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use X247Commerce\Nutritics\Helper\Config as ConfigHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

class NutriticsApi
{    
    const NUTRITICS_API_RESPONSE_CODE_SUCCESS = 200;
    const NUTRITICS_API_RESPONSE_CODE_ERROR = 400;

    protected ResponseFactory $responseFactory;
    protected ClientFactory $clientFactory;
    protected ConfigHelper $configHelper;
    protected TimezoneInterface $timezone;
    protected LoggerInterface $logger;
    /**
     * NutriticsApi constructor
     *
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param ConfigHelper $configHelper
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        ConfigHelper $configHelper,
        TimezoneInterface $timezone,
        LoggerInterface $logger
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->configHelper = $configHelper;
        $this->timezone = $timezone;
        $this->logger = $logger;
    }

    /**
     * Build required params for API request
     * @param array $params 
     * @return string|null
     */
    public function buildQueryParams($params)
    {
        try {   
            $username = $this->configHelper->getNutriticsAccountUsername();
            $password = $this->configHelper->getNutriticsAccountPassword();
            $limit = $this->configHelper->getLimit();

            $filter = $this->configHelper->getFilter();

            if (!$username || !$password) {
                throw new \Exception("Username or Password cannot be empty");
            }

            $filterParams = [];
            if ($filter) {
                // code...
            }
            $requestParams = ['attr' => $params];
            $limitParams = ['limit' => $limit];

            $finalParams = array_merge($requestParams,$limitParams);
            return http_build_query($finalParams);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return null;
        }

    }

     /**
     * Build API endpoint URI for GET method (read only API)
     * @param string $requestFunction LIST, DELETE, DETAIL, CREATE, MODIFY
     * @param (string) $requestObject client, food, recipe, activity, menu
     * @param array $params 
     * @return string
     */
    public function buildApiUri($requestFunction, $requestObject, $params = [])
    {   
        $username = $this->configHelper->getNutriticsAccountUsername();
        $password = $this->configHelper->getNutriticsAccountPassword();
        $userId = $this->configHelper->getUserId();
        $paramUserId = '';
        if ($userId) {
            $paramUserId = 'user='.$userId.'&';
        }
        $baseEndpoint = "https://" . $username . ":" . $password . "@" . $this->configHelper->getBaseApiEndPointUrl();
        $paramsString = '';
        $paramsString = $this->buildQueryParams($params);
        $format = '%s/%s/%s%s&%s';
        return sprintf($format, $baseEndpoint, $requestFunction, $paramUserId, $requestObject, $paramsString);
    }

    /**
     * Fetch list of object
     * @param (string) $object client, food, recipe, activity, menu
     * @param array $params
     * @return string
     * @see 
     */
    public function getList($object, array $params = [])
    {
        // $params = $this->configHelper->getAttributesToFetch();
        $apiUriEndpoint = $this->buildApiUri('LIST', $object, $params);

        $response = $this->doRequest(
            $apiUriEndpoint,
            // [], // all params is already included in endpoint with GET method
            // Request::HTTP_METHOD_GET
        );

        $status = $response->getStatusCode(); 
        
        if ($status == self::NUTRITICS_API_RESPONSE_CODE_SUCCESS) {
            $responseBody = $response->getBody();
            $responseContent = $responseBody->getContents(); 
            return $responseContent;
        }   else {
            $this->logger->error('Nutritics API getList error: '. $response->getReasonPhrase());
            return false;
        }        
    }

    /**
     * Fetch entity data by ifc (IFC: International Food Code)
     * @param array $params 
     * @param $fields
     * @return string
     */

    public function getFoodProductByIfc($ifc, array $params = [])
    {
        $apiUriEndpoint = $this->buildApiUri('LIST', 'food=ifc='.$ifc, $params);

        $response = $this->doRequest(
            $apiUriEndpoint,
            // [], // all params is already included in endpoint with GET method
            // Request::HTTP_METHOD_GET
        );

        $status = $response->getStatusCode(); 
        
        if ($status == self::NUTRITICS_API_RESPONSE_CODE_SUCCESS) {
            $responseBody = $response->getBody();
            $responseContent = $responseBody->getContents(); 
            return $responseContent;
        }   else {
            $this->logger->error('Nutritics API get item error: '. $response->getReasonPhrase());
            return false;
        }
    }
    /**
     * Do API request with provided params
     *
     * @param string $uriEndpoint
     * @param array $params
     * @param string $requestMethod
     *
     * @return Response
     */
    public function doRequest(
        string $uriEndpoint,
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_GET
    ): Response {
        /** @var Client $client */
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => "https://" . $this->configHelper->getBaseApiEndPointUrl()
        ]]);
        try {
            $response = $client->request(
                $requestMethod,
                $uriEndpoint,
                $params
            );

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