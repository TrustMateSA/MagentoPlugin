<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2019 TrustMate
 */

namespace TrustMate\Opinions\Model;

use Magento\CheckoutAgreements\Model\AgreementsProvider as CoreAgreementsProvider;
use Magento\Store\Model\ScopeInterface;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory as AgreementCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CheckoutAgreements\Model\AgreementModeOptions;
use TrustMate\Opinions\Helper\Data;

/**
 * Class AgreementsProvider
 * @package TrustMate\Opinions\Model
 */
class AgreementsProvider extends CoreAgreementsProvider
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * AgreementsProvider constructor.
     * @param AgreementCollectionFactory $agreementCollectionFactory
     * @param StoreManagerInterface      $storeManager
     * @param ScopeConfigInterface       $scopeConfig
     * @param Data                       $helper
     */
    public function __construct(
        AgreementCollectionFactory $agreementCollectionFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Data $helper
    ) {
        parent::__construct($agreementCollectionFactory, $storeManager, $scopeConfig);
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function getRequiredAgreementIds()
    {
        $agreementIds = [];
        if ($this->scopeConfig->isSetFlag(self::PATH_ENABLED, ScopeInterface::SCOPE_STORE)) {
            $agreementCollection = $this->agreementCollectionFactory->create();
            $agreementCollection->addStoreFilter($this->storeManager->getStore()->getId());
            $agreementCollection->addFieldToFilter('is_active', 1);
            $agreementCollection->addFieldToFilter('mode', AgreementModeOptions::MODE_MANUAL);
            $agreementCollection->addFieldToFilter('name', array('neq' => Data::TRUSTMATE_CODE));
            $agreementIds = $agreementCollection->getAllIds();
        }
        return $agreementIds;
    }
}