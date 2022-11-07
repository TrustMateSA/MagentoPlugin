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
    public const STORE_ID = 'trustmate_opinions_section/general/store_id';
    public const GTIN_CODE = 'trustmate_opinions_section/general/gtin_code';
    public const MPN_CODE = 'trustmate_opinions_section/general/mpn_code';
    public const FIX_LOCALID = 'trustmate_opinions_section/general/fix_localid';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigInterface;

    public function __construct(
        ScopeConfigInterface $scopeConfigInterface
    ) {
        $this->scopeConfigInterface = $scopeConfigInterface;
    }

    /**
     * Return status of module
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isModuleEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfigInterface->isSetFlag(
            self::MODULE_STATUS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return status of sandbox
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isSandboxEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfigInterface->isSetFlag(
            self::SANDBOX_MODE_STATUS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Value of API Key
     *
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getApiKey(?int $storeId = null)
    {
        return $this->scopeConfigInterface->getValue(
            self::API_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check collect agreements status
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isCollectAgreementsEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfigInterface->isSetFlag(
            self::COLLECT_AGREEMENTS_STATUS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if collect additional review of product
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isProductOpinionEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfigInterface->isSetFlag(
            self::PRODUCTS_OPINIONS_STATUS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get invitation event
     *
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getInvitationEvent(?int $storeId = null)
    {
        return $this->scopeConfigInterface->getValue(
            self::INVITATION_EVENT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get store id
     *
     * @return mixed
     */
    public function getStoreId(?int $storeId = null)
    {
        return $this->scopeConfigInterface->getValue(
            self::STORE_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get GTIN attribute code
     *
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getGtinCode(?int $storeId = null)
    {
        return $this->scopeConfigInterface->getValue(
            self::GTIN_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get MPN attribute code
     *
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getMpnCode(?int $storeId = null)
    {
        return $this->scopeConfigInterface->getValue(
            self::MPN_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return status of module
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isFixLocalIdEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfigInterface->isSetFlag(
            self::FIX_LOCALID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
