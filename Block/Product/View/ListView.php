<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2020 TrustMate
 */

namespace TrustMate\Opinions\Block\Product\View;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\EncoderInterface as JsonEncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;
use Magento\Review\Block\Product\View\ListView as ProductListView;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\Review;
use TrustMate\Opinions\Helper\Data;

/**
 * Class ListView
 *
 * @package TrustMate\Opinions\Block\Product\View
 */
class ListView extends ProductListView
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * ListView constructor.
     *
     * @param Context                    $context
     * @param EncoderInterface           $urlEncoder
     * @param JsonEncoderInterface       $jsonEncoder
     * @param StringUtils                $string
     * @param Product                    $productHelper
     * @param ConfigInterface            $productTypeConfig
     * @param FormatInterface            $localeFormat
     * @param Session                    $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface     $priceCurrency
     * @param CollectionFactory          $collectionFactory
     * @param Data                       $helper
     * @param array                      $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $urlEncoder,
        JsonEncoderInterface $jsonEncoder,
        StringUtils $string,
        Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        CollectionFactory $collectionFactory,
        Data $helper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $collectionFactory,
            $data
        );
        $this->helper = $helper;
    }

    /**
     * @param  Review $review
     *
     * @return bool
     */
    public function isTrustMateOpinion($review)
    {
        if ($review) {
            return $review->getTitle() == Data::OPINION_TITLE;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getReviewsCollection()
    {
        $product = $this->getProduct();

        if (null === $this->_reviewsCollection) {
            $this->_reviewsCollection = $this->_reviewsColFactory->create()
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->addStatusFilter(Review::STATUS_APPROVED)
                ->setDateOrder();

            if (!$this->helper->isProductsOpinionsEnabled()) {
                $this->_reviewsCollection->addFieldToFilter('title', array('neq' => Data::OPINION_TITLE));
                $this->_reviewsCollection->addEntityFilter('product', $product->getId());
            } else {
                if ($product->getTypeId() === Configurable::TYPE_CODE) {
                    $products = $product->getTypeInstance()->getChildrenIds($product->getId());
                    $products[] = $product->getId();
                    $this->_reviewsCollection->addFieldToFilter('entity_pk_value', array('in' => $products));
                } else {
                    $this->_reviewsCollection->addEntityFilter('product', $product->getId());
                }
            }
        }

        return $this->_reviewsCollection;
    }
}
