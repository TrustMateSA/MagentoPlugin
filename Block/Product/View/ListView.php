<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Block\Product\View;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\EncoderInterface as JsonEncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;
use Magento\Review\Block\Product\View\ListView as ProductListView;
use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\Review\Model\Review;
use TrustMate\Opinions\Api\Data\ProductReviewInterface;
use TrustMate\Opinions\Model\ResourceModel\ProductReview\CollectionFactory;
use Zend_Db_Expr;
use Zend_Db_Select;

class ListView extends ProductListView
{
    /**
     * @var CollectionFactory
     */
    private $trustmateCollectionFactory;

    /**
     * @var Resolver
     */
    private Resolver $localeResolver;

    public function __construct(
        Context                    $context,
        EncoderInterface           $urlEncoder,
        JsonEncoderInterface       $jsonEncoder,
        StringUtils                $string,
        Product                    $productHelper,
        ConfigInterface            $productTypeConfig,
        FormatInterface            $localeFormat,
        Session                    $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        ReviewCollectionFactory $collectionFactory,
        CollectionFactory $trustmateCollectionFactory,
        Resolver $localeResolver,
        array $data = []
    ) {
        $this->trustmateCollectionFactory = $trustmateCollectionFactory;
        $this->localeResolver = $localeResolver;
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
    }

    /**
     * @param ProductReviewInterface|Review $review
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

            $magentoReviewsLastId = (string)$this->_reviewsColFactory->create()->getLastItem()->getId() ?: 0;
            $reviewsCollection = $this->_reviewsCollection;
            $trustmateCollection = $this->trustmateCollectionFactory->create();

            $reviewsCollection->getSelect()->reset('columns');
            $reviewsCollection->getSelect()->columns(
                [
                    'main_table.review_id',
                    'detail.detail_id',
                    'detail.store_id',
                    'detail.title',
                    'detail.detail',
                    'detail.nickname',
                    'main_table.created_at',
                    new Zend_Db_Expr('NULL as product')
                ]
            );
            if (!isset($reviewsCollection->getSelect()->getPart(Zend_Db_Select::FROM)['review_entity'])) {
                $reviewsCollection->getSelect()->join(
                    ['review_entity' => $reviewsCollection->getTable('review_entity')],
                    'main_table.entity_id = review_entity.entity_id',
                    ['entity_code']
                );
            }

            $reviewsCollection->getSelect()->where("review_entity.entity_code='product'");
            $reviewsCollection->getSelect()->where("main_table.entity_pk_value=" . $product->getId());

            $trustmateCollection->getSelect()->reset('columns');
            $trustmateCollection->getSelect()->columns(
                [
                    'review_id' => new Zend_Db_Expr(
                        'CAST(id AS UNSIGNED) + CAST(' . $magentoReviewsLastId . ' AS UNSIGNED)'
                    ),
                    'id',
                    'store_id',
                    'author_email',
                    'body',
                    'author_name',
                    'created_at',
                    'product'
                ]
            );

            $trustmateCollection->addFieldToFilter('store_id', ['eq' => $storeId]);
            $trustmateCollection->addFieldToFilter('product', ['eq' => $product->getId()]);

            $reviewsColumns = array_column($reviewsCollection->getSelect()->getPart('columns'), 1);
            if (in_array('entity_code', $reviewsColumns, true)) {
                $trustmateCollection->getSelect()->columns(['entity_code' => new Zend_Db_Expr('NULL')]);
            }

            $newest = clone $reviewsCollection->getSelect()->union([
                (string)$reviewsCollection->getSelect(),
                (string)$trustmateCollection->getSelect()
            ]);
            $newest->reset('from');

            $reviewsCollection->getSelect()->reset();
            $reviewsCollection->getSelect()->from([
                'main_table' => new Zend_Db_Expr('(' . (string) $newest . ')')
            ]);

            $reviewsCollection->getSelect()->order('created_at DESC');
            $reviewsCollection->addRateVotes();
            $this->_reviewsCollection = $reviewsCollection;
        }

        return $this->_reviewsCollection;
    }

    /**
     * @return false|string
     */
    public function getStoreLocale()
    {
        $currentLocaleCode = $this->localeResolver->getLocale();

        return strstr($currentLocaleCode, '_', true);
    }
}
