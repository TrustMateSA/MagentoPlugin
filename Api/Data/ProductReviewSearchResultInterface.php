<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ProductReviewSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return ProductReviewInterface[]
     */
    public function getItems(): array;

    /**
     * @param ProductReviewInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items): ProductReviewSearchResultInterface;
}
