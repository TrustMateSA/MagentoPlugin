<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Model;

use Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory;

class Option
{
    /**
     * @var OptionCollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get option by rating id and value
     *
     * @param $ratingId
     * @param $value
     *
     * @return mixed
     */
    public function getOptionByRatingIdAndValue($ratingId, $value)
    {
        return $this->collectionFactory->create()
            ->addFieldToFilter('rating_id', ['eq' => $ratingId])
            ->addFieldToFilter('value', ['eq' => $value])
            ->getFirstItem()
            ->getData();
    }
}
