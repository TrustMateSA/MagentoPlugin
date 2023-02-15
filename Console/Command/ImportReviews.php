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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TrustMate\Opinions\Model\Review as ReviewModel;
use TrustMate\Opinions\Model\Store as StoreModel;
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
    protected $state;

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
     * ImportReviews constructor.
     *
     * @param State $state
     * @param ReviewModel $reviewModel
     * @param ReviewService $reviewService
     * @param StoreModel $storeModel
     */
    public function __construct(
        State         $state,
        ReviewModel   $reviewModel,
        ReviewService $reviewService,
        StoreModel    $storeModel
    ) {
        $this->state = $state;
        $this->reviewModel = $reviewModel;
        $this->reviewService = $reviewService;
        $this->storeModel = $storeModel;
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
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $output->writeln('<info>Start importing</info>');
        $data['query'] = [
            "start" => $this->reviewModel->getLatestUpdatedDate(),
            'per_page' => 1000,
            'page' => 1,
            'sort' => 'updatedAt'
        ];

        $this->reviewService->add($data);

        if ($input->getOption('translation')) {
            foreach ($this->storeModel->getStoreLocales() as $languageCode => $storeId) {
                $data['query']['start'] = $this->reviewModel->getLatestUpdatedDate(true);
                $data['query']['language'] = $languageCode;
                $this->reviewService->addTranslation($data);
            }
        }

        $output->writeln('<info>Finish importing</info>');
    }
}
