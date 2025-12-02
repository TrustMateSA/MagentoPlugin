<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use TrustMate\Opinions\Api\Data\ProductReviewExtensionInterface;
use TrustMate\Opinions\Api\Data\ProductReviewInterface;

class ProductReview extends AbstractExtensibleModel implements ProductReviewInterface
{
    public function _construct()
    {
        $this->_init(ResourceModel\ProductReview::class);
    }

    /**
     * @inheritDoc
     */
    public function getId(): ?string
    {
        return $this->getData(self::REVIEW_ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($id): ?ProductReviewInterface
    {
        return $this->setData(self::REVIEW_ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($date): ?ProductReviewInterface
    {
        return $this->setData(self::CREATED_AT, $date);
    }

    /**
     * @inheritDoc
     */
    public function getGrade(): ?string
    {
        return $this->getData(self::GRADE);
    }

    /**
     * @inheritDoc
     */
    public function setGrade($grade): ?ProductReviewInterface
    {
        return $this->setData(self::GRADE, $grade);
    }

    /**
     * @inheritDoc
     */
    public function getAuthorEmail(): ?string
    {
        return $this->getData(self::AUTHOR_EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setAuthorEmail($email): ?ProductReviewInterface
    {
        return $this->setData(self::AUTHOR_EMAIL, $email);
    }

    /**
     * @inheritDoc
     */
    public function getAuthorName(): ?string
    {
        return $this->getData(self::AUTHOR_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setAuthorName($name): ?ProductReviewInterface
    {
        return $this->setData(self::AUTHOR_NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getProduct(): ?string
    {
        return $this->getData(self::PRODUCT);
    }

    /**
     * @inheritDoc
     */
    public function setProduct($product): ?ProductReviewInterface
    {
        return $this->setData(self::PRODUCT, $product);
    }

    /**
     * @inheritDoc
     */
    public function getBody(): ?string
    {
        return $this->getData(self::BODY);
    }

    /**
     * @inheritDoc
     */
    public function setBody($body): ?ProductReviewInterface
    {
        return $this->setData(self::BODY, $body);
    }

    /**
     * @inheritDoc
     */
    public function getPublicIdentifier(): ?string
    {
        return $this->getData(self::PUBLIC_IDENTIFIER);
    }

    /**
     * @inheritDoc
     */
    public function setPublicIdentifier($publicIdentifier): ?ProductReviewInterface
    {
        return $this->setData(self::PUBLIC_IDENTIFIER, $publicIdentifier);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?ProductReviewExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(ProductReviewExtensionInterface $extensionAttributes): ProductReviewInterface
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt($date): ?ProductReviewInterface
    {
        return $this->setData(self::UPDATED_AT, $date);
    }

    /**
     * @inheritDoc
     */
    public function getLanguage(): ?string
    {
        return $this->getData(self::LANGUAGE);
    }

    /**
     * @inheritDoc
     */
    public function setLanguage($language): ?ProductReviewInterface
    {
        return $this->setData(self::LANGUAGE, $language);
    }

    /**
     * @inheritDoc
     */
    public function getOriginalBody(): ?string
    {
        return $this->getData(self::ORIGINAL_BODY);
    }

    /**
     * @inheritDoc
     */
    public function setOriginalBody($originalBody): ?ProductReviewInterface
    {
        return $this->setData(self::ORIGINAL_BODY, $originalBody);
    }

    /**
     * @inheritDoc
     */
    public function getOrderIncrementId(): ?string
    {
        return $this->getData(self::ORDER_INCREMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderIncrementId($orderIncrementId): ?ProductReviewInterface
    {
        return $this->setData(self::ORDER_INCREMENT_ID, $orderIncrementId);
    }

    /**
     * @inheritDoc
     */
    public function getGtinCode(): ?string
    {
        return $this->getData(self::GTIN_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setGtinCode($gtinCode): ?ProductReviewInterface
    {
        return $this->setData(self::GTIN_CODE, $gtinCode);
    }

    /**
     * @inheritDoc
     */
    public function getMpnCode(): ?string
    {
        return $this->getData(self::MPN_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setMpnCode($mpnCode): ?ProductReviewInterface
    {
        return $this->setData(self::MPN_CODE, $mpnCode);
    }
}
