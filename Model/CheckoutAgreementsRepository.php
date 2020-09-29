<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2020 TrustMate
 */

namespace TrustMate\Opinions\Model;

use Magento\CheckoutAgreements\Model\CheckoutAgreementsRepository as CoreCheckoutAgreementsRepository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory as AgreementCollectionFactory;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection as AgreementCollection;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement as AgreementResource;
use Magento\CheckoutAgreements\Model\AgreementFactory;
use TrustMate\Opinions\Helper\Data;

/**
 * Class CheckoutAgreementsRepository
 * @package TrustMate\Opinions\Model
 */
class CheckoutAgreementsRepository extends CoreCheckoutAgreementsRepository
{
    /**
     * Collection factory.
     *
     * @var AgreementCollectionFactory
     */
    protected $collectionFactory;

    /**
     * Scope config.
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * Store manager.
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * CheckoutAgreementsRepository constructor.
     * @param AgreementCollectionFactory $collectionFactory
     * @param StoreManagerInterface      $storeManager
     * @param ScopeConfigInterface       $scopeConfig
     * @param AgreementResource          $agreementResource
     * @param AgreementFactory           $agreementFactory
     * @param JoinProcessorInterface     $extensionAttributesJoinProcessor
     * @param Data                       $helper
     */
    public function __construct(
        AgreementCollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        AgreementResource $agreementResource,
        AgreementFactory $agreementFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        Data $helper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->helper = $helper;

        parent::__construct(
            $collectionFactory,
            $storeManager,
            $scopeConfig,
            $agreementResource,
            $agreementFactory,
            $extensionAttributesJoinProcessor
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\CheckoutAgreements\Api\Data\AgreementInterface[] Array of checkout agreement data objects.
     */
    public function getList()
    {
        if (!$this->scopeConfig->isSetFlag('checkout/options/enable_agreements', ScopeInterface::SCOPE_STORE)) {
            return [];
        }
        $storeId = $this->storeManager->getStore()->getId();
        /** @var $agreementCollection AgreementCollection */
        $agreementCollection = $this->collectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($agreementCollection);
        $agreementCollection->addStoreFilter($storeId);
        $agreementCollection->addFieldToFilter('is_active', 1);

        if (!$this->helper->collectAgreementsWithTrustMate()) {
            $agreementCollection->addFieldToFilter('name', array('neq' => Data::TRUSTMATE_CODE));
        }

        $agreementDataObjects = [];
        foreach ($agreementCollection as $agreement) {
            $agreementDataObjects[] = $agreement;
        }

        return $agreementDataObjects;
    }
}