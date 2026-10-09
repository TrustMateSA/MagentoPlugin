<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 * @since     1.1.0
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action\Context;
use Magento\Sales\Helper\Data as SalesData;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save as MagentoShippingSave;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use TrustMate\Opinions\Enum\TrustMateConfigDataEnum;
use TrustMate\Opinions\Service\InvitationSender;

class Save extends MagentoShippingSave
{
    /**
     * @var InvitationSender
     */
    protected $invitationSender;

    public function __construct(
        Context $context,
        ShipmentLoader $shipmentLoader,
        LabelGenerator $labelGenerator,
        ShipmentSender $shipmentSender,
        InvitationSender $invitationSender,
        ShipmentValidatorInterface $shipmentValidator = null,
        SalesData $salesData = null
    ) {
        $this->invitationSender = $invitationSender;

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
     */
    protected function _saveShipment($shipment)
    {
        // Send invitation only for a shipment that was actually saved
        $result = parent::_saveShipment($shipment);
        $this->invitationSender->send($shipment->getOrder(), TrustMateConfigDataEnum::CREATE_SHIPMENT_EVENT);

        return $result;
    }
}
