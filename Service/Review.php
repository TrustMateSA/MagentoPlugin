<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Service;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use TrustMate\Opinions\Logger\Logger;
use TrustMate\Opinions\Model\Config\Data;
use TrustMate\Opinions\Model\Review as ReviewModel;
use TrustMate\Opinions\Model\Store;

class Review
{
    /**
     * @var ReviewModel
     */
    private $reviewModel;

    /**
     * @var Data
     */
    private $config;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param ReviewModel $reviewModel
     * @param ProductRepositoryInterface $productRepository
     * @param Data $config
     * @param Store $store
     * @param Logger $logger
     */
    public function __construct(
        ReviewModel                $reviewModel,
        ProductRepositoryInterface $productRepository,
        Data                       $config,
        Store                      $store,
        Logger                     $logger
    ) {
        $this->reviewModel = $reviewModel;
        $this->productRepository = $productRepository;
        $this->config = $config;
        $this->store = $store;
        $this->logger = $logger;
    }

    /**
     * Save review
     *
     * @param array $item
     * @param int $storeId
     * @param bool $translation
     *
     * @return array
     * @throws LocalizedException
     */
    public function prepareDataToSave(array $item, int $storeId, bool $translation = false): array
    {
        $originalBody = (!isset($item['originalBody']) || !$item['originalBody']) ? null : $item['originalBody'];
        $productId = $item['product']['localId'];
        if ($this->config->isFixLocalIdEnabled()) {
            try {
                $product = $this->productRepository->get($item['product']['localId']);
                $productId = $product->getId();
            } catch (NoSuchEntityException $e) {
                $this->logger->info($e->getMessage());
            }
        }

        return [
            'id' => $this->reviewModel->checkIfExists($item['publicIdentifier'], $originalBody, $translation),
            'created_at' => $item['createdAt'],
            'updated_at' => $item['updatedAt'],
            'grade' => $item['grade'],
            'author_email' => $item['author']['email'],
            'author_name' => $item['author']['name'],
            'product' => $productId,
            'body' => !$item['body'] ? ' ' : $item['body'],
            'public_identifier' => $item['publicIdentifier'],
            'language' => $item['language'],
            'original_body' => $originalBody,
            'order_increment_id' => $item['orderIdentifier'],
            'gtin_code' => $item['product']['gtin'],
            'mpn_code' => $item['product']['mpn'],
            'status' => $item['status'],
            'store_id' => $storeId
        ];
    }
}
