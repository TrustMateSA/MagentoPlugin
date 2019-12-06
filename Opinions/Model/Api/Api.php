<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2019 TrustMate
 */

namespace TrustMate\Opinions\Model\Api;

use TrustMate\Opinions\Api\ApiInterface;

/**
 * Class Api
 * @package TrustMate\Opinions\Model\Api
 */
class Api extends Curl implements ApiInterface
{
    const API_URI = 'https://stage.api.trustmate.io/v0/';
    const DEVELOPER_MODE = true;
    const AUTH_LOGIN = 'fwc';
    const AUTH_PASS = 'SaithieM8va0vie';

    /**
     * Create invitation to shop opinion
     *
     * @param $data
     *
     * @return mixed
     */
    public function createInvitation($data)
    {
        $response = $this->curl(static::API_URI . 'invitation/', 'POST', $data);

        return $response ? $response : $this->getError();
    }

    /**
     * Create invitation to product opinion
     *
     * @deprecated see createInvitation
     *
     * @param $data
     *
     * @return mixed
     */
    public function createProductInvitation($data)
    {
        $response = $this->curl(static::API_URI . 'invitation/product/', 'POST', $data);

        return $response ? $response : $this->getError();
    }

    /**
     * Get product review
     *
     * @param $data
     *
     * @return mixed
     */
    public function getProductReview($data)
    {
        $response = $this->curl(static::API_URI . 'product_review/', 'GET', $data);

        return $response ? $response : $this->getError();
    }

    /**
     * Uunused
     *
     * @param $data
     *
     * @return mixed
     */
    public function getInvitations($data)
    {
        $response = $this->curl(static::API_URI . 'invitation_config/', 'GET', $data);

        return $response ? $response : $this->getError();
    }

    /**
     * Unused
     *
     * @param $data
     *
     * @return mixed
     */
    public function createInvitationProduct($data)
    {
        if (isset($data['invitation_config_id'])) {
            $response = $this->curl(static::API_URI . 'invitation_config/' . $data['invitation_config_id'] . '/invitation/product', 'POST', $data);

            return $response ? $response : $this->getError();
        }
        else {
            return 'Invitation config id is undefined';
        }
    }

    /**
     * Unused
     *
     * @param $data
     *
     * @return mixed
     */
    public function createInvitationWithConfigId($data)
    {
        if (isset($data['invitation_config_id'])) {
            $response = $this->curl(static::API_URI . 'invitation_config/' . $data['invitation_config_id'] . '/invitation', 'POST', $data);

            return $response ? $response : $this->getError();
        }
        else {
            return 'Invitation config id is undefined';
        }
    }

    /**
     * Unused
     *
     * @param $data
     *
     * @return mixed
     */
    public function getInvitationById($data)
    {
        if (isset($data['id'])) {
            $response = $this->curl(static::API_URI . 'invitation_config/' . $data['id'], 'GET', $data);

            return $response ? $response : $this->getError();
        }
        else {
            return 'Invitation config id is undefined';
        }
    }
}