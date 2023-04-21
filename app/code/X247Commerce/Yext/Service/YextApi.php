<?php
/**
 * Yext API Call
 *
 * @author     Phung Thong <phung.thong@247commerce.co.uk>
 * @copyright  2022 247Commerce
 */
namespace X247Commerce\Yext\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use X247Commerce\Yext\Helper\Config as ConfigHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

/**
 * Class YextApi
 */
class YextApi
{
    
    const YEXT_API_RESPONSE_CODE_SUCCESS = 200;
    const YEXT_API_RESPONSE_CODE_ERROR = 400;

    protected ResponseFactory $responseFactory;
    protected ClientFactory $clientFactory;
    protected ConfigHelper $configHelper;
    protected TimezoneInterface $timezone;
    protected LoggerInterface $logger;
    /**
     * YextApi constructor
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
            $apiKey = $this->configHelper->getYextApiKey();

            if (!$apiKey) {
                throw new \Exception("API key cannot be empty");
            }

            $date = $this->timezone->date()->format('Ymd');

            $requiredParams = [
                'api_key' => $apiKey,
                'v' => $date
            ];
            $finalParams = array_merge($requiredParams,$params);
            return http_build_query($finalParams);

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return null;
        }
        

    }

     /**
     * Build API endpoint URI for GET method (read only API)
     * @param string $requestPath
     * @param array $params 
     * @return string
     */

    public function buildApiUri($requestPath, $params = [])
    {
        $baseEndpoint = $this->configHelper->getBaseApiEndPointUrl(). ConfigHelper::YEXT_ENDPOINT_STATIC_PATH;
        $paramsString = $this->buildQueryParams($params);
        $format = '%s%s?%s';
        return sprintf($format, $baseEndpoint, $requestPath, $paramsString);
    }

    /**
     * Fetch list of entities
     * @param array $params 
     * @param $fields
     * @return string
     * @see https://hitchhikers.yext.com/docs/knowledgeapis/knowledgegraph/entities/entities/
     */
    public function getList(array $params = [])
    {
        $apiUriEndpoint = $this->buildApiUri('entities', $params);

        $response = $this->doRequest(
            $apiUriEndpoint,
            // [], // all params is already included in endpoint with GET method
            // Request::HTTP_METHOD_GET
        );

        $status = $response->getStatusCode(); 
        
        if ($status == self::YEXT_API_RESPONSE_CODE_SUCCESS) {
            $responseBody = $response->getBody();
            $responseContent = $responseBody->getContents(); 
            return $responseContent;
        }   else {
            $this->logger->error('Yext API getList error: '. $response->getReasonPhrase());
            return false;
        }
        
    }

    /**
     * Fetch entity data by Yext entity id
     * @param array $params 
     * @param $fields
     * @return string
     * @see https://hitchhikers.yext.com/docs/knowledgeapis/knowledgegraph/entities/entities/#operation/getEntity
     */

    public function getByYextId($yextEntityId, array $params = [])
    {
        $apiUriEndpoint = $this->buildApiUri('entities/'.$yextEntityId, $params);

        $response = $this->doRequest(
            $apiUriEndpoint,
            // [], // all params is already included in endpoint with GET method
            // Request::HTTP_METHOD_GET
        );

        $status = $response->getStatusCode(); 
        
        if ($status == self::YEXT_API_RESPONSE_CODE_SUCCESS) {
            $responseBody = $response->getBody();
            $responseContent = $responseBody->getContents(); 
            return $responseContent;
        }   else {
            $this->logger->error('Yext API getByYextId error: '. $response->getReasonPhrase());
            return false;
        }
    }

    public function update($yextEntityData)
    {

    }

    public function create($yextEntityData)
    {

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
    private function doRequest(
        string $uriEndpoint,
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_GET
    ): Response {
        /** @var Client $client */
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => $this->configHelper->getBaseApiEndPointUrl()
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
