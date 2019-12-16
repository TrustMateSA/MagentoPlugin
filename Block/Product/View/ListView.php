<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2019 TrustMate
 */


namespace TrustMate\Opinions\Block\Product\View;

use Magento\Review\Block\Product\View\ListView as ProductListView;
use Magento\Review\Model\Review;
use TrustMate\Opinions\Helper\Data;

/**
 * Class ListView
 * @package TrustMate\Opinions\Block\Product\View
 */
class ListView extends ProductListView
{
    /**
     * @param  Review $review
     * @return bool
     */
    public function isTrustMateOpinion($review)
    {
        if ($review) {
            return $review->getTitle() == Data::OPINION_TITLE;
        }

        return false;
    }
}
