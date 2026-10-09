<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Http\Request;

use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Psr\Http\Message\ResponseInterface;
use TrustMate\Opinions\Enum\TrustMateApiEnum;
use TrustMate\Opinions\Http\Client\TrustMateRestApi;

class ReviewInvitation
{
    /**
     * @var TrustMateRestApi
     */
    private $apiService;

    /**
     * @var SerializerInterface
     */
    private $serializerInterface;

    /**
     * ProductReview constructor
     *
     * @param TrustMateRestApi    $apiService
     * @param SerializerInterface $serializerInterface
     */
    public function __construct(
        TrustMateRestApi    $apiService,
        SerializerInterface $serializerInterface
    ) {
        $this->apiService          = $apiService;
        $this->serializerInterface = $serializerInterface;
    }

    /**
     * @param array $data
     * @param int   $storeId
     *
     * @return array|bool|float|int|string|null
     * @throws GuzzleException on connection error and 4xx/5xx response
     * @throws \RuntimeException on any other non-200 response
     */
    public function sendRequest(array $data, int $storeId)
    {
        $response = $this->create($data, $storeId);
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                sprintf('Unexpected response: %d %s', $response->getStatusCode(), $response->getReasonPhrase())
            );
        }

        $bodyContent = $response->getBody()->getContents();

        return $this->serializerInterface->unserialize($bodyContent);
    }

    /**
     * Create invitation
     *
     * @param array $data
     * @param int   $storeId
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    protected function create(array $data, int $storeId): ResponseInterface
    {
        return $this->apiService->doRequest(
            $storeId,
            TrustMateApiEnum::INVITATION_ENDPOINT,
            $data,
            Request::HTTP_METHOD_POST
        );
    }
}
