<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 * @since     1.1.0
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Helper\Data as SalesData;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save as MagentoShippingSave;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Psr\Log\LoggerInterface;
use TrustMate\Opinions\Enum\TrustMateConfigDataEnum;
use TrustMate\Opinions\Http\Request\ReviewInvitation;
use TrustMate\Opinions\Model\Category;
use TrustMate\Opinions\Model\Config\Data;

class Save extends MagentoShippingSave
{
    /**
     * @var ReviewInvitation
     */
    protected $reviewInvitation;

    /**
     * @var Data
     */
    protected $configData;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * @var SerializerInterface
     */
    protected $serializerInterface;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Context $context,
        ShipmentLoader $shipmentLoader,
        LabelGenerator $labelGenerator,
        ShipmentSender $shipmentSender,
        ReviewInvitation $reviewInvitation,
        Data $configData,
        Category $category,
        Resolver $resolver,
        SerializerInterface $serializerInterface,
        LoggerInterface $logger,
        ShipmentValidatorInterface $shipmentValidator = null,
        SalesData $salesData = null
    ) {
        $this->reviewInvitation = $reviewInvitation;
        $this->configData = $configData;
        $this->category = $category;
        $this->resolver = $resolver;
        $this->serializerInterface = $serializerInterface;
        $this->logger = $logger;

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
     *
     * @throws NoSuchEntityException
     */
    protected function _saveShipment($shipment)
    {
        if ($this->configData->isModuleEnabled()
            && $this->configData->getInvitationEvent() === TrustMateConfigDataEnum::CREATE_SHIPMENT_EVENT
        ) {
            $order = $shipment->getOrder();
            $storeId = (int) $order->getStoreId();
            $invitationData = [
                'customer_name' => $order->getCustomerFirstname(),
                'send_to' => $order->getCustomerEmail(),
                'order_number' => $order->getIncrementId(),
                'language' => strstr($this->resolver->getLocale(), '_', true),
                'source_type' => 'magento3.0'
            ];

            $data['body'] = $this->serializerInterface->serialize($invitationData);
            $response = $this->reviewInvitation->sendRequest($data, $storeId);
            if (isset($response['status'])) {
                $this->logger->error($response['message']);
            }

            if ($this->configData->isProductOpinionEnabled()) {
                foreach ($order->getAllVisibleItems() as $item) {
                    $product = $item->getProduct();
                    $localId = $this->configData->isFixLocalIdEnabled() ? $product->getId() : $product->getSku();
                    $store = $order->getStore();
                    $gtinCode = $this->configData->getGtinCode();
                    $mpnCode  = $this->configData->getMpnCode();
                    $invitationData['products'][$product->getSku()] = [
                        'id' => $localId,
                        'name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'product_url' => $product->getProductUrl(),
                        'category' => $this->category->getCategoriesPath($product->getCategoryIds()),
                        'image_url' => $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                            . 'catalog/product' . $product->getImage(),
                        'image_thumb_url' => $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                            . 'catalog/product' . $product->getThumbnail(),
                        'gtin' => $gtinCode ? $product->getData($gtinCode) : null,
                        'mpn' => $mpnCode ? $product->getData($mpnCode) : null
                    ];
                }

                if ($this->configData->isSandboxEnabled()) {
                    $this->logger->info(print_r($invitationData, true));
                }

                $data['body'] = $this->serializerInterface->serialize($invitationData);
                $response = $this->reviewInvitation->sendRequest($data, $storeId);
                if (isset($response['status'])) {
                    $this->logger->error($response['message']);
                }
            }
        }

        parent::_saveShipment($shipment);
    }
}
