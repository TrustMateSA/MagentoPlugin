<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2026 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Setup\Patch\Data;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use TrustMate\Opinions\Model\Config\Data;

/**
 * API key used to be stored in plain text, encrypt values saved before the field got Encrypted backend model
 */
class EncryptApiKey implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EncryptorInterface $encryptor
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->encryptor = $encryptor;
    }

    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table = $this->moduleDataSetup->getTable('core_config_data');

        $values = $connection->fetchPairs(
            $connection->select()
                ->from($table, ['config_id', 'value'])
                ->where('path = ?', Data::API_KEY)
                ->where('value <> ?', '')
        );

        foreach ($values as $configId => $value) {
            // Already saved through Encrypted backend model (e.g. key re-entered before setup:upgrade)
            if (preg_match(Data::ENCRYPTED_VALUE_PATTERN, $value)) {
                continue;
            }

            $connection->update(
                $table,
                ['value' => $this->encryptor->encrypt($value)],
                ['config_id = ?' => $configId]
            );
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
