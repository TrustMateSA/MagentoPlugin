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
use Magento\Review\Model\Rating;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Rating\OptionFactory;
use Magento\Review\Model\ResourceModel\Rating as RatingResourceModel;
use Magento\Review\Model\ResourceModel\Rating\Option;
use Magento\Store\Model\StoreManagerInterface;
use TrustMate\Opinions\Enum\ReviewDataEnum;

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
     * @var RatingResourceModel
     */
    private $ratingResource;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param RatingFactory            $ratingFactory
     * @param RatingResourceModel      $ratingResource
     * @param Option                   $ratingOptionResource
     * @param OptionFactory            $ratingOption
     * @param StoreManagerInterface    $storeManager
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        RatingFactory            $ratingFactory,
        RatingResourceModel      $ratingResource,
        Option                   $ratingOptionResource,
        OptionFactory            $ratingOption,
        StoreManagerInterface    $storeManager
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->ratingFactory = $ratingFactory;
        $this->ratingResource = $ratingResource;
        $this->ratingOptionResource = $ratingOptionResource;
        $this->storeManager = $storeManager;
        $this->ratingOption = $ratingOption;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     *
     * @throws AlreadyExistsException
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $storeIds = array_keys($this->storeManager->getStores(true));

        /**
         * @var Rating $rating
         */
        $rating = $this->ratingFactory->create();
        $this->ratingResource->load(
            $rating,
            ReviewDataEnum::TRUSTMATE_RATING_CODE,
            ReviewDataEnum::RATING_CODE_COLUMN
        );

        if (!$rating->getId()) {
            $rating->setRatingCode(ReviewDataEnum::TRUSTMATE_RATING_CODE)
                ->setStores($storeIds)
                ->setIsActive(1)
                ->setEntityId(1);

            $this->ratingResource->save($rating);

            foreach (range(1, 5) as $optionCode) {
                $ratingOption = $this->ratingOption->create()
                    ->setCode($optionCode)
                    ->setValue($optionCode)
                    ->setRatingId($rating->getId())
                    ->setPosition($optionCode);

                $this->ratingOptionResource->save($ratingOption);
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }
}
