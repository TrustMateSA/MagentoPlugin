<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Block\Product\View;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Review\Block\Product\View\ListView as ProductListView;
use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\Review;
use TrustMate\Opinions\Api\Data\ProductReviewInterface;

class ListView extends ProductListView
{
    private \TrustMate\Opinions\Model\ResourceModel\ProductReview\CollectionFactory $trustmateCollectionFactory;
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context, \Magento\Framework\Url\EncoderInterface $urlEncoder, \Magento\Framework\Json\EncoderInterface $jsonEncoder, \Magento\Framework\Stdlib\StringUtils $string, \Magento\Catalog\Helper\Product $productHelper, \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig, \Magento\Framework\Locale\FormatInterface $localeFormat, \Magento\Customer\Model\Session $customerSession, ProductRepositoryInterface $productRepository, \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency, \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory,
        \TrustMate\Opinions\Model\ResourceModel\ProductReview\CollectionFactory $trustmateCollectionFactory,
        array $data = [])
    {
        $this->trustmateCollectionFactory = $trustmateCollectionFactory;
        parent::__construct($context, $urlEncoder, $jsonEncoder, $string, $productHelper, $productTypeConfig, $localeFormat, $customerSession, $productRepository, $priceCurrency, $collectionFactory, $data);
    }

    /**
     * @param Review|ProductReviewInterface $review
     *
     * @return bool
     */
    public function isTrustMateOpinion($review): bool
    {
        return $review->getTitle() === 'Opinia z TrustMate';
    }

    /**
     * @inheritdoc
     */
    public function getReviewsCollection(): Collection
    {
        $product = $this->getProduct();
        $storeId = $this->_storeManager->getStore()->getId();
        if (null === $this->_reviewsCollection) {
            $this->_reviewsCollection = $this->_reviewsColFactory->create()
                ->addFieldToFilter('detail.store_id', ['eq' => $storeId]);
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $products = $product->getTypeInstance()->getChildrenIds($product->getId());
                $products[] = $product->getId();
                $this->_reviewsCollection->addFieldToFilter('entity_pk_value', ['in' => $products]);
            } else {
                $this->_reviewsCollection->addEntityFilter('product', $product->getId());
            }
        }

        $reviewsCollection = $this->_reviewsCollection;
        $trustmateCollection = $this->trustmateCollectionFactory->create();
        $reviewsCollection->getSelect()->reset('columns');
        $reviewsCollection->getSelect()->columns(['main_table.review_id', 'detail.detail_id', 'detail.store_id', 'detail.title', 'detail.detail',
            'detail.nickname', 'main_table.created_at', 'review_entity.entity_code']);
        $reviewsCollection->getSelect()->where("review_entity.entity_code='product'");
        $reviewsCollection->getSelect()->where("main_table.entity_pk_value=" . $product->getId());

        $trustmateCollection->getSelect()->reset('columns');
        $trustmateCollection->getSelect()->columns(['id', 'id', 'store_id', 'author_email', 'body', 'author_name', 'created_at', 'product']);
        $trustmateCollection->addFieldToFilter('store_id', ['eq' => $storeId]);
        $trustmateCollection->addFieldToFilter('product', ['eq' => $product->getId()]);

        $newest = clone $reviewsCollection->getSelect()->union([
            (string)$reviewsCollection->getSelect(),
            (string)$trustmateCollection->getSelect()
        ]);
        $newest->reset('from');

        $reviewsCollection->getSelect()->reset();
        $reviewsCollection->getSelect()->from([
            'main_table' => new \Zend_Db_Expr('(' . (string)$newest . ')')
        ]);
        $reviewsCollection->setOrder('created_at', 'DESC');
        $reviewsCollection->addRateVotes();
        return $reviewsCollection;
    }

    private function addVoteRating()
    {

    }
}
