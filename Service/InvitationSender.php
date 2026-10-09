<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2026 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Service;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;
use TrustMate\Opinions\Http\Request\ReviewInvitation;
use TrustMate\Opinions\Model\Config\Data;

class InvitationSender
{
    /**
     * @var Data
     */
    private $configData;

    /**
     * @var Review
     */
    private $reviewService;

    /**
     * @var ReviewInvitation
     */
    private $reviewInvitation;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Data $configData,
        Review $reviewService,
        ReviewInvitation $reviewInvitation,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->configData = $configData;
        $this->reviewService = $reviewService;
        $this->reviewInvitation = $reviewInvitation;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * Send invitation if module is enabled and configured to send it on given event.
     * Never throws - invitation failure must not break placing order or saving shipment.
     *
     * @param OrderInterface $order
     * @param string $event one of TrustMateConfigDataEnum events
     */
    public function send(OrderInterface $order, string $event): void
    {
        try {
            $storeId = (int) $order->getStoreId();

            if (!$this->configData->isModuleEnabled($storeId)
                || $this->configData->getInvitationEvent($storeId) !== $event
            ) {
                return;
            }

            $invitationData = $this->reviewService->prepareInvitationData(
                $order,
                $this->configData->isProductOpinionEnabled($storeId)
            );
            $this->reviewInvitation->sendRequest(
                ['body' => $this->serializer->serialize($invitationData)],
                $storeId
            );
        } catch (\Throwable $exception) {
            $this->logger->error(
                sprintf('Invitation for order %s failed: %s', $order->getIncrementId(), $exception->getMessage()),
                ['exception' => $exception]
            );
        }
    }
}
