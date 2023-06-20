<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Model\Resolver\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Review\Model\Review\Config as ReviewsConfig;
use Magento\ReviewGraphQl\Model\DataProvider\AggregatedReviewsDataProvider;
use Magento\ReviewGraphQl\Model\DataProvider\ProductReviewsDataProvider;

class Reviews implements ResolverInterface
{
    /**
     * @var ProductReviewsDataProvider
     */
    private $productReviewsDataProvider;

    /**
     * @var AggregatedReviewsDataProvider
     */
    private $aggregatedReviewsDataProvider;

    /**
     * @var ReviewsConfig
     */
    private $reviewsConfig;

    /**
     * @var \TrustMate\Opinions\Model\ResourceModel\ProductReview\CollectionFactory
     */
    private $trustmateCollectionFactory;

    /**
     * @param ProductReviewsDataProvider $productReviewsDataProvider
     * @param AggregatedReviewsDataProvider $aggregatedReviewsDataProvider
     * @param ReviewsConfig $reviewsConfig
     */
    public function __construct(
        ProductReviewsDataProvider                                              $productReviewsDataProvider,
        AggregatedReviewsDataProvider                                           $aggregatedReviewsDataProvider,
        ReviewsConfig                                                           $reviewsConfig,
        \TrustMate\Opinions\Model\ResourceModel\ProductReview\CollectionFactory $trustmateCollectionFactory
    )
    {
        $this->productReviewsDataProvider = $productReviewsDataProvider;
        $this->aggregatedReviewsDataProvider = $aggregatedReviewsDataProvider;
        $this->reviewsConfig = $reviewsConfig;
        $this->trustmateCollectionFactory = $trustmateCollectionFactory;
    }

    /**
     * Resolves the product reviews
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return array|Value|mixed
     *
     * @throws GraphQlInputException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field       $field,
                    $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    )
    {
        if (false === $this->reviewsConfig->isEnabled()) {
            return ['items' => []];
        }

        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('Value must contain "model" property.'));
        }

        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }

        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        /** @var Product $product */
        $product = $value['model'];
        $reviewsCollection = $this->productReviewsDataProvider->getData(
            (int)$product->getId(),
            $args['currentPage'],
            $args['pageSize']
        );

        $trustmateCollection = $this->trustmateCollectionFactory->create();
        $reviewsCollection->getSelect()->reset('columns');
        $reviewsCollection->getSelect()->columns(['main_table.review_id', 'detail.detail_id', 'detail.store_id', 'detail.title', 'detail.detail',
            'detail.nickname', 'main_table.created_at', 'review_entity.entity_code']);
        $reviewsCollection->getSelect()->reset('where');
        $reviewsCollection->getSelect()->where("review_entity.entity_code='product'");
        $reviewsCollection->getSelect()->where("main_table.entity_pk_value=" . $product->getId());

        $trustmateCollection->getSelect()->reset('from');
        $trustmateCollection->getSelect()->from('trustmate_product_opinions');
        $trustmateCollection->getSelect()->reset('columns');
        $trustmateCollection->getSelect()->columns(['id', 'id', 'store_id', 'author_email', 'body', 'author_name', 'created_at', 'product']);
        $trustmateCollection->getSelect()->reset('where');
        $trustmateCollection->addFieldToFilter('trustmate_product_opinions.store_id', ['eq' => $product->getStoreId()]);
        $trustmateCollection->addFieldToFilter('trustmate_product_opinions.product', ['eq' => $product->getId()]);

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

        foreach ($reviewsCollection->getData() as $itemData) {
            if (isset($reviewsCollection->getItems()[$itemData['review_id']])) {
                continue;
            }

            $reviewItem = clone $reviewsCollection->getFirstItem();
            $reviewItem->setData([
                'review_id' => $itemData['review_id'],
                'detail_id' => $itemData['detail_id'],
                'store_id' => $itemData['store_id'],
                'title' => $itemData['title'],
                'detail' => $itemData['detail'],
                'nickname' => $itemData['nickname'],
                'created_at' => $itemData['created_at'],
                'entity_code' => $itemData['entity_code']
            ]);

            $reviewsCollection->addItem($reviewItem);
        }

        return $this->aggregatedReviewsDataProvider->getData($reviewsCollection);
    }
}
