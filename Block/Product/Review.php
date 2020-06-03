<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2019 TrustMate
 */

namespace TrustMate\Opinions\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
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
        $product = $this->getProduct();

        if (!$product) {
            return 0;
        }

        $collection = $this->_reviewsColFactory->create()
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->addStatusFilter(ModelReview::STATUS_APPROVED)
        ;

        if (!$this->helper->isProductsOpinionsEnabled()) {
            $collection->addFieldToFilter('title', array('neq' => Data::OPINION_TITLE));
            $collection->addEntityFilter('product', $product->getId());
        } else {
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $products = $product->getTypeInstance()->getChildrenIds($product->getId());
                $products[] = $product->getId();
                $collection->addFieldToFilter('entity_pk_value', array('in' => $products));
            } else {
                $collection->addEntityFilter('product', $product->getId());
            }
        }

        return $collection->getSize();
    }

    /**
     * Get current product
     *
     * @return null|Product
     */
    public function getProduct()
    {
        $product = $this->_coreRegistry->registry('product');
        return $product ?: null;
    }
}
