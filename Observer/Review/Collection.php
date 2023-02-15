<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Observer\Review;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;

class Collection implements ObserverInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Add store id and language to review collection
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $trustMateTableName = $this->resource->getTableName('trustmate_product_opinions');
        $joinConditions = 'main_table.trustmate_review_id = ' . $trustMateTableName . '.id';
        $observer->getCollection()->getSelect()->joinLeft(
            [$trustMateTableName],
            $joinConditions,
            []
        )->columns([
            $trustMateTableName . '.language',
            $trustMateTableName . '.status',
        ]);

        $joinConditions = 'main_table.review_id = detail.review_id';
        $observer->getCollection()->getSelect()->join(
            [],
            $joinConditions,
            []
        )->columns('detail.store_id');
    }
}
