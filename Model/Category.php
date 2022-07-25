<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class Category
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepositoryInterface;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param CategoryRepositoryInterface $categoryRepositoryInterface
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepositoryInterface,
        StoreManagerInterface       $storeManager
    ) {
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->storeManager = $storeManager;
    }

    /**
     * Get category Path
     *
     * @param array $categoryIds
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCategoriesPath(array $categoryIds): string
    {
        $storeGroupId = $this->storeManager->getStore()->getStoreGroupId();
        $rootCategoryId = $this->storeManager->getGroup($storeGroupId)->getRootCategoryId();
        $categoryPath = [];
        foreach ($categoryIds as $categoryId) {
            if ($categoryId === $rootCategoryId) {
                continue;
            }

            $category = $this->categoryRepositoryInterface->get($categoryId);
            $categoryPath[] = $category->getName();
        }

        return implode(' / ', $categoryPath);
    }
}
