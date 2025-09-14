<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Http\Request;

use GuzzleHttp\Psr7\Response;
use Magento\Framework\Serialize\SerializerInterface;
use TrustMate\Opinions\Enum\TrustMateApiEnum;
use TrustMate\Opinions\Http\Client\TrustMateRestApi;

class ProductReview
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
        TrustMateRestApi $apiService,
        SerializerInterface $serializerInterface
    ) {
        $this->apiService = $apiService;
        $this->serializerInterface = $serializerInterface;
    }

    /**
     * @param array $data
     * @param int $storeId
     * @param bool $translation
     *
     * @return array|bool|float|int|string|null
     */
    public function sendRequest(array $data, int $storeId, bool $translation = false)
    {
        $response = $this->getReview($data, $storeId, $translation);

        if ($response->getStatusCode() !== 200) {
            return [
                'status' => $response->getStatusCode(),
                'message' => $response->getReasonPhrase()
            ];
        }

        return $this->serializerInterface->unserialize($response->getBody()->getContents());
    }

    /**
     * Get review with original language
     *
     * @param array $data
     * @param int $storeId
     * @param bool $translation
     *
     * @return Response
     */
    protected function getReview(array $data, int $storeId, bool $translation = false): Response
    {
        return $this->apiService->doRequest(
            $storeId,
            (!$translation) ? TrustMateApiEnum::REVIEW_ENDPOINT : TrustMateApiEnum::REVIEW_TRANSLATION_ENDPOINT,
            $data
        );
    }

}
