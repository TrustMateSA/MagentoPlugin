<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2020 TrustMate
 */

namespace TrustMate\Opinions\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use TrustMate\Opinions\Helper\Data;
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
     * @param Data              $helper
     * @param CollectionFactory $collectionFactory
     * @param ReviewFactory     $reviewFactory
     * @param Logger            $logger
     */
    public function __construct(
        Data $helper,
        CollectionFactory $collectionFactory,
        ReviewFactory $reviewFactory,
        Logger $logger
    ) {
        $this->helper            = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->reviewFactory     = $reviewFactory;
        $this->logger            = $logger;
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
        $enabled = $this->helper->isProductsOpinionsEnabled();
        $collection = $this->collectionFactory->create()
            ->addStoreFilter($this->helper->getOpinionsStoreId())
            ->addFieldToFilter('title', Data::OPINION_TITLE);

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
                    $productIds[]         = $review->getEntityPkValue();
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
