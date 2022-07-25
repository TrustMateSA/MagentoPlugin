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
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return $this->scopeConfigInterface->isSetFlag(
          self::MODULE_STATUS,
          ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return status of sandbox
     *
     * @return bool
     */
    public function isSandboxEnabled(): bool
    {
        return $this->scopeConfigInterface->isSetFlag(
            self::SANDBOX_MODE_STATUS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Value of API Key
     *
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->scopeConfigInterface->getValue(
            self::API_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check collect agreements status
     *
     * @return bool
     */
    public function isCollectAgreementsEnabled(): bool
    {
        return $this->scopeConfigInterface->isSetFlag(
            self::COLLECT_AGREEMENTS_STATUS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if collect additional review of product
     *
     * @return bool
     */
    public function isProductOpinionEnabled(): bool
    {
        return $this->scopeConfigInterface->isSetFlag(
            self::PRODUCTS_OPINIONS_STATUS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get invitation event
     *
     * @return mixed
     */
    public function getInvitationEvent()
    {
        return $this->scopeConfigInterface->getValue(
            self::INVITATION_EVENT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get store id
     *
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->scopeConfigInterface->getValue(
            self::STORE_ID,
            ScopeInterface::SCOPE_STORE
        );
    }
}
