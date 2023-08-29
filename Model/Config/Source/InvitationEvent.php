<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 * @since     1.1.0
 */

namespace TrustMate\Opinions\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use TrustMate\Opinions\Enum\TrustMateConfigDataEnum;

/**
 * Class InvitationEvent
 * @package TrustMate
 */
class InvitationEvent implements OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => TrustMateConfigDataEnum::PLACE_ORDER_EVENT,
                'label' => __('After place order')
            ],
            [
                'value' => TrustMateConfigDataEnum::CREATE_SHIPMENT_EVENT ,
                'label' => __('After create shipment')
            ]
        ];
    }
}
