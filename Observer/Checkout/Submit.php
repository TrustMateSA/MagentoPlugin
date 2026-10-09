<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 * @since     1.1.0
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Observer\Checkout;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use TrustMate\Opinions\Enum\TrustMateConfigDataEnum;
use TrustMate\Opinions\Service\InvitationSender;

class Submit implements ObserverInterface
{
    /**
     * @var InvitationSender
     */
    protected $invitationSender;

    /**
     * @param InvitationSender $invitationSender
     */
    public function __construct(
        InvitationSender $invitationSender
    ) {
        $this->invitationSender = $invitationSender;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        // Multishipping dispatches this event with 'orders' instead of 'order'
        if (!$order) {
            return;
        }

        $this->invitationSender->send($order, TrustMateConfigDataEnum::PLACE_ORDER_EVENT);
    }
}
