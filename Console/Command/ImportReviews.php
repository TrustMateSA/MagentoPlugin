<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2020 TrustMate
 */

namespace TrustMate\Opinions\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TrustMate\Opinions\Cron\DownloadOpinions;

/**
 * Class ImportReviews
 * @package TrustMate\Opinions\Console\Command
 */
class ImportReviews extends Command
{
    /**
     * @var DownloadOpinions
     */
    protected $downloadOpinions;

    /**
     * @var State
     */
    protected $state;

    /**
     * ImportReviews constructor.
     * @param State            $state
     * @param DownloadOpinions $downloadOpinions
     */
    public function __construct(
        State $state,
        DownloadOpinions $downloadOpinions
    ) {
        $this->downloadOpinions = $downloadOpinions;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('trustmate:import:opinions')
            ->setDescription('Download TrustMate opinions');
        parent::configure();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);

        $output->writeln('<info>Start importing</info>');

        $this->downloadOpinions->execute();

        $output->writeln('<info>Finish importing</info>');
    }
}
