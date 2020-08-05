<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2019 TrustMate
 */

namespace TrustMate\Opinions\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package TrustMate\Opinions
 */
class Data extends AbstractHelper
{
    const SCOPE_STORE = ScopeInterface::SCOPE_STORE;

    const TRUSTMATE_CODE                             = 'TrustMate';
    const OPINION_TITLE                              = 'Opinia z TrustMate';
    const WIDGET_LINK                                = 'https://trustmate.io/widget/modal/review_button/';
    const XML_PATH_UUID                              = 'trustmate_opinions_section/general/uuid';
    const XML_API_KEY                                = 'trustmate_opinions_section/general/api_key';
    const XML_PATH_MODULE_ENABLED                    = 'trustmate_opinions_section/general/module_enabled';
    const XML_PATH_COLLECT_AGREEMENTS_WITH_TRUSTMATE = 'trustmate_opinions_section/general/collect_agreements_with_trustmate';
    const XML_PATH_WIDGET_ENABLED                    = 'trustmate_opinions_section/general/widget_enabled';
    const XML_PATH_SHOP_OPINIONS_LOCATION            = 'trustmate_opinions_section/general/shop_widget_location';
    const XML_PATH_PRODUCTS_OPINIONS_ENABLED         = 'trustmate_opinions_section/general/products_opinions_enabled';
    const XML_STORE_ID                               = 'trustmate_opinions_section/general/store_id';

    /**
     * @return bool
     */
    public function isShopOpinionsEnabled()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_MODULE_ENABLED, static::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_UUID, static::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->scopeConfig->getValue(static::XML_API_KEY, static::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isWidgetEnabled()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_WIDGET_ENABLED, static::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isProductsOpinionsEnabled()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_PRODUCTS_OPINIONS_ENABLED, static::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function collectAgreementsWithTrustMate()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_COLLECT_AGREEMENTS_WITH_TRUSTMATE, static::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getShopWidgetLocation()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_SHOP_OPINIONS_LOCATION, static::SCOPE_STORE);
    }

    /**
     * @return bool|string
     */
    public function getWidgetLink()
    {
        return $this->getUuid() ? static::WIDGET_LINK . $this->getUuid() : false;
    }

    /**
     * @return string
     */
    public function getOpinionsStoreId()
    {
        return $this->scopeConfig->getValue(static::XML_STORE_ID, static::SCOPE_STORE);
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

        if ($isFromApp = $order->getData('is_from_app')) {
            $invitation['metadata'][] = [
                'name'  => 'is_from_app',
                'value' => 'Yes'
            ];
        }


        if (($shops = $order->getData('shops')) &&
            class_exists(\Otcf\App\Service\StoreLocator\ShopRepository::class)) {
            try {
                $stationaryShopRepository = ObjectManager::getInstance()->get(\Otcf\App\Service\StoreLocator\ShopRepository::class);
                $stationaryShop = $stationaryShopRepository->getByWmsId($shops);

                $invitation['metadata'][] = [
                    'name'  => 'shop',
                    'value' => $stationaryShop->getName()
                ];
            } catch (NoSuchEntityException $exception) {
            }
        }

        return $invitation;
    }
}
