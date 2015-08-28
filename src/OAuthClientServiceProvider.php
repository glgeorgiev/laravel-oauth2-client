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
        $loginPath = Config::get('laravel-oauth2-client.client_app_uri');
        $loginPath = explode('/', $loginPath, 4)[3];

        $router->get($loginPath, function() {

            $provider = new Provider([
                'clientId'      => Config::get('laravel-oauth2-client.client_app_id'),
                'clientSecret'  => Config::get('laravel-oauth2-client.client_app_secret'),
                'redirectUri'   => Config::get('laravel-oauth2-client.client_app_uri'),
                'scopes'        => Config::get('laravel-oauth2-client.client_app_scopes'),
            ]);

            if (! Request::input('code')) {

                $authUrl = $provider->getAuthorizationUrl();

                if (Request::input('if_not_authenticated')) {
                    $authUrl .= '&if_not_authenticated=' .
                        Request::input('if_not_authenticated');
                }

                if (Request::input('empty_return')) {
                    Session::put('empty_return', 1);
                }

                Session::put('oauth2state', $provider->getState());

                return redirect($authUrl);
            } elseif ((! Request::input('state')) ||
                (Request::input('state') != Session::pull('oauth2state'))) {
                Session::pull('current_url');
                Session::pull('empty_return');
                die('Invalid state!');
            } else {
                try {
                    $token = $provider->getAccessToken('authorization_code', [
                        'code'      => Request::input('code'),
                        'back_url'  => Session::pull('current_url'),
                    ]);

                    $resourceOwner = $provider->getResourceOwner($token);

                    $model = Config::get('auth.model');

                    $user = $model::find($resourceOwner->getId());

                    Auth::login($user);

                    if (Session::has('empty_return')) {
                        Session::pull('empty_return');
                        return '';
                    }

                    if (Session::has('current_url')) {
                        return redirect(Session::pull('current_url'));
                    }
                    if (Config::get('laravel-oauth2-client.redirect_is_route')) {
                        return redirect(route(Config::get('laravel-oauth2-client.redirect_route')));
                    }
                    return redirect(Config::get('laravel-oauth2-client.redirect_path'));
                } catch (Exception $e) {
                    die('Something went wrong!');
                }
            }
        });
    }

    /**
     * The route responsible for logging out the user
     *
     * @param $router
     * @return \Response
     */
    private function logoutRoute($router)
    {
        $router->get(Config::get('laravel-oauth2-client.client_app_logout'), function() {
            Auth::logout();
            if (Request::input('empty_return')) {
                return '';
            }
            if (Config::get('laravel-oauth2-client.redirect_is_route')) {
                return redirect(route(Config::get('laravel-oauth2-client.redirect_route')));
            }
            return redirect(Config::get('laravel-oauth2-client.redirect_path'));
        });
    }

}