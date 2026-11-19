<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Http\Client;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        Data $configData,
        LoggerInterface $logger
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->configData = $configData;
        $this->logger = $logger;
    }

    public function doRequest(
        int $storeId,
        string $endpoint,
        array $data = [],
        string $method = Request::HTTP_METHOD_GET
    ): Response {
        $client = $this->clientFactory->create([
            'config' => [
                'base_uri' => ($this->configData->isSandboxEnabled($storeId))
                    ? TrustMateApiEnum::SANDBOX_URL
                    : TrustMateApiEnum::PRODUCTION_URL,
                'headers' => [
                    'x-api-key' => $this->configData->getApiKey($storeId),
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
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);

            if ($exception instanceof RequestException && $exception->hasResponse()) {
                return $exception->getResponse();
            }

            $status = (int) $exception->getCode();
            $response = $this->responseFactory->create([
                'status' => ($status >= 400 && $status <= 599) ? $status : 503,
                'reason' => 'TrustMate API request failed'
            ]);
        }

        return $response;
    }
}
