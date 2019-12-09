<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2019 TrustMate
 */


namespace TrustMate\Opinions\Block\Product;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\Review as ModelReview;
use Magento\Review\Block\Product\Review as ProductReview;
use TrustMate\Opinions\Helper\Data;

/**
 * Class Shop
 * @package TrustMate\Opinions\Block
 */
class Review extends ProductReview
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Review constructor.
     * @param Context           $context
     * @param Registry          $registry
     * @param CollectionFactory $collectionFactory
     * @param Data              $helper
     * @param array             $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $collectionFactory,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($context, $registry, $collectionFactory, $data);
    }

    /**
     * @inheritdoc
     */
    public function getCollectionSize()
    {
        $collection = $this->_reviewsColFactory->create()->addStoreFilter(
            $this->_storeManager->getStore()->getId()
        )->addStatusFilter(ModelReview::STATUS_APPROVED)->addEntityFilter(
            'product',
            $this->getProductId()
        );

        if (!$this->helper->isProductsOpinionsEnabled()) {
            $collection->addFieldToFilter('title' , array('neq' => Data::OPINION_TITLE));
        }

        return $collection->getSize();
    }
}
