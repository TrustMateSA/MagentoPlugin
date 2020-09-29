<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2019 TrustMate
 */

namespace TrustMate\Opinions\Model\Checkout\Plugin;

use Closure;
use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory as AgreementCollectionFactory;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use TrustMate\Opinions\Helper\Data;
use TrustMate\Opinions\Model\Api\Api;

/**
 * Class TrustMateValidation
 *
 * @package TrustMate\Opinions\Model\Checkout\Plugin
 */
class TrustMateValidation
{
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
     * TrustMateValidationGuest constructor.
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
     * @param PaymentInformationManagement $subject
     * @param Closure                      $proceed
     * @param                              $cartId
     * @param PaymentInterface             $paymentMethod
     * @param AddressInterface|null        $billingAddress
     *
     * @return mixed
     */
    public function aroundSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagement $subject,
        Closure $proceed,
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $orderId = $proceed($cartId, $paymentMethod, $billingAddress);

        if ($this->helper->isShopOpinionsEnabled() || $this->helper->isProductsOpinionsEnabled()) {
            $agreements = $paymentMethod->getExtensionAttributes() === null ? [] :
                $paymentMethod->getExtensionAttributes()->getAgreementIds();
            $agreementsList = $this->collectionFactory->create()
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('name', Data::TRUSTMATE_CODE);
            $trustMateAgreementId = $agreementsList->getFirstItem()->getId();
            $collectAgreementsWithTrustMate = $this->helper->collectAgreementsWithTrustMate();

            if (!$collectAgreementsWithTrustMate || in_array($trustMateAgreementId, $agreements)) {
                $order = $this->orderRepository->get($orderId);

                $invitation = [
                    "send_to"       => $order->getCustomerEmail(),
                    "customer_name" => $order->getCustomerFirstname(),
                    "order_number"  => $order->getIncrementId(),
                ];

                $invitation = $this->helper->addMetadata($order, $invitation, 'Yes');

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
        }

        return $orderId;
    }
}
