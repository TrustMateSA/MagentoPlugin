<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2020 TrustMate
 */

namespace TrustMate\Opinions\Block;

use Magento\Framework\View\Element\Template;
use TrustMate\Opinions\Helper\Data;

/**
 * Class Shop
 * @package TrustMate\Opinions\Block
 */
class Shop extends Template
{
    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        Template\Context $context,
        Data $helper,
        $data = array()
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    public function getShopWidgetLocation()
    {
        return $this->helper->getShopWidgetLocation();
    }

    /**
     * @return string
     */
    public function getWidgetLink()
    {
        return $this->helper->getWidgetLink();
    }

    /**
     * @return string
     */
    public function isWidgetEnabled()
    {
        return $this->helper->isWidgetEnabled();
    }
}
