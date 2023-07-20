<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Plugin\Magento\Review\Resolver\Product;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\ReviewGraphQl\Model\Resolver\Product\ReviewCount as MagentoReviewCount;
use TrustMate\Opinions\Model\ResourceModel\ProductReviewRating\CollectionFactory;

class ReviewCount
{
    /**
     * @var CollectionFactory
     */
    private $productReviewRating;

    /**
     * @param CollectionFactory $productReviewRating
     */
    public function __construct(CollectionFactory $productReviewRating)
    {
        $this->productReviewRating = $productReviewRating;
    }

    /**
     * @param MagentoReviewCount $subject
     * @param int $result
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     *
     * @return int
     */
    public function afterResolve(
        MagentoReviewCount $subject,
        int $result,
        Field $field,
        ContextInterface $context,
        ResolveInfo $info,
        ?array $value
    ): int {
        $product = $value['model'];
        $store = $context->getExtensionAttributes()->getStore();
        $productRating = $this->productReviewRating->create()
            ->addFieldToFilter('store_id', ['eq' => $store->getId()])
            ->addFieldToFilter('product_id', ['eq' => $product->getId()]);

        return (count($productRating->getItems()) > 0)
            ? $result + $productRating->getFirstItem()->getReviewSum()
            : $result;
    }
}
