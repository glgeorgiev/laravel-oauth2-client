<?php namespace GLGeorgiev\LaravelOAuth2Client;

use Auth;
use Config;
use Exception;
use Request;
use Session;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

use GLGeorgiev\LaravelOAuth2Client\Provider\Provider;

class OAuthClientServiceProvider extends ServiceProvider {

    protected $defer = false;

    public function register()
    {
        $configPath = __DIR__ . '/../config/laravel-oauth2-client.php';
        $this->mergeConfigFrom($configPath, 'laravel-oauth2-client');
    }

    public function boot(Router $router)
    {
        $configPath = __DIR__ . '/../config/laravel-oauth2-client.php';
        $this->publishes([$configPath => config_path('laravel-oauth2-client.php')], 'config');
        
        $router->get(Config::get('laravel-oauth2-client.login_path'), function() {

            $provider = new Provider([
                'clientId'      => Config::get('laravel-oauth2-client.client_app_id'),
                'clientSecret'  => Config::get('laravel-oauth2-client.client_app_secret'),
                'redirectUri'   => Config::get('laravel-oauth2-client.client_app_url'),
                'scopes'        => Config::get('laravel-oauth2-client.client_app_scopes'),
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

                    if (Config::get('laravel-oauth2-client.redirect_is_route')) {
                        return redirect(route(Config::get('laravel-oauth2-client.redirect_route')));
                    }
                    return redirect(Config::get('laravel-oauth2-client.redirect_path'));
                } catch (Exception $e) {
                    die('Something went wrong');
                }
            }
        });
    }
}