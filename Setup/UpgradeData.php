<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2020 TrustMate
 */

namespace TrustMate\Opinions\Setup;

use Magento\CheckoutAgreements\Model\AgreementFactory;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Review\Model\RatingFactory as Rating;
use Magento\Review\Model\Rating\OptionFactory as RatingOption;
use Magento\Store\Model\StoreManagerInterface;
use TrustMate\Opinions\Helper\Data;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var AgreementFactory
     */
    protected $agreement;

    /**
     * @var CheckoutAgreementsRepositoryInterface
     */
    protected $agreementRepository;

    /**
     * @var Rating
     */
    protected $rating;

    /**
     * @var RatingOption
     */
    protected $ratingOption;

    /**
     * @var State
     */
    protected $appState;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * UpgradeData constructor.
     * @param AgreementFactory                      $agreement
     * @param CheckoutAgreementsRepositoryInterface $agreementRepository
     * @param Rating                                $rating
     * @param RatingOption                          $ratingOption
     * @param State                                 $appState
     * @param StoreManagerInterface                 $storeManager
     */
    public function __construct(
        AgreementFactory $agreement,
        CheckoutAgreementsRepositoryInterface $agreementRepository,
        Rating $rating,
        RatingOption $ratingOption,
        State $appState,
        StoreManagerInterface $storeManager
    ) {
        $this->agreement = $agreement;
        $this->agreementRepository = $agreementRepository;
        $this->rating = $rating;
        $this->ratingOption = $ratingOption;
        $this->appState = $appState;
        $this->storeManager = $storeManager;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     * @throws \Exception
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->addTrustMateAgreement();
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $this->addTrustMateRating();
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $this->addIdentifierToTrustMateTable($setup);
        }
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addTrustMateAgreement()
    {
        $agreement = $this->agreement->create();
        $agreement->setName(Data::TRUSTMATE_CODE)
            ->setIsActive(true)
            ->setIsHtml(1)
            ->setMode(1)
            ->setCheckboxText('Wyrażam zgodę na otrzymanie ankiety do wystawienia opinii o zakupionym produkcie...')
            ->setContent('Wyrażam zgodę na otrzymanie ankiety do wystawienia opinii o zakupionym produkcie w Programie Opinii Sklepu Internetowego na mój adres email. Wystawienie opinii jest dobrowolne. <a href="https://trustmate.io/regulamin-uzytkownika" target="_blank">Regulamin</a>')
        ;

        $this->agreementRepository->save($agreement, 0);
    }

    /**
     * Create new Rating attribute
     *
     * @throws \Exception
     */
    public function addTrustMateRating()
    {
        $rating = $this->rating->create();

        $rating->setRatingCode(Data::TRUSTMATE_CODE)
            ->setStores($this->getStoreIds())
            ->setIsActive(1)
            ->setEntityId(1)
            ->save();

        foreach (range(1,5) as $optionCode) {
            $this->ratingOption->create()->setCode($optionCode)
                ->setValue($optionCode)
                ->setRatingId($rating->getId())
                ->setPosition($optionCode)
                ->save();
        }
    }

    /**
     * @return array
     */
    private function getStoreIds() {
        $stores = $this->storeManager->getStores();
        $ids = array();

        foreach ($stores as $store) {
            $ids[] = $store->getId();
        }

        return $ids;
    }

    /**
     * @param $setup
     */
    public function addIdentifierToTrustMateTable($setup)
    {
        $setup->getConnection()
            ->addColumn(
                InstallSchema::PRODUCT_OPINIONS_TABLE,
                'public_identifier',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Unique identifier'
                ]
            );
    }
}
