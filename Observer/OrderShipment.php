<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2020 TrustMate
 * @since     1.1.0
 */

namespace TrustMate\Opinions\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use TrustMate\Opinions\Helper\Invitation;

/**
 * Class UpdateOpinionsObserver
 */
class OrderShipment implements ObserverInterface
{
    /**
     * @var Invitation
     */
    protected $helperInvitation;

    /**
     * OrderShipment constructor.
     *
     * @param Invitation $helperInvitation
     */
    public function __construct(
        Invitation $helperInvitation
    ) {
        $this->helperInvitation = $helperInvitation;
    }

    /**
     * Create TrustMate invitation
     *
     * @param EventObserver $observer
     *
     * @return $this|void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->helperInvitation->checkInvitationEnabled() && $this->helperInvitation->createInvitationAfterCreateShipment()) {
            $this->helperInvitation->proceedOrderShipmentInvitation($observer->getEvent()->getShipment()->getOrder());
        }

        return $this;
    }
}
