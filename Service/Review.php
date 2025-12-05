<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Service;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store as MagentoStore;
use TrustMate\Opinions\Logger\Logger;
use TrustMate\Opinions\Model\Category;
use TrustMate\Opinions\Model\Config\Data;
use TrustMate\Opinions\Model\Review as ReviewModel;

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
     * @var Configurable
     */
    private $configurableType;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        ReviewModel $reviewModel,
        ProductRepositoryInterface $productRepository,
        Configurable $configurableType,
        Category $category,
        ScopeConfigInterface $scopeConfig,
        Data $config,
        Logger $logger
    ) {
        $this->reviewModel = $reviewModel;
        $this->productRepository = $productRepository;
        $this->configurableType = $configurableType;
        $this->category = $category;
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
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
        $localeCode = $this->scopeConfig->getValue(
            'general/locale/code',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $invitationData = [
            'customer_name' => $order->getCustomerFirstname(),
            'send_to' => $order->getCustomerEmail(),
            'order_number' => $order->getIncrementId(),
            'language' => strstr($localeCode, '_', true),
            'source_type' => 'magento3.0'
        ];

        if ($includeProducts) {
            $sendVariants = $this->config->sendVariantInformation($storeId);
            foreach ($order->getAllItems() as $item) {
                $productType = $item->getProductType();
                $product = $item->getProduct();
                $isChild = (bool) $item->getParentItemId();
                $context = ($isChild ? 'child_' : 'parent_') . $productType;

                switch ($context) {
                    case 'parent_simple':
                        $invitationData['products'][$product->getSku()] = $this->prepareProductData($product, $order);
                        break;

                    case 'parent_bundle':
                    case 'parent_configurable':
                        if (!$sendVariants) {
                            $invitationData['products'][$product->getSku()] = $this->prepareProductData($product, $order);
                        }

                        break;
                    case 'child_bundle':
                    case 'child_configurable':
                        if ($sendVariants) {
                            $invitationData['products'][$product->getSku()] = $this->prepareProductData($product, $order, $item->getParentItem());
                        }

                        break;
                    case 'child_simple':
                        if ($item->getParentItemId()) {
                            $parentType = $item->getParentItem()->getProductType();
                            if (in_array($parentType, ['configurable', 'bundle']) && !$sendVariants) {
                                break;
                            }
                        }

                        $invitationData['products'][$product->getSku()] = $this->prepareProductData(
                            $product,
                            $order,
                            $item->getParentItem()
                        );
                        break;

                    default:
                        $parentIds = $this->configurableType->getParentIdsByChild($product->getId());
                        if (($parentIds || $item->getParentItemId()) && !$sendVariants) {
                            $parentType = $item->getParentItem()->getProductType();
                            if (in_array($parentType, ['configurable', 'bundle']) && !$sendVariants) {
                                break;
                            }

                            $product = $this->productRepository->getById((int) $parentIds[0]);
                        }

                        $invitationData['products'][$product->getSku()] = $this->prepareProductData(
                            $product,
                            $order,
                            $item->getParentItem()
                        );
                }
            }
        }

        return $invitationData;
    }

    /**
     * @throws NoSuchEntityException
     */
    private function prepareProductData(Product $product, Order|OrderInterface $order, Item|OrderItem $parentItem = null): array
    {
        $storeId = (int) $order->getStore()->getId();
        $groupId = ($parentItem) ? $parentItem->getProductId() : $product->getId();
        $localId = $this->config->isFixLocalIdEnabled($storeId) ? $product->getId() : $product->getSku();
        $gtinCode = $this->config->getGtinCode($storeId);
        $mpnCode = $this->config->getMpnCode($storeId);

        return [
            'id' => $localId,
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'product_url' => $this->getProductFrontendUrl(
                $product,
                $order->getStore()
            ),
            'category' => $this->category->getCategoriesPath($product->getCategoryIds()),
            'image_url' => $this->getImageUrl($order->getStore(), $product),
            'image_thumb_url' => $this->getImageUrl($order->getStore(), $product, true),
            'gtin' => $gtinCode ? $product->getData($gtinCode) : null,
            'mpn' => $mpnCode ? $product->getData($mpnCode) : null,
            'group_id' => $groupId,
        ];
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

    /**
     * @throws NoSuchEntityException
     */
    private function getProductFrontendUrl(Product $product, MagentoStore $store): string
    {
        $parentIds = $this->configurableType->getParentIdsByChild($product->getId());
        $urlKey = $product->getUrlKey();
        if (!empty($parentIds)) {
            $parent = $this->productRepository->getById($parentIds[0], false, $store->getId());
            $urlKey = $parent->getUrlKey();
        }

        return sprintf(
            '%s%s%s',
            rtrim($store->getBaseUrl(), '/'),
            '/' . $urlKey,
            $store->getConfig('catalog/seo/product_url_suffix')
        );
    }
}
