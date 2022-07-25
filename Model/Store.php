<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use TrustMate\Opinions\Enum\StoreDataEnum;

class Store
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface  $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get stores locale
     *
     * @return array
     */
    public function getStoreLocales(): array
    {
        $stores = [];
        foreach ($this->storeManager->getStores() as $store) {
            $localeCode = $this->scopeConfig->getValue(
                StoreDataEnum::CONFIG_LOCALE_CODE_PATH,
                ScopeInterface::SCOPE_STORE,
                $store->getId()
            );

            $stores[strstr($localeCode, '_', true)] = $store->getId();
        }

        return $stores;
    }
}
