<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Model\ResourceModel\MagentoReview;

use Magento\Review\Model\ResourceModel\Review\Collection as MagentoReviewCollection;

class Collection extends MagentoReviewCollection
{
    /**
     * Add entity filter
     *
     * @param int|string $entity
     * @param int $pkValue
     * @return MagentoReviewCollection
     */
    public function addEntityFilter($entity, $pkValue): MagentoReviewCollection
    {
        $reviewEntityTable = $this->getReviewEntityTable();
        if (is_numeric($entity)) {
            $this->addFilter('entity', $this->getConnection()->quoteInto('main_table.entity_id=?', $entity), 'string');
        } elseif (is_string($entity)) {
            $this->_select->join(
                $reviewEntityTable,
                'main_table.entity_id=' . $reviewEntityTable . '.entity_id',
                ['entity_code']
            );
        }

        return $this;
    }
}
