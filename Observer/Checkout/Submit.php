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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use TrustMate\Opinions\Enum\TrustMateConfigDataEnum;
use TrustMate\Opinions\Http\Request\ReviewInvitation;
use TrustMate\Opinions\Model\Config\Data;
use TrustMate\Opinions\Service\Review;

class Submit implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $configData;

    /**
     * @var ReviewInvitation
     */
    protected $reviewInvitation;

    /**
     * @var Review
     */
    protected $reviewService;

    /**
     * @var SerializerInterface
     */
    protected $serializerInterface;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Data $configData
     * @param ReviewInvitation $reviewInvitation
     * @param SerializerInterface $serializerInterface
     * @param Review $reviewService
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $configData,
        ReviewInvitation $reviewInvitation,
        SerializerInterface $serializerInterface,
        Review $reviewService,
        LoggerInterface $logger
    ) {
        $this->configData = $configData;
        $this->reviewInvitation = $reviewInvitation;
        $this->reviewService = $reviewService;
        $this->serializerInterface = $serializerInterface;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $storeId = (int) $order->getStoreId();

        if ($this->configData->isModuleEnabled($storeId)
            && $this->configData->getInvitationEvent($storeId) === TrustMateConfigDataEnum::PLACE_ORDER_EVENT
        ) {
            $isProductInvitationEnabled = $this->configData->isProductOpinionEnabled($storeId);
            $invitationData = $this->reviewService->prepareInvitationData($order, $isProductInvitationEnabled);
            $data['body'] = $this->serializerInterface->serialize($invitationData);
            $response = $this->reviewInvitation->sendRequest($data, $storeId);

            if (isset($response['status'])) {
                $this->logger->error($response['message']);
            }
        }
    }
}
