<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 * @since     1.1.0
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Helper\Data as SalesData;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save as MagentoShippingSave;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Psr\Log\LoggerInterface;
use TrustMate\Opinions\Enum\TrustMateConfigDataEnum;
use TrustMate\Opinions\Http\Request\ReviewInvitation;
use TrustMate\Opinions\Model\Category;
use TrustMate\Opinions\Model\Config\Data;
use TrustMate\Opinions\Service\Review;

class Save extends MagentoShippingSave
{
    /**
     * @var ReviewInvitation
     */
    protected $reviewInvitation;

    /**
     * @var Data
     */
    protected $configData;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * @var SerializerInterface
     */
    protected $serializerInterface;

    /**
     * @var Review
     */
    protected $reviewService;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Context $context,
        ShipmentLoader $shipmentLoader,
        LabelGenerator $labelGenerator,
        ShipmentSender $shipmentSender,
        ReviewInvitation $reviewInvitation,
        Data $configData,
        Category $category,
        Resolver $resolver,
        SerializerInterface $serializerInterface,
        Review $reviewService,
        LoggerInterface $logger,
        ShipmentValidatorInterface $shipmentValidator = null,
        SalesData $salesData = null
    ) {
        $this->reviewInvitation = $reviewInvitation;
        $this->configData = $configData;
        $this->category = $category;
        $this->resolver = $resolver;
        $this->serializerInterface = $serializerInterface;
        $this->reviewService = $reviewService;
        $this->logger = $logger;

        parent::__construct(
            $context,
            $shipmentLoader,
            $labelGenerator,
            $shipmentSender,
            $shipmentValidator,
            $salesData
        );
    }

    /**
     * @inheritDoc
     *
     * @throws NoSuchEntityException
     */
    protected function _saveShipment($shipment)
    {
        $order = $shipment->getOrder();
        $storeId = (int) $order->getStoreId();

        if ($this->configData->isModuleEnabled($storeId)
            && $this->configData->getInvitationEvent($storeId) === TrustMateConfigDataEnum::CREATE_SHIPMENT_EVENT
        ) {
            $isProductInvitationEnabled = $this->configData->isProductOpinionEnabled($storeId);
            $invitationData = $this->reviewService->prepareInvitationData($order, $isProductInvitationEnabled);
            $data['body'] = $this->serializerInterface->serialize($invitationData);
            $response = $this->reviewInvitation->sendRequest($data, $storeId);
            if (isset($response['status'])) {
                $this->logger->error($response['message']);
            }
        }

        parent::_saveShipment($shipment);
    }
}
