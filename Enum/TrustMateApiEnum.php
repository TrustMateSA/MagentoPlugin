<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Enum;

class TrustMateApiEnum
{
    public const PRODUCTION_URL = 'https://trustmate.io/integration/v1/api' . DIRECTORY_SEPARATOR;
    public const SANDBOX_URL = 'https://trustmate.tech/integration/v1/api' . DIRECTORY_SEPARATOR;
    public const INVITATION_ENDPOINT = 'invitation';
}
