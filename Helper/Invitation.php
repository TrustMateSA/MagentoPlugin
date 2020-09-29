<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2020 TrustMate
 * @since     1.1.0
 */

namespace TrustMate\Opinions\Helper;

use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory as AgreementCollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use TrustMate\Opinions\Model\Api\Api;

/**
 * Class Invitation
 *
 * @package TrustMate\Opinions
 */
class Invitation extends AbstractHelper
{
    const PLACE_ORDER_EVENT     = 'place_order';
    const CREATE_SHIPMENT_EVENT = 'create_shipment';

    /**
     * Collection factory.
     *
     * @var AgreementCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Api
     */
    protected $api;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Invitation constructor.
     *
     * @param AgreementCollectionFactory $collectionFactory
     * @param Api                        $api
     * @param OrderRepositoryInterface   $orderRepository
     * @param Data                       $helper
     */
    public function __construct(
        AgreementCollectionFactory $collectionFactory,
        Api $api,
        OrderRepositoryInterface $orderRepository,
        Data $helper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->api               = $api;
        $this->orderRepository   = $orderRepository;
        $this->helper            = $helper;
    }

    /**
     * @param Order $order
     */
    public function create(Order $order)
    {
        $shipping   = $order->getShippingAddress();
        $invitation = [
            "send_to"       => $order->getCustomerEmail(),
            "customer_name" => $order->getCustomerFirstname() ?: $shipping->getFirstname(),
            "order_number"  => $order->getIncrementId()
        ];
        $invitation = $this->addMetadata($order, $invitation, $this->mapBoolValue((bool) $order->getCustomerId()));

        $this->createInvitations($invitation, $order);

    }

    /**
     * @param bool $value
     *
     * @return string
     */
    public function mapBoolValue(bool $value)
    {
        return $value ? 'Yes' : 'No';
    }

    /**
     * @return int|null
     */
    public function getTrustmateAgreementId()
    {
        $agreement = $this->collectionFactory->create()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('name', Data::TRUSTMATE_CODE)
            ->getFirstItem();

        return $agreement ? $agreement->getId() : null;
    }

    /**
     * @param OrderInterface $order
     * @param array          $invitation
     * @param string         $logged
     *
     * @return array
     */
    public function addMetadata(OrderInterface $order, array $invitation, string $logged)
    {
        $invitation["metadata"] = [
            [
                'name'  => 'is_logged_in',
                'value' => $logged
            ]
        ];

        if ($payment = $order->getPayment()) {
            $invitation['metadata'][] = [
                'name'  => 'payment_method',
                'value' => $payment->getMethodInstance()->getTitle()
            ];
        }

        if ($shipping = $order->getShippingDescription()) {
            $invitation['metadata'][] = [
                'name'  => 'shipping_method',
                'value' => $shipping
            ];
        }

        $is_from_app = $order->getData('is_from_app');
        if ($is_from_app == '0' || $is_from_app == '1') {
            $invitation['metadata'][] = [
                'name'  => 'is_from_app',
                'value' => $this->mapBoolValue((bool) $is_from_app)
            ];
        }


        if (($shops = $order->getData('shops')) &&
            class_exists(\Otcf\App\Service\StoreLocator\ShopRepository::class)) {
            try {
                $stationaryShopRepository =
                    ObjectManager::getInstance()->get(\Otcf\App\Service\StoreLocator\ShopRepository::class);
                $stationaryShop           = $stationaryShopRepository->getByWmsId($shops);

                $invitation['metadata'][] = [
                    'name'  => 'shop',
                    'value' => $stationaryShop->getName()
                ];
            } catch (NoSuchEntityException $exception) {
            }
        }

        return $invitation;
    }

    /**
     * @param array          $invitation
     * @param OrderInterface $order
     */
    public function createInvitations(array $invitation, OrderInterface $order)
    {
        if ($this->helper->isShopOpinionsEnabled()) {
            $this->api->createInvitation($invitation);
        }

        if ($this->helper->isProductsOpinionsEnabled()) {
            foreach ($order->getItems() as $item) {
                $invitation['products'][] = ["local_id" => $item->getSku()];
            }

            $this->api->createInvitation($invitation);
        }
    }

    /**
     * @return bool
     */
    public function createInvitationAfterPlaceOrder()
    {
        return $this->helper->getCreateInvitationEvent() === static::PLACE_ORDER_EVENT;
    }

    /**
     * @return bool
     */
    public function createInvitationAfterCreateShipment()
    {
        return $this->helper->getCreateInvitationEvent() === static::CREATE_SHIPMENT_EVENT;
    }

    /**
     * @return bool
     */
    public function checkInvitationEnabled()
    {
        return $this->helper->isShopOpinionsEnabled() || $this->helper->isProductsOpinionsEnabled();
    }

    /**
     * @param int              $orderId
     * @param PaymentInterface $paymentMethod
     */
    public function proceedCheckoutInvitation(int $orderId, PaymentInterface $paymentMethod)
    {
        if ($this->checkInvitationEnabled()) {
            $paymentExtAttr       = $paymentMethod->getExtensionAttributes();
            $agreements           = $paymentExtAttr === null ? [] : $paymentExtAttr->getAgreementIds();
            $trustMateAgreementId = $this->getTrustmateAgreementId();

            if (!$this->helper->collectAgreementsWithTrustMate() || in_array($trustMateAgreementId, $agreements)) {
                $order = $this->orderRepository->get($orderId);

                if ($this->createInvitationAfterPlaceOrder()) {
                    $this->create($order);
                } else {
                    // save TrustMate agreement to proceed invitation after create shipment
                    $order->setTrustmateAgreement(1);
                    $this->orderRepository->save($order);
                }
            }
        }
    }

    /**
     * @param Order $order
     */
    public function proceedOrderShipmentInvitation(Order $order)
    {
        if ($order->getTrustmateAgreement()) {
            $this->create($order);
        }
    }
}
