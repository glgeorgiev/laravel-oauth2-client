<?php namespace GLGeorgiev\LaravelOAuth2Client;

use Auth;
use Config;
use Exception;
use Request;
use Session;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

use GLGeorgiev\LaravelOAuth2Client\Provider\Provider;

class OAuthServerServiceProvider extends ServiceProvider {

    protected $defer = false;

    public function register()
    {
        //
    }

    public function boot(Router $router)
    {
        $router->get(Config::get('laravel-oauth2-client.client_app_url'), function() {

            $provider = new Provider([
                'clientId'      => Config::get('laravel-oauth2-client.client_app_id'),
                'clientSecret'  => Config::get('laravel-oauth2-client.client_app_secret'),
                'redirectUri'   => Config::get('laravel-oauth2-client.client_app_url'),
                'scopes'        => ['uid', 'email'],
            ]);

            if (! Request::input('code')) {

                $authUrl = $provider->getAuthorizationUrl();

                Session::put('oauth2state', $provider->state);

                return redirect($authUrl);
            } elseif ((! Request::input('state')) ||
                (Request::input('state') != Session::pull('oauth2state'))) {

                die('Invalid state');
            } else {

                $token = $provider->getAccessToken('authorization_code', [
                    'code' => Request::input('code')
                ]);

                try {
                    $userDetails = $provider->getUserDetails($token);
                    
                    $model = Config::get('auth.model');

                    $user = $model::find($userDetails->uid);

                    Auth::login($user);

                    return redirect('admin/dashboard');
                } catch (Exception $e) {
                    die('Something went wrong');
                }
            }
        });
    }
}