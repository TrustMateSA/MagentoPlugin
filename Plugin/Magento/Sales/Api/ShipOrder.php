<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Plugin\Magento\Sales\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipOrderInterface;
use Psr\Log\LoggerInterface;
use TrustMate\Opinions\Enum\TrustMateConfigDataEnum;
use TrustMate\Opinions\Http\Request\ReviewInvitation;
use TrustMate\Opinions\Model\Category;
use TrustMate\Opinions\Model\Config\Data;
use TrustMate\Opinions\Service\Review;

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
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Review
     */
    private $reviewService;

    /**
     * @param Data $configData
     * @param SerializerInterface $serializerInterface
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param ReviewInvitation $reviewInvitation
     * @param Review $reviewService
     */
    public function __construct(
        Data $configData,
        SerializerInterface $serializerInterface,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        ReviewInvitation $reviewInvitation,
        Review $reviewService
    ) {
        $this->configData = $configData;
        $this->serializerInterface = $serializerInterface;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->reviewInvitation = $reviewInvitation;
        $this->reviewService = $reviewService;
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
        $order = $this->orderRepository->get($orderId);
        $storeId = (int) $order->getStoreId();

        if ($this->configData->isModuleEnabled($storeId)
            && $this->configData->getInvitationEvent($storeId) === TrustMateConfigDataEnum::CREATE_SHIPMENT_EVENT
        ) {
            $isProductInvitationEnabled = $this->configData->isProductOpinionEnabled($storeId);
            $reviewInvitationData = $this->reviewService->prepareInvitationData($order, $isProductInvitationEnabled);
            $data['body'] = $this->serializerInterface->serialize($reviewInvitationData);
            $response = $this->reviewInvitation->sendRequest($data, $storeId);

            if (isset($response['status'])) {
                $this->logger->error($response['message']);
            }
        }

        return $result;
    }
}
