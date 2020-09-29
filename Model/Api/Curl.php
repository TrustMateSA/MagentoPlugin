<?php
/**
 * @package TrustMate\Opinions
 * @copyright 2020 TrustMate
 */

namespace TrustMate\Opinions\Model\Api;

use TrustMate\Opinions\Helper\Data;
use TrustMate\Opinions\Logger\Logger;

/**
 * Class RestClientInterface
 *
 * @api
 * @package TrustMate\Opinions
 */
class Curl
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var string
     */
    protected $error;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Curl constructor.
     * @param Data   $helper
     * @param Logger $logger
     */
    public function __construct(Data $helper, Logger $logger)
    {
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param $url
     * @param $type
     * @param array $data
     *
     * @return mixed
     */
    public function curl($url, $type, array $data)
    {
        if ($type === 'GET') {
            $url .= '?' . http_build_query($data);
        }

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlConfig($url, $type, $data));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);

        $this->checkError($info, json_decode($response, true), $url, $data);
        curl_close($curl);

        if (!empty($response) && ($response != "null\n")) {
            return json_decode($response, true);
        }

        return in_array($info['http_code'], array(200, 201)) ? $info['http_code'] : false;
    }

    /**
     * @param $info
     * @param $response
     * @param $url
     * @param $data
     */
    protected function checkError($info, $response, $url, $data)
    {
        $this->error = null;
        if (!in_array($info['http_code'], array(200, 201))) {
            $msg = null;
            switch ($info['http_code']) {
                case 400:
                    $msg = '400 Bad Request';
                    break;
                case 401:
                    $msg = '401 Unauthorized';
                    break;
                case 403:
                    $msg = '403 Forbidden';
                    break;
                case 422:
                    $msg = '422 Unprocessable entity';
                    break;
                case 404:
                    $msg = '404 Not Found';
                    break;
                case 409:
                    $msg = '409 Conflict';
                    break;
                case 503:
                    $msg = '503 Service Unavailable';
                    break;
                default:
                    $msg = $info['http_code'] . ' Unknown Error';
            }

            if (isset($response['errors'])) {
                $msg .= ' - ' . json_encode($response['errors']);
            }

            if (isset($response['message'])) {
                $msg .= ' - Response message: ' . json_encode($response['message']);
            }

            if ($msg) {
                $this->logger->critical($msg . ' - url: ' . $url . ' # ' . print_r($data, true));
            }

            $this->error = $msg;
        }
    }

    /**
     * @return null|string
     */
    public function getError()
    {
        if (!empty($this->error)) {
            return $this->error;
        }

        return null;
    }

    /**
     * @param string $msg
     */
    public function setError($msg)
    {
        $this->error = $msg;
    }

    /**
     * @param string $url
     * @param string $type
     * @param array $data
     *
     * @return array
     */
    public function getCurlConfig($url, $type, $data)
    {
        $key = $this->helper->getApiKey();

        $config = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => 'UTF-8',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Content-Type: application/json",
                "Accept: application/json",
                "x-api-key: {$key}"
            )
        );

        if ($type === 'POST') {
            $config[CURLOPT_POSTFIELDS] = json_encode($data);
            $config[CURLOPT_POST] = true;
        }

        return $config;
    }
}