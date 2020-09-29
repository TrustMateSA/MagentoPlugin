<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2020 TrustMate
 */

namespace TrustMate\Opinions\Model\Checkout\Plugin;

use Magento\Checkout\Model\GuestPaymentInformationManagement;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use TrustMate\Opinions\Helper\Invitation;

/**
 * Class TrustMateValidationGuest
 *
 * @package TrustMate\Opinions\Model\Checkout\Plugin
 */
class TrustMateValidationGuest
{
    /**
     * @var Invitation
     */
    protected $helperInvitation;

    /**
     * TrustMateValidationGuest constructor.
     *
     * @param Invitation $helper
     */
    public function __construct(
        Invitation $helperInvitation
    ) {
        $this->helperInvitation = $helperInvitation;
    }

    /**
     * @param GuestPaymentInformationManagement $subject
     * @param \Closure                          $proceed
     * @param                                   $cartId
     * @param                                   $email
     * @param PaymentInterface                  $paymentMethod
     * @param AddressInterface|null             $billingAddress
     *
     * @return mixed
     */
    public function aroundSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $orderId = $proceed($cartId, $email, $paymentMethod, $billingAddress);
        $this->helperInvitation->proceedCheckoutInvitation($orderId, $paymentMethod);

        return $orderId;
    }
}
