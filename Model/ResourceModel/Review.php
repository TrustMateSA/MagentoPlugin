<?php

/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Review\Model\ResourceModel\Review as MagentoReview;

class Review extends MagentoReview
{
    /**
     * Perform actions after object save
     *
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _afterSave(AbstractModel $object): Review
    {
        $connection = $this->getConnection();
        /**
         * save detail
         */
        $detail   = [
            'title' => $object->getTitle(),
            'detail' => $object->getDetail(),
            'nickname' => $object->getNickname(),
        ];

        $select   = $connection->select()->from($this->_reviewDetailTable, 'detail_id')->where('review_id = :review_id');
        $detailIds = $connection->fetchAll($select, [':review_id' => $object->getId()]);
        if ($detailIds) {
            foreach ($detailIds as $detailId) {
                $condition = ["detail_id = ?" => $detailId];
                $connection->update($this->_reviewDetailTable, $detail, $condition);
            }
        } else {
            if (is_array($object->getStoreId())) {
                foreach ($object->getStoreId() as $storeId) {
                    $detail['store_id']    = $storeId;
                    $detail['customer_id'] = $object->getCustomerId();
                    $detail['review_id']   = $object->getId();
                    $connection->insert($this->_reviewDetailTable, $detail);
                }
            } else {
                $detail['store_id'] = $object->getStoreId();
                $detail['customer_id'] = $object->getCustomerId();
                $detail['review_id'] = $object->getId();
                $connection->insert($this->_reviewDetailTable, $detail);
            }
        }

        /**
         * save stores
         */
        $stores = $object->getStores();
        if (!empty($stores)) {
            $condition = ['review_id = ?' => $object->getId()];
            $connection->delete($this->_reviewStoreTable, $condition);
            $insertedStoreIds = [];
            foreach ($stores as $storeId) {
                if (in_array($storeId, $insertedStoreIds)) {
                    continue;
                }

                $insertedStoreIds[] = $storeId;
                $storeInsert        = ['store_id' => $storeId, 'review_id' => $object->getId()];
                $connection->insert($this->_reviewStoreTable, $storeInsert);
            }
        }

        // reaggregate ratings, that depend on this review
        $this->_aggregateRatings($this->_loadVotedRatingIds($object->getId()), $object->getEntityPkValue());

        return $this;
    }
}
