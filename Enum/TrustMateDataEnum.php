<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Enum;

class TrustMateDataEnum
{
    public const PUBLIC_IDENTIFIER_COLUMN = 'public_identifier';
    public const LANGUAGE_COLUMN = 'language';
    public const CONDITION_EQUAL = 'eq';
    public const CONDITION_NOT_EQUAL = 'neq';
    public const CONDITION_NOT_NULL = 'notnull';
}
