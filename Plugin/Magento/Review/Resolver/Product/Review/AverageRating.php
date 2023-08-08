<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Plugin\Magento\Review\Resolver\Product\Review;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\ReviewGraphQl\Model\Resolver\Product\Review\AverageRating as MagentoAverageRating;
use TrustMate\Opinions\Model\ResourceModel\ProductReview;
use TrustMate\Opinions\Model\ProductReviewFactory;

class AverageRating
{
    /**
     * @var ProductReviewFactory
     */
    private $trustmateReviewModel;

    /**
     * @var ProductReview;
     */
    private $trustmateReviewResourceModel;

    /**
     * @param ProductReviewFactory $trustmateReviewModel
     * @param ProductReview $trustmateReviewResourceModel
     */
    public function __construct(
        ProductReviewFactory $trustmateReviewModel,
        ProductReview $trustmateReviewResourceModel
    ) {
        $this->trustmateReviewModel = $trustmateReviewModel;
        $this->trustmateReviewResourceModel = $trustmateReviewResourceModel;
    }

    /**
     * @param MagentoAverageRating $subject
     * @param float $result
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     *
     * @return float
     */
    public function afterResolve(
        MagentoAverageRating $subject,
        float $result,
        Field $field,
        ContextInterface $context,
        ResolveInfo $info,
        ?array $value
    ) :float {
        $review = $value['model'];
        if ($review->getTitle() !== 'Opinia z TrustMate') {
            return $result;
        }

        $trustmateReviewModel = $this->trustmateReviewModel->create();
        $this->trustmateReviewResourceModel->load($trustmateReviewModel, $review->getDetailId());

        return ((int)$trustmateReviewModel->getGrade() / 5) * 100;
    }
}
