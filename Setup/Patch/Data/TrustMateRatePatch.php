<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Setup\Patch\Data;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Rating\OptionFactory;
use Magento\Review\Model\ResourceModel\Rating\Option;
use Magento\Store\Model\StoreManagerInterface;

class TrustMateRatePatch implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var RatingFactory
     */
    private $ratingFactory;

    /**
     * @var Option
     */
    private $ratingOptionResource;

    /**
     * @var OptionFactory
     */
    private $ratingOption;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param RatingFactory $ratingFactory
     * @param Option $ratingOptionResource
     * @param OptionFactory $ratingOption
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        RatingFactory            $ratingFactory,
        Option                   $ratingOptionResource,
        OptionFactory            $ratingOption,
        StoreManagerInterface    $storeManager
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->ratingFactory = $ratingFactory;
        $this->ratingOptionResource = $ratingOptionResource;
        $this->storeManager = $storeManager;
        $this->ratingOption = $ratingOption;
    }

    /**
     * @inheirtDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheirtDoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheirtDoc
     *
     * @throws AlreadyExistsException
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $storeIds = array_keys($this->storeManager->getStores(true));
        $rating = $this->ratingFactory->create();
        $rating->setRatingCode('TrustMate')
            ->setStores($storeIds)
            ->setIsActive(1)
            ->setEntityId(1)
            ->save();

        foreach (range(1, 5) as $optionCode) {
            $ratingOption = $this->ratingOption->create()->setCode($optionCode)
                ->setValue($optionCode)
                ->setRatingId($rating->getId())
                ->setPosition($optionCode);

            $this->ratingOptionResource->save($ratingOption);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }
}
