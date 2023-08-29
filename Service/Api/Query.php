<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Service\Api;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use TrustMate\Opinions\Model\Review as ReviewModel;

class Query
{
    /**
     * @var ReviewModel
     */
    private $reviewModel;

    /**
     * @param ReviewModel $reviewModel
     */
    public function __construct(ReviewModel $reviewModel)
    {
        $this->reviewModel = $reviewModel;
    }

    /**
     * @param int         $storeId
     * @param string|null $languageCode
     *
     * @param bool        $translation
     * @param string      $perPage
     * @param string      $page
     *
     * @return array[]
     * @throws InputException
     * @throws LocalizedException
     */
    public function prepare(
        int $storeId,
        ?string $languageCode = null,
        bool $translation = false,
        string $perPage = '1000',
        string $page = '1'
    ): array {
        $data['query'] = [
            'start' => $this->reviewModel->getLatestUpdatedDate($storeId, $translation),
            'per_page' => $perPage,
            'page' => $page,
            'sort' => 'updatedAt',
            'include_shared' => 1,
        ];

        if ($translation) {
            $data['query']['language'] = $languageCode;
        }

        return $data;
    }
}
