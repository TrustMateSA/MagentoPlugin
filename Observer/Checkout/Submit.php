<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 * @since     1.1.0
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Observer\Checkout;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use TrustMate\Opinions\Enum\TrustMateConfigDataEnum;
use TrustMate\Opinions\Http\Request\ReviewInvitation;
use TrustMate\Opinions\Model\Category;
use TrustMate\Opinions\Model\Config\Data;

class Submit implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $configData;

    /**
     * @var ReviewInvitation
     */
    protected $reviewInvitation;

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
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Data                  $configData
     * @param ReviewInvitation      $reviewInvitation
     * @param Category              $category
     * @param Resolver              $resolver
     * @param SerializerInterface   $serializerInterface
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface       $logger
     */
    public function __construct(
        Data                  $configData,
        ReviewInvitation      $reviewInvitation,
        Category              $category,
        Resolver              $resolver,
        SerializerInterface   $serializerInterface,
        StoreManagerInterface $storeManager,
        LoggerInterface       $logger
    ) {
        $this->configData          = $configData;
        $this->reviewInvitation    = $reviewInvitation;
        $this->category            = $category;
        $this->resolver            = $resolver;
        $this->serializerInterface = $serializerInterface;
        $this->storeManager        = $storeManager;
        $this->logger              = $logger;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if ($this->configData->isModuleEnabled()
            && $this->configData->getInvitationEvent() === TrustMateConfigDataEnum::PLACE_ORDER_EVENT
        ) {
            $order          = $observer->getEvent()->getOrder();
            $storeId = (int)$this->storeManager->getStore()->getId();
            $invitationData = [
                'customer_name' => $order->getCustomerFirstname(),
                'send_to' => $order->getCustomerEmail(),
                'order_number' => $order->getIncrementId(),
                'language' => strstr($this->resolver->getLocale(), '_', true),
                'source_type' => 'magento3.0'
            ];

            $data['body'] = $this->serializerInterface->serialize($invitationData);
            $response     = $this->reviewInvitation->sendRequest($data, $storeId);
            if (isset($response['status'])) {
                $this->logger->error($response['message']);
            }

            if ($this->configData->isProductOpinionEnabled()) {
                foreach ($order->getItems() as $item) {
                    $product  = $item->getProduct();
                    $localId = $this->configData->isFixLocalIdEnabled() ? $product->getId() : $product->getSku();
                    $store    = $this->storeManager->getStore();
                    $gtinCode = $this->configData->getGtinCode();
                    $mpnCode  = $this->configData->getMpnCode();

                    $invitationData['products'][] = [
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
                $response     = $this->reviewInvitation->sendRequest($data, $storeId);
                if (isset($response['status'])) {
                    $this->logger->error($response['message']);
                }
            }
        }
    }
}
