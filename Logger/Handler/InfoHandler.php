<?php

/**
 * @category    TrustMate
 * @package     TrustMate_Opinions
 * @subpackage  Logger
 * @copyright   TrustMate
 * @since       2.0.7
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Logger\Handler;

use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Logger;

class InfoHandler extends BaseHandler
{
    /**
     * Logger type
     *
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * Filename
     *
     * @var string
     */
    protected $fileName = '/var/log/trustmate/invitation-info.log';
}
