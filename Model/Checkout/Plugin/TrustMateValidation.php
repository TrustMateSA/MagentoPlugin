<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2020 TrustMate
 */

namespace TrustMate\Opinions\Model\Checkout\Plugin;

use Closure;
use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use TrustMate\Opinions\Helper\Invitation;

/**
 * Class TrustMateValidation
 *
 * @package TrustMate\Opinions\Model\Checkout\Plugin
 */
class TrustMateValidation
{
    /**
     * @var Invitation
     */
    protected $helperInvitation;

    /**
     * TrustMateValidation constructor.
     *
     * @param Invitation $helperInvitation
     */
    public function __construct(
        Invitation $helperInvitation
    ) {
        $this->helperInvitation = $helperInvitation;
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
        $this->helperInvitation->create($paymentMethod, $orderId, true);

        return $orderId;
    }
}
