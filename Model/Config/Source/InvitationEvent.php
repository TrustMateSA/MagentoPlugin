<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 * @since     1.1.0
 */

namespace TrustMate\Opinions\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use TrustMate\Opinions\Enum\TrustMateConfigDataEnum;

/**
 * Class InvitationEvent
 * @package TrustMate
 */
class InvitationEvent implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $arr     = $this->toArray();
        $options = array();
        foreach ($arr as $key => $value) {
            $options[] = array(
                'value' => $key,
                'label' => $value
            );
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            TrustMateConfigDataEnum::PLACE_ORDER_EVENT => __('After place order'),
            TrustMateConfigDataEnum::CREATE_SHIPMENT_EVENT => __('After create shipment')
        ];
    }
}
