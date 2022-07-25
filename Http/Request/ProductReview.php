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
        TrustMateRestApi    $apiService,
        SerializerInterface $serializerInterface
    ) {
        $this->apiService = $apiService;
        $this->serializerInterface = $serializerInterface;
    }

    /**
     * @param array $data
     * @param bool $translation
     *
     * @return array|bool|float|int|string|null
     */
    public function sendRequest(array $data, bool $translation = false)
    {
        if ($translation) {
            $response = $this->getTranslations($data);
        } else {
            $response = $this->getReview($data);
        }

        if ($response->getStatusCode() !== 200) {
            return [
                'status' => false,
                'message' => $response->getReasonPhrase()
            ];
        }

        return $this->serializerInterface->unserialize($response->getBody()->getContents());
    }

    /**
     * Get review with original language
     *
     * @param array $data
     *
     * @return Response
     */
    protected function getReview(array $data): Response
    {
        return $this->apiService->doRequest(
                TrustMateApiEnum::REVIEW_ENDPOINT,
                $data
            );
    }

    /**
     * Get translations for review
     *
     * @param array $data
     *
     * @return Response
     */
    protected function getTranslations(array $data): Response
    {
        return $this->apiService->doRequest(
                TrustMateApiEnum::REVIEW_TRANSLATION_ENDPOINT,
                $data
            );
    }
}
