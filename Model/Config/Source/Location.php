<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2020 TrustMate
 */

namespace TrustMate\Opinions\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Location
 * @package TrustMate
 */
class Location implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
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
    public function toArray()
    {
        $status = array(
            'top-left' => __('Left Top (1)'),
            'left-m' => __('Left Middle (2)'),
            'bottom-left' => __('Left Bottom (3)'),
            'top-m' => __('Middle Top (4)'),
            'bottom-m' => __('Middle Bottom (5)'),
            'top-right' => __('Right Top (6)'),
            'right-m' => __('Right Middle (7)'),
            'bottom-right' => __('Right Bottom (8)')
        );

        return $status;
    }
}
