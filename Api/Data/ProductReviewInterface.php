<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface ProductReviewInterface extends ExtensibleDataInterface
{
    public const REVIEW_ID = 'id';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const GRADE = 'grade';
    public const AUTHOR_EMAIL = 'author_email';
    public const AUTHOR_NAME = 'author_name';
    public const PRODUCT = 'product';
    public const BODY = 'body';
    public const PUBLIC_IDENTIFIER = 'public_identifier';
    public const LANGUAGE = 'language';
    public const ORIGINAL_BODY = 'original_body';
    public const ORDER_INCREMENT_ID = 'order_increment_id';
    public const GTIN_CODE = 'gtin_code';
    public const MPN_CODE = 'mpn_code';

    /**
     * @return string|null
     */
    public function getId(): ?string;

    /**
     * @param $id
     *
     * @return ProductReviewInterface|null
     */
    public function setId($id): ?ProductReviewInterface;

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * @param $date
     *
     * @return ProductReviewInterface|null
     */
    public function setCreatedAt($date): ?ProductReviewInterface;

    /**
     * @param $date
     *
     * @return ProductReviewInterface|null
     */
    public function setUpdatedAt($date): ?ProductReviewInterface;

    /**
     * @return int|null
     */
    public function getGrade(): ?string;

    /**
     * @param $grade
     *
     * @return ProductReviewInterface|null
     */
    public function setGrade($grade): ?ProductReviewInterface;

    /**
     * @return string|null
     */
    public function getAuthorEmail(): ?string;

    /**
     * @param $email
     *
     * @return ProductReviewInterface|null
     */
    public function setAuthorEmail($email): ?ProductReviewInterface;

    /**
     * @return string|null
     */
    public function getAuthorName(): ?string;

    /**
     * @param $name
     *
     * @return ProductReviewInterface|null
     */
    public function setAuthorName($name): ?ProductReviewInterface;

    /**
     * @return string|null
     */
    public function getProduct(): ?string;

    /**
     * @param $product
     *
     * @return ProductReviewInterface|null
     */
    public function setProduct($product): ?ProductReviewInterface;

    /**
     * @return string|null
     */
    public function getBody(): ?string;

    /**
     * @param $body
     *
     * @return ProductReviewInterface|null
     */
    public function setBody($body): ?ProductReviewInterface;

    /**
     * @return string|null
     */
    public function getPublicIdentifier(): ?string;

    /**
     * @param $publicIdentifier
     *
     * @return ProductReviewInterface|null
     */
    public function setPublicIdentifier($publicIdentifier): ?ProductReviewInterface;

    /**
     * @return string|null
     */
    public function getLanguage(): ?string;

    /**
     * @param $language
     *
     * @return ProductReviewInterface|null
     */
    public function setLanguage($language): ?ProductReviewInterface;

    /**
     * @return string|null
     */
    public function getOriginalBody(): ?string;

    /**
     * @param $originalBody
     *
     * @return ProductReviewInterface|null
     */
    public function setOriginalBody($originalBody): ?ProductReviewInterface;

    /**
     * @return string|null
     */
    public function getOrderIncrementId(): ?string;

    /**
     * @param $orderIncrementId
     *
     * @return ProductReviewInterface|null
     */
    public function setOrderIncrementId($orderIncrementId): ?ProductReviewInterface;

    /**
     * @return string|null
     */
    public function getGtinCode(): ?string;

    /**
     * @param $gtinCode
     *
     * @return ProductReviewInterface|null
     */
    public function setGtinCode($gtinCode): ?ProductReviewInterface;

    /**
     * @return string|null
     */
    public function getMpnCode(): ?string;

    /**
     * @param $mpnCode
     *
     * @return ProductReviewInterface|null
     */
    public function setMpnCode($mpnCode): ?ProductReviewInterface;

    /**
     * @return \TrustMate\Opinions\Api\Data\ProductReviewExtensionInterface|null
     */
    public function getExtensionAttributes(): ?\TrustMate\Opinions\Api\Data\ProductReviewExtensionInterface;

    /**
     * @param \TrustMate\Opinions\Api\Data\ProductReviewExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(\TrustMate\Opinions\Api\Data\ProductReviewExtensionInterface $extensionAttributes): self;
}
