<?php

/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Data
{
    public const API_KEY = 'trustmate_opinions_section/general/api_key';
    public const MODULE_STATUS = 'trustmate_opinions_section/general/module_enabled';
    public const SANDBOX_MODE_STATUS = 'trustmate_opinions_section/general/sandbox_mode';
    public const COLLECT_AGREEMENTS_STATUS = 'trustmate_opinions_section/general/collect_agreements_with_trustmate';
    public const PRODUCTS_OPINIONS_STATUS = 'trustmate_opinions_section/general/products_opinions_enabled';
    public const INVITATION_EVENT = 'trustmate_opinions_section/general/invitation_event';
    public const GTIN_CODE = 'trustmate_opinions_section/general/gtin_code';
    public const MPN_CODE = 'trustmate_opinions_section/general/mpn_code';
    public const FIX_LOCALID = 'trustmate_opinions_section/general/fix_localid';
    public const SEND_VARIANT = 'trustmate_opinions_section/general/send_child';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigInterface;

    public function __construct(
        ScopeConfigInterface $scopeConfigInterface
    ) {
        $this->scopeConfigInterface = $scopeConfigInterface;
    }

    public function isModuleEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfigInterface->isSetFlag(
            self::MODULE_STATUS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isSandboxEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfigInterface->isSetFlag(
            self::SANDBOX_MODE_STATUS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getApiKey(?int $storeId = null): string
    {
        return $this->scopeConfigInterface->getValue(
            self::API_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isCollectAgreementsEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfigInterface->isSetFlag(
            self::COLLECT_AGREEMENTS_STATUS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isProductOpinionEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfigInterface->isSetFlag(
            self::PRODUCTS_OPINIONS_STATUS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getInvitationEvent(?int $storeId = null): string
    {
        return $this->scopeConfigInterface->getValue(
            self::INVITATION_EVENT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getGtinCode(?int $storeId = null): ?string
    {
        return $this->scopeConfigInterface->getValue(
            self::GTIN_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getMpnCode(?int $storeId = null): ?string
    {
        return $this->scopeConfigInterface->getValue(
            self::MPN_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isFixLocalIdEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfigInterface->isSetFlag(
            self::FIX_LOCALID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function sendVariantInformation(?int $storeId = null): bool
    {
        return $this->scopeConfigInterface->isSetFlag(
            self::SEND_VARIANT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
