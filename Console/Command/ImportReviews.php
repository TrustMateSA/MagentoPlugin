<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TrustMate\Opinions\Model\Review as ReviewModel;
use TrustMate\Opinions\Model\Store as StoreModel;
use TrustMate\Opinions\Service\Api\Query;
use TrustMate\Opinions\Service\Review as ReviewService;

/**
 * Class ImportReviews
 * @package TrustMate\Opinions\Console\Command
 */
class ImportReviews extends Command
{
    public const TRANSLATION_OPTION = 'translation';

    /**
     * @var State
     */
    private $state;

    /**
     * @var ReviewModel
     */
    private $reviewModel;

    /**
     * @var ReviewService
     */
    private $reviewService;

    /**
     * @var StoreModel
     */
    private $storeModel;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Query
     */
    private $query;

    /**
     * ImportReviews constructor.
     *
     * @param State                 $state
     * @param ReviewModel           $reviewModel
     * @param ReviewService         $reviewService
     * @param StoreModel            $storeModel
     * @param StoreManagerInterface $storeManager
     * @param Query                 $query
     */
    public function __construct(
        State         $state,
        ReviewModel   $reviewModel,
        ReviewService $reviewService,
        StoreModel    $storeModel,
        StoreManagerInterface $storeManager,
        Query $query
    ) {
        $this->state = $state;
        $this->reviewModel = $reviewModel;
        $this->reviewService = $reviewService;
        $this->storeModel = $storeModel;
        $this->storeManager = $storeManager;
        $this->query = $query;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('trustmate:import:opinions')
            ->setDescription('Download TrustMate opinions (for translation use --translation=true option');
        $this->addOption(
            self::TRANSLATION_OPTION,
            'trans',
            InputOption::VALUE_REQUIRED,
            'Download reviews translation (true or false)'
        );
        parent::configure();
    }

    /**
     * Execute command code
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $output->writeln('<info>Start importing</info>');

        foreach ($this->getAllStores() as $store)
        {
            $storeId = (int)$store->getId();
//            $languageCode = null;
//            $translation = false;
//            if ($input->getOption('translation')) {
//                $languageCode = $this->storeModel->getStoreLocales($storeId);
//                $translation = true;
//            }

            $query = $this->query->prepare($storeId);
            $reviews = $this->reviewModel->getReviewsByApi($query, $storeId);
            foreach ($reviews['items'] as $item) {
                $reviewData = $this->reviewService->prepareDataToSave($item, $storeId);
                $this->reviewModel->save($reviewData);
            }
        }

        $output->writeln('<info>Finish importing</info>');

        return 1;
    }

    /**
     * Get all stores
     *
     * @return array
     */
    private function getAllStores(): array
    {
        return $this->storeManager->getStores();
    }
}
