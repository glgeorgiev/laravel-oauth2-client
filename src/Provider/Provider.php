<?php namespace GLGeorgiev\LaravelOAuth2Client\Provider;

use Config;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Entity\User;

class Provider extends AbstractProvider {

    public function urlAuthorize()
    {
        return Config::get('laravel-oauth2-client.server_url_authorize');
    }

    public function urlAccessToken()
    {
        return Config::get('laravel-oauth2-client.server_url_access_token');
    }

    public function urlUserDetails(AccessToken $token)
    {
        return Config::get('laravel-oauth2-client.server_url_user_details') . $token;
    }

    public function userDetails($response, AccessToken $token)
    {
        $user = new User();

        $user->exchangeArray([
            'uid' => $response->id,
            'email' => $response->email,
        ]);

        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        return $response->id;
    }

    public function userEmail($response, AccessToken $token)
    {
        return $response->email;
    }
}