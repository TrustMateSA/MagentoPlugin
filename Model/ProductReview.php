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
use TrustMate\Opinions\Api\Data\TrustMate;

class ProductReview extends AbstractExtensibleModel implements ProductReviewInterface
{
    public function _construct()
    {
        $this->_init(ResourceModel\ProductReview::class);
    }

    /**
     * @inheirtDoc
     */
    public function getId(): ?string
    {
        return $this->getData(self::REVIEW_ID);
    }

    /**
     * @inheirtDoc
     */
    public function setId($id): ?ProductReviewInterface
    {
        return $this->setData(self::REVIEW_ID, $id);
    }

    /**
     * @inheirtDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheirtDoc
     */
    public function setCreatedAt($date): ?ProductReviewInterface
    {
        return $this->setData(self::CREATED_AT, $date);
    }

    /**
     * @inheirtDoc
     */
    public function getGrade(): ?int
    {
        return $this->getData(self::GRADE);
    }

    /**
     * @inheirtDoc
     */
    public function setGrade($grade): ?ProductReviewInterface
    {
        return $this->setData(self::GRADE, $grade);
    }

    /**
     * @inheirtDoc
     */
    public function getAuthorEmail(): ?string
    {
        return $this->getData(self::AUTHOR_EMAIL);
    }

    /**
     * @inheirtDoc
     */
    public function setAuthorEamil($email): ?ProductReviewInterface
    {
        return $this->setData(self::AUTHOR_EMAIL, $email);
    }

    /**
     * @inheirtDoc
     */
    public function getAuthorName(): ?string
    {
        return $this->getData(self::AUTHOR_NAME);
    }

    /**
     * @inheirtDoc
     */
    public function setAuthorName($name): ?ProductReviewInterface
    {
        return $this->setData(self::AUTHOR_NAME, $name);
    }

    /**
     * @inheirtDoc
     */
    public function getProduct(): ?string
    {
        return $this->getData(self::PRODUCT);
    }

    /**
     * @inheirtDoc
     */
    public function setProduct($product): ?ProductReviewInterface
    {
        return $this->setData(self::PRODUCT, $product);
    }

    /**
     * @inheirtDoc
     */
    public function getBody(): ?string
    {
        return $this->getData(self::BODY);
    }

    /**
     * @inheirtDoc
     */
    public function setBody($body): ?ProductReviewInterface
    {
        return $this->setData(self::BODY, $body);
    }

    /**
     * @inheirtDoc
     */
    public function getPublicIdentifier(): ?string
    {
        return $this->getData(self::PUBLIC_IDENTIFIER);
    }

    /**
     * @inheirtDoc
     */
    public function setPublicIdentifier($publicIdentifier): ?ProductReviewInterface
    {
        return $this->setData(self::PUBLIC_IDENTIFIER, $publicIdentifier);
    }

    /**
     * @inheirtDoc
     */
    public function getExtensionAttributes(): ?\TrustMate\Opinions\Api\Data\ProductReviewExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheirtDoc
     */
    public function setExtensionAttributes(\TrustMate\Opinions\Api\Data\ProductReviewExtensionInterface $extensionAttributes): ProductReviewInterface
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @inheirtDoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheirtDoc
     */
    public function setUpdatedAt($date): ?ProductReviewInterface
    {
        return $this->setData(self::UPDATED_AT, $date);
    }

    /**
     * @inheirtDoc
     */
    public function getLanguage(): ?string
    {
        return $this->getData(self::LANGUAGE);
    }

    /**
     * @inheirtDoc
     */
    public function setLanguage($language): ?ProductReviewInterface
    {
        return $this->setData(self::LANGUAGE, $language);
    }

    /**
     * @inheirtDoc
     */
    public function getOriginalBody(): ?string
    {
        return $this->getData(self::ORIGINAL_BODY);
    }

    /**
     * @inheirtDoc
     */
    public function setOriginalBody($originalBody): ?ProductReviewInterface
    {
        return $this->setData(self::ORIGINAL_BODY, $originalBody);
    }
}
