<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2020 TrustMate
 */

namespace TrustMate\Opinions\Api;

/**
 * Interface ApiInterface
 * @package TrustMate\Opinions\Api
 */
interface ApiInterface
{
    /**
     * @param $data
     *
     * @return mixed
     */
    public function createInvitation($data);

    /**
     * @param $data
     *
     * @return mixed
     */
    public function createInvitationWithConfigId($data);

    /**
     * @param $data
     *
     * @return mixed
     */
    public function createInvitationProduct($data);

    /**
     * @param $data
     *
     * @return mixed
     */
    public function getInvitations($data);

    /**
     * @param $data
     *
     * @return mixed
     */
    public function getInvitationById($data);

    /**
     * @param $data
     *
     * @return mixed
     */
    public function getProductReview($data);
}