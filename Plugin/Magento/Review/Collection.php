<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Plugin\Magento\Review;

use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use TrustMate\Opinions\Model\ResourceModel\ProductReview\CollectionFactory as TrustmateCollectionFactory;

class Collection
{
    /**
     * @var TrustmateCollectionFactory
     */
    private $trustmateCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param TrustmateCollectionFactory $trustmateCollectionFactory
     * @param StoreManagerInterface      $storeManager
     * @param Registry                   $registry
     */
    public function __construct(
        TrustmateCollectionFactory $trustmateCollectionFactory,
        StoreManagerInterface $storeManager,
        Registry $registry
    ) {
        $this->trustmateCollectionFactory = $trustmateCollectionFactory;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
    }

    public function afterGetItems($subject, $result)
    {
        $renumberedResult = array_values($result);
        $trustmateCollection = $this->trustmateCollectionFactory->create()
            ->addFieldToFilter('store_id', ['eq' => $this->storeManager->getStore()->getId()])
            ->addFieldToFilter('product', $this->registry->registry('current_product')->getId())
            ->addFieldToFilter('original_body', ['null' => 1])
            ->getItems();
        foreach ($trustmateCollection as $key => $r) {
            $r->setData('title', 'Opinia z TrustMate');
            $r->setData('nickname', $r->getAuthorName());
            $r->setData('detail', $r->getBody());
            $r->setData('percent', ((int)$r->getGrade() / 5) * 100);

            $renumberedResult[array_key_last($renumberedResult) + 1] = $r;
        }

        return $renumberedResult;
    }
}
