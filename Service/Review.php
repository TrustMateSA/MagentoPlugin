<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Service;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\Store as MagentoStore;
use TrustMate\Opinions\Logger\Logger;
use TrustMate\Opinions\Model\Category;
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
     * @var Category
     */
    private $category;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param ReviewModel $reviewModel
     * @param ProductRepositoryInterface $productRepository
     * @param Category $category
     * @param Data $config
     * @param Store $store
     * @param Resolver $resolver
     * @param Logger $logger
     */
    public function __construct(
        ReviewModel $reviewModel,
        ProductRepositoryInterface $productRepository,
        Category $category,
        Data $config,
        Store $store,
        Resolver $resolver,
        Logger $logger
    ) {
        $this->reviewModel = $reviewModel;
        $this->productRepository = $productRepository;
        $this->category = $category;
        $this->config = $config;
        $this->store = $store;
        $this->resolver = $resolver;
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

    /**
     * @throws NoSuchEntityException
     */
    public function prepareInvitationData(Order|OrderInterface $order, bool $includeProducts): array
    {
        $storeId = (int) $order->getStoreId();
        $invitationData = [
            'customer_name' => $order->getCustomerFirstname(),
            'send_to' => $order->getCustomerEmail(),
            'order_number' => $order->getIncrementId(),
            'language' => strstr($this->resolver->getLocale(), '_', true),
            'source_type' => 'magento3.0'
        ];

        if ($includeProducts) {
            foreach ($order->getAllVisibleItems() as $item) {
                $product = $item->getProduct();
                $localId = $this->config->isFixLocalIdEnabled($storeId) ? $product->getId() : $product->getSku();
                $store = $order->getStore();
                $gtinCode = $this->config->getGtinCode($storeId);
                $mpnCode = $this->config->getMpnCode($storeId);

                $invitationData['products'][$product->getSku()] = [
                    'id' => $localId,
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                    'product_url' => $product->getProductUrl(),
                    'category' => $this->category->getCategoriesPath($product->getCategoryIds()),
                    'image_url' => $this->getImageUrl($store, $product),
                    'image_thumb_url' => $this->getImageUrl($store, $product, true),
                    'gtin' => $gtinCode ? $product->getData($gtinCode) : null,
                    'mpn' => $mpnCode ? $product->getData($mpnCode) : null
                ];
            }
        }

        return $invitationData;
    }

    private function getImageUrl(MagentoStore $store, Product $product, bool $thumbnail = false): string
    {
        return sprintf(
            '%s%s%s',
            $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA),
            'catalog/product',
            ($thumbnail) ? $product->getThumbnail() : $product->getImage()
        );
    }
}
