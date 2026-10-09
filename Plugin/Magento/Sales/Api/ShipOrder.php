<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Plugin\Magento\Sales\Api;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipOrderInterface;
use TrustMate\Opinions\Enum\TrustMateConfigDataEnum;
use TrustMate\Opinions\Service\InvitationSender;

class ShipOrder
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var InvitationSender
     */
    private $invitationSender;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param InvitationSender $invitationSender
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        InvitationSender $invitationSender
    ) {
        $this->orderRepository = $orderRepository;
        $this->invitationSender = $invitationSender;
    }

    /**
     * @param ShipOrderInterface $subject
     * @param int|null $result
     * @param int $orderId
     *
     * @return int|null
     */
    public function afterExecute(ShipOrderInterface $subject, ?int $result, int $orderId): ?int
    {
        // Order was already loaded through repository by ShipOrder::execute(), so this hits repository cache
        $order = $this->orderRepository->get($orderId);
        $this->invitationSender->send($order, TrustMateConfigDataEnum::CREATE_SHIPMENT_EVENT);

        return $result;
    }
}
