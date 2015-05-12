<?php namespace GLGeorgiev\LaravelOAuth2Client\Provider;

use Config;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Entity\User;

/**
 * Class Provider
 * @author Georgi Georgiev georgi.georgiev@delta.bg
 * @package GLGeorgiev\LaravelOAuth2Client\Provider
 */
class Provider extends AbstractProvider {

    /**
     * @return mixed
     */
    public function urlAuthorize()
    {
        return Config::get('laravel-oauth2-client.server_url_authorize');
    }

    /**
     * @return mixed
     */
    public function urlAccessToken()
    {
        return Config::get('laravel-oauth2-client.server_url_access_token');
    }

    /**
     * @param AccessToken $token
     * @return string
     */
    public function urlUserDetails(AccessToken $token)
    {
        return Config::get('laravel-oauth2-client.server_url_user_details') . $token;
    }

    /**
     * @param object $response
     * @param AccessToken $token
     * @return User
     */
    public function userDetails($response, AccessToken $token)
    {
        $user = new User();

        $user->exchangeArray([
            'uid' => $response->uid,
            'email' => $response->email,
        ]);

        return $user;
    }

    /**
     * @param $response
     * @param AccessToken $token
     * @return mixed
     */
    public function userUid($response, AccessToken $token)
    {
        return $response->id;
    }

    /**
     * @param $response
     * @param AccessToken $token
     * @return mixed
     */
    public function userEmail($response, AccessToken $token)
    {
        return $response->email;
    }
}