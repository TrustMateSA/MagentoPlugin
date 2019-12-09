<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2019 TrustMate
 */


namespace TrustMate\Opinions\Block\Product;

use Magento\Review\Model\ResourceModel\Review\Collection as ReviewCollection;
use Magento\Review\Block\Product\View as ProductView;
use TrustMate\Opinions\Helper\Data;

/**
 * Class View
 * @package TrustMate\Opinions\Block\Product
 */
class View
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * View constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper   = $helper;
    }

    /**
     * @param ProductView $subject
     * @param ReviewCollection $result
     * @return mixed
     */
    public function afterGetReviewsCollection($subject, $result)
    {
        if (!$this->helper->isProductsOpinionsEnabled()) {
            return $result->addFieldToFilter('title' , array('neq' => Data::OPINION_TITLE));
        }

        return $result;
    }
}
