<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use TrustMate\Opinions\Enum\StoreDataEnum;

class Store
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface  $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface  $scopeConfig
    ) {
        $this->scopeConfig  = $scopeConfig;
    }

    /**
     * Get stores locale
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getStoreLocales(int $storeId): string
    {
        $localeCode = $this->scopeConfig->getValue(
            StoreDataEnum::CONFIG_LOCALE_CODE_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return strstr($localeCode, '_', true);
    }
}
