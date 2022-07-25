<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Http\Client;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use TrustMate\Opinions\Enum\TrustMateApiEnum;
use TrustMate\Opinions\Model\Config\Data;

class TrustMateRestApi
{
    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var Data
     */
    private $configData;

    /**
     * TrustMate API service constructor
     *
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param Data $configData
     */
    public function __construct(
        ClientFactory   $clientFactory,
        ResponseFactory $responseFactory,
        Data            $configData
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->configData = $configData;
    }

    /**
     * API request with provided params
     *
     * @param string $endpoint
     * @param array $data
     * @param string $method
     *
     * @return Response
     */
    public function doRequest(
        string $endpoint,
        array $data = [],
        string $method = Request::HTTP_METHOD_GET
    ): Response
    {
        $client = $this->clientFactory->create([
            'config' => [
                'base_uri' => ($this->configData->isSandboxEnabled())
                    ? TrustMateApiEnum::SANDBOX_URL
                    : TrustMateApiEnum::PRODUCTION_URL,
                'headers' => [
                    'x-api-key' => $this->configData->getApiKey(),
                    'Accept' => 'application/json"'
                ]
            ]
        ]);

        try {
            $response = $client->request(
                $method,
                $endpoint,
                $data
            );
        } catch (GuzzleException $exception) {
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }
}
