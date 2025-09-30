<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Plugin\Magento\Sales\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipOrderInterface;
use Psr\Log\LoggerInterface;
use TrustMate\Opinions\Enum\TrustMateConfigDataEnum;
use TrustMate\Opinions\Http\Request\ReviewInvitation;
use TrustMate\Opinions\Model\Category;
use TrustMate\Opinions\Model\Config\Data;

class ShipOrder
{
    /**
     * @var Data
     */
    private $configData;

    /**
     * @var ReviewInvitation
     */
    private $reviewInvitation;

    /**
     * @var SerializerInterface
     */
    private $serializerInterface;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Data $configData
     * @param SerializerInterface $serializerInterface
     * @param OrderRepositoryInterface $orderRepository
     * @param Resolver $resolver
     * @param LoggerInterface $logger
     * @param Category $category
     * @param ReviewInvitation $reviewInvitation
     */
    public function __construct(
        Data $configData,
        SerializerInterface $serializerInterface,
        OrderRepositoryInterface $orderRepository,
        Resolver $resolver,
        LoggerInterface $logger,
        Category $category,
        ReviewInvitation $reviewInvitation
    ) {
        $this->configData = $configData;
        $this->serializerInterface = $serializerInterface;
        $this->orderRepository = $orderRepository;
        $this->resolver = $resolver;
        $this->logger = $logger;
        $this->category = $category;
        $this->reviewInvitation = $reviewInvitation;
    }

    /**
     * @param ShipOrderInterface $subject
     * @param int|null $result
     * @param int $orderId
     *
     * @return int|null
     * @throws NoSuchEntityException
     */
    public function afterExecute(ShipOrderInterface $subject, ?int $result, int $orderId): ?int
    {
        if ($this->configData->isModuleEnabled()
            && $this->configData->getInvitationEvent() === TrustMateConfigDataEnum::CREATE_SHIPMENT_EVENT
        ) {
            $order = $this->orderRepository->get($orderId);
            $reviewInvitationData = [
                'customer_name' => $order->getCustomerFirstname(),
                'send_to' => $order->getCustomerEmail(),
                'order_number' => $order->getIncrementId(),
                'language' => strstr($this->resolver->getLocale(), '_', true),
                'source_type' => 'magento3.0'
            ];

            $data['body'] = $this->serializerInterface->serialize($reviewInvitationData);
            $storeId = (int) $order->getStoreId();
            $response = $this->reviewInvitation->sendRequest($data, $storeId);
            if (isset($response['status'])) {
                $this->logger->error($response['message']);
            }

            if ($this->configData->isProductOpinionEnabled()) {
                foreach ($order->getItems() as $item) {
                    $product = $item->getProduct();
                    $localId = $this->configData->isFixLocalIdEnabled() ? $product->getId() : $product->getSku();
                    $store = $order->getStore();
                    $gtinCode = $this->configData->getGtinCode();
                    $mpnCode = $this->configData->getMpnCode();
                    $reviewInvitationData['products'][] = [
                        'id' => $localId,
                        'name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'product_url' => $product->getProductUrl(),
                        'category' => $this->category->getCategoriesPath($product->getCategoryIds()),
                        'image_url' => $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                            . 'catalog/product' . $product->getImage(),
                        'image_thumb_url' => $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                            . 'catalog/product' . $product->getThumbnail(),
                        'gtin' => $gtinCode ? $product->getData($gtinCode) : null,
                        'mpn' => $mpnCode ? $product->getData($mpnCode) : null
                    ];
                }

                if ($this->configData->isSandboxEnabled()) {
                    $this->logger->info(print_r($reviewInvitationData, true));
                }
                $data['body'] = $this->serializerInterface->serialize($reviewInvitationData);
                $response = $this->reviewInvitation->sendRequest($data, $storeId);
                if (isset($response['status'])) {
                    $this->logger->error($response['message']);
                }
            }
        }

        return $result;
    }
}
