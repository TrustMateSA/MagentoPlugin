<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Cron;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use TrustMate\Opinions\Model\Review as ReviewModel;
use TrustMate\Opinions\Model\Store as StoreModel;
use TrustMate\Opinions\Service\Review as ReviewService;

class Review
{
    /**
     * @var ReviewModel
     */
    private $reviewModel;

    /**
     * @var ReviewService
     */
    private $reviewService;

    /**
     * @var StoreModel
     */
    private $storeModel;

    /**
     * @param ReviewModel $reviewModel
     * @param ReviewService $reviewService
     * @param StoreModel $storeModel
     */
    public function __construct(
        ReviewModel   $reviewModel,
        ReviewService $reviewService,
        StoreModel    $storeModel
    ) {
        $this->reviewModel = $reviewModel;
        $this->reviewService = $reviewService;
        $this->storeModel = $storeModel;
    }

    /**
     * Add review to magento
     *
     * @return void
     * @throws InputException
     * @throws LocalizedException
     * @throws AlreadyExistsException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $this->reviewService->add($this->prepareData());
        foreach ($this->storeModel->getStoreLocales() as $languageCode => $storeId) {
            $this->reviewService->addTranslation($this->prepareData(true, $languageCode));
        }

    }

    /**
     * Return query data for request
     *
     * @param bool $addLanguage
     * @param string|null $language
     *
     * @return array
     * @throws InputException
     * @throws LocalizedException
     */
    private function prepareData(bool $addLanguage = false, ?string $language = null): array
    {
        $preparedData['query'] = [
            'start' => $this->reviewModel->getLatestUpdatedDate($addLanguage),
            'per_page' => 1000,
            'page' => 1
        ];

        if ($addLanguage) {
            $preparedData['query']['language'] = $language;
        }

        return $preparedData;
    }
}
