<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Enum;

class TrustMateApiEnum
{
    public const PRODUCTION_URL = 'https://trustmate.io/public/api' . DIRECTORY_SEPARATOR;
    public const SANDBOX_URL = 'https://trustmate.tech/public/api' . DIRECTORY_SEPARATOR;
    public const REVIEW_ENDPOINT = 'product_review' . DIRECTORY_SEPARATOR;
    public const REVIEW_TRANSLATION_ENDPOINT = 'product_review_translation' . DIRECTORY_SEPARATOR;
    public const INVITATION_ENDPOINT = 'invitation';
}
