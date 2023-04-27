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
use Magento\Store\Model\StoreManagerInterface;
use TrustMate\Opinions\Model\Review as ReviewModel;
use TrustMate\Opinions\Model\Store as StoreModel;
use TrustMate\Opinions\Service\Api\Query;
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Query
     */
    private $query;

    /**
     * @param ReviewModel           $reviewModel
     * @param ReviewService         $reviewService
     * @param StoreModel            $storeModel
     * @param StoreManagerInterface $storeManager
     * @param Query                 $query
     */
    public function __construct(
        ReviewModel           $reviewModel,
        ReviewService         $reviewService,
        StoreModel            $storeModel,
        StoreManagerInterface $storeManager,
        Query                 $query
    ) {
        $this->reviewModel   = $reviewModel;
        $this->reviewService = $reviewService;
        $this->storeModel    = $storeModel;
        $this->storeManager  = $storeManager;
        $this->query         = $query;
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
        foreach ($this->getAllStores() as $store) {
            $storeId      = (int)$store->getId();
            $data         = $this->query->prepare($storeId);
            $this->reviewService->add($data, $storeId);

            // $languageCode = $this->storeModel->getStoreLocales($storeId);
            // $data         = $this->query->prepare($storeId, $languageCode, true);
            // $this->reviewService->add($data, $storeId, true);
        }
    }

    /**
     * Get all stores
     *
     * @return array
     */
    private function getAllStores(): array
    {
        return $this->storeManager->getStores();
    }
}
