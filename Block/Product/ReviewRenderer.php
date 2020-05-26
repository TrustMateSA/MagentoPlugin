<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2019 TrustMate
 */


namespace TrustMate\Opinions\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\View\Element\Template\Context;
use Magento\Review\Block\Product\ReviewRenderer as ReviewRendererCore;
use Magento\Review\Model\Review\Summary;
use Magento\Review\Model\ReviewFactory;
use TrustMate\Opinions\Helper\Data;

/**
 * Class ReviewRenderer
 */
class ReviewRenderer extends ReviewRendererCore
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * ReviewRenderer constructor.
     *
     * @param Context       $context
     * @param ReviewFactory $reviewFactory
     * @param Data          $helper
     * @param array         $data
     */
    public function __construct(
        Context $context,
        ReviewFactory $reviewFactory,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($context, $reviewFactory, $data);
    }

    /**
     * @inheritdoc
     */
    public function getRatingSummary()
    {
        $product = $this->getProduct();

        if ($this->helper->isProductsOpinionsEnabled() && $product->getTypeId() === Configurable::TYPE_CODE) {
            $count = 0;
            $summary = 0;

            foreach ($product->getTypeInstance()->getUsedProducts($product) as $simpleProduct) {
                $simpleRating = $this->getProductRatingSummary($simpleProduct)->getRatingSummary();

                if ($simpleRating) {
                    $summary += $simpleRating;
                    $count ++;
                }
            }

            return $summary / $count;
        }

        return $product->getRatingSummary()->getRatingSummary();
    }

    /**
     * @inheritdoct
     */
    public function getReviewsCount()
    {
        $product = $this->getProduct();

        if ($this->helper->isProductsOpinionsEnabled() && $product->getTypeId() === Configurable::TYPE_CODE) {
            $count = 0;

            foreach ($product->getTypeInstance()->getUsedProducts($product) as $simpleProduct) {
                $count += $this->getProductRatingSummary($simpleProduct)->getReviewsCount();
            }

            return $count;
        }

        return $this->getProduct()->getRatingSummary()->getReviewsCount();
    }


    /**
     * @param Product $product
     *
     * @return mixed
     */
    public function getProductRatingSummary(Product $product)
    {
        if (!$product->getRatingSummary()) {
            $this->_reviewFactory->create()->getEntitySummary($product, $this->_storeManager->getStore()->getId());
        }

        return $product->getRatingSummary();
    }
}
