<?php
/**
 * @package TrustMate\Opinions
 * @copyright 2020 TrustMate
 */

namespace TrustMate\Opinions\Logger;

use Magento\Framework\Logger\Handler\Base;

/**
 * Class Handler
 * @package TrustMate\Opinions\Logger
 */
class Handler extends Base
{
    protected $fileName = '/var/log/trustmate.log';
    protected $loggerType = Logger::DEBUG;
}