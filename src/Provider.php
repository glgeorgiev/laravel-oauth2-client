<?php namespace GLGeorgiev\LaravelOAuth2Client;

use Config;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Provider
 * @author Georgi Georgiev georgi.georgiev@delta.bg
 * @package GLGeorgiev\LaravelOAuth2Client\Provider
 */
class Provider extends AbstractProvider
{

    /**
     * @return mixed
     */
    public function getBaseAuthorizationUrl()
    {
        return Config::get('laravel-oauth2-client.server_url_authorize');
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return Config::get('laravel-oauth2-client.server_url_access_token');
    }

    /**
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return Config::get('laravel-oauth2-client.server_url_user_details') .
            '?access_token=' . $token->getToken();;
    }

    /**
     * @return array
     */
    protected function getDefaultScopes()
    {
        return ['uid'];
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new ResourceOwner($response);
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (! empty($data['error'])) {
            throw new IdentityProviderException($data['error'], $data['code'], $data);
        }
    }

}