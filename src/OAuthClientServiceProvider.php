<?php namespace GLGeorgiev\LaravelOAuth2Client;

use Auth;
use Config;
use Exception;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Request;
use Session;

/**
 * Class OAuthClientServiceProvider
 * @author Georgi Georgiev georgi.georgiev@delta.bg
 * @package GLGeorgiev\LaravelOAuth2Client
 */
class OAuthClientServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/laravel-oauth2-client.php';
        $this->mergeConfigFrom($configPath, 'laravel-oauth2-client');
    }

    /**
     * Bootstrap application services.
     *
     * @param Router $router
     */
    public function boot(Router $router)
    {
        $configPath = __DIR__ . '/../config/laravel-oauth2-client.php';
        $this->publishes([$configPath => config_path('laravel-oauth2-client.php')], 'config');

        $this->loginRoute($router);

        $this->logoutRoute($router);
    }

    /**
     * The route responsible for logging in the user
     *
     * @param Router $router
     * @return \Response
     */
    private function loginRoute(Router $router)
    {
        $router->get(Config::get('laravel-oauth2-client.client_app_login'), function() {

            $provider = new Provider([
                'clientId'      => env('APP_ID'),
                'clientSecret'  => env('APP_SECRET'),
                'redirectUri'   => env('APP_HOST') .
                                       Config::get('laravel-oauth2-client.client_app_login'),
                'scopes'        => Config::get('laravel-oauth2-client.client_app_scopes'),
            ]);

            if (! Request::input('code')) {

                $authUrl = $provider->getAuthorizationUrl();

                if (Request::input('target_url')) {
                    $authUrl .= '&target_url=' .
                        Request::input('target_url');
                } else {
                    $authUrl .= '&target_url=' .
                        Config::get('laravel-oauth2-client.default_redirect');
                }

                if (Request::input('auth_checkup')) {
                    $authUrl .= '&auth_checkup=1';
                }

                Session::put('oauth2state', $provider->getState());

                return redirect($authUrl);
            } elseif ((! Request::input('state')) ||
                (Request::input('state') != Session::pull('oauth2state'))) {
                Session::pull('current_url');
                die('Invalid state!');
            } else {
                try {
                    $token = $provider->getAccessToken('authorization_code', [
                        'code'      => Request::input('code'),
                    ]);

                    $resourceOwner = $provider->getResourceOwner($token);

                    $model = Config::get('auth.model');

                    if (is_null($model)) {
                        $model = Config::get('auth.providers.users.model');
                    }

                    if (is_null($model)) {
                        die('Unable to determine model class');
                    }

                    $user = $model::find($resourceOwner->getId());

                    Auth::login($user);

                    if (Request::input('target_url')) {
                        return redirect(Request::input('target_url'));
                    }

                    return redirect(Config::get('laravel-oauth2-client.default_redirect'));
                } catch (Exception $e) {
                    die('Something went wrong!');
                }
            }
        });
    }

    /**
     * The route responsible for logging out the user
     *
     * @param Router $router
     * @return \Response
     */
    private function logoutRoute(Router $router)
    {
        $router->get(Config::get('laravel-oauth2-client.client_app_logout'), function() {
            Auth::logout();
            
            Session::forget('not_first');

            if (Request::input('target_url')) {
                return redirect(Request::input('target_url'));
            }

            return redirect(Config::get('laravel-oauth2-client.default_redirect'));
        });
    }

}
