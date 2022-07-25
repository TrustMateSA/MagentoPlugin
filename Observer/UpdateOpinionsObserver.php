<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use TrustMate\Opinions\Model\Config\Data;
use TrustMate\Opinions\Logger\Logger;

/**
 * Class UpdateOpinionsObserver
 */
class UpdateOpinionsObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Review collection
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * UpdateOpinionsObserver constructor.
     *
     * @param Data $helper
     * @param CollectionFactory $collectionFactory
     * @param ReviewFactory $reviewFactory
     * @param Logger $logger
     */
    public function __construct(
        Data              $helper,
        CollectionFactory $collectionFactory,
        ReviewFactory     $reviewFactory,
        Logger            $logger
    ) {
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->reviewFactory = $reviewFactory;
        $this->logger = $logger;
    }

    /**
     * Update magento opinions
     *
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $productIds = [];
        $reviewsToAggregate = [];
        $enabled = $this->helper->isProductOpinionEnabled();
        $collection = $this->collectionFactory->create()
            ->addStoreFilter($this->helper->getStoreId())
            ->addFieldToFilter('title', 'Opinia z TrustMate');

        if ($enabled) {
            $collection->addStatusFilter(Review::STATUS_NOT_APPROVED);
            $newStatus = Review::STATUS_APPROVED;
        } else {
            $collection->addStatusFilter(Review::STATUS_APPROVED);
            $newStatus = Review::STATUS_NOT_APPROVED;
        }

        /** @var Review $review */
        foreach ($collection as $review) {
            try {
                $review->setStatusId($newStatus)->save();

                if (!in_array($review->getEntityPkValue(), $productIds)) {
                    $productIds[] = $review->getEntityPkValue();
                    $reviewsToAggregate[] = $review;
                }
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                continue;
            };
        }

        foreach ($reviewsToAggregate as $reviewToAggregate) {
            $reviewToAggregate->getResource()->aggregate($reviewToAggregate);
        }
    }
}
