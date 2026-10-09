<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Http\Client;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Webapi\Rest\Request;
use Psr\Http\Message\ResponseInterface;
use TrustMate\Opinions\Enum\TrustMateApiEnum;
use TrustMate\Opinions\Model\Config\Data;

class TrustMateRestApi
{
    private const CONNECT_TIMEOUT = 5;
    private const TIMEOUT = 10;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var Data
     */
    private $configData;

    public function __construct(
        ClientFactory $clientFactory,
        Data $configData
    ) {
        $this->clientFactory = $clientFactory;
        $this->configData = $configData;
    }

    /**
     * @throws GuzzleException
     */
    public function doRequest(
        int $storeId,
        string $endpoint,
        array $data = [],
        string $method = Request::HTTP_METHOD_GET
    ): ResponseInterface {
        $client = $this->clientFactory->create([
            'config' => [
                'base_uri' => ($this->configData->isSandboxEnabled($storeId))
                    ? TrustMateApiEnum::SANDBOX_URL
                    : TrustMateApiEnum::PRODUCTION_URL,
                'headers' => [
                    'x-api-key' => $this->configData->getApiKey($storeId),
                    'Accept' => 'application/json'
                ],
                'connect_timeout' => self::CONNECT_TIMEOUT,
                'timeout' => self::TIMEOUT,
                // Guzzle keeps custom headers (x-api-key) on cross-host and https->http redirects
                'allow_redirects' => false
            ]
        ]);

        return $client->request(
            $method,
            $endpoint,
            $data
        );
    }
}
