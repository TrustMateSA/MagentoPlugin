<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Http\Request;

use GuzzleHttp\Psr7\Response;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\Rest\Request;
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
     * @param TrustMateRestApi $apiService
     * @param SerializerInterface $serializerInterface
     */
    public function __construct(
        TrustMateRestApi    $apiService,
        SerializerInterface $serializerInterface
    ) {
        $this->apiService = $apiService;
        $this->serializerInterface = $serializerInterface;
    }

    /**
     * @param array $data
     *
     * @return array|bool|float|int|string|null
     */
    public function sendRequest(array $data)
    {
        $response = $this->create($data);
        if ($response->getStatusCode() !== 200) {
            return [
                'status' => false,
                'message' => $response->getReasonPhrase()
            ];
        }

        $bodyContent = $response->getBody()->getContents();
        return $this->serializerInterface->unserialize($bodyContent);
    }

    /**
     * Create invitation
     *
     * @param array $data
     *
     * @return Response
     */
    protected function create(array $data): Response
    {
        return $this->apiService->doRequest(
                TrustMateApiEnum::INVITATION_ENDPOINT,
                $data,
                Request::HTTP_METHOD_POST
            );
    }
}
