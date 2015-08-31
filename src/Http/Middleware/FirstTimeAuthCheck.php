<?php namespace GLGeorgiev\LaravelOAuth2Client\Http\Middleware;

use Auth;
use Closure;
use Config;
use Session;

/**
 * Class FirstTimeAuthCheck
 * @author Georgi Georgiev georgi.georgiev@delta.bg
 * @package GLGeorgiev\LaravelOAuth2Client\Http\Middleware
 */
class FirstTimeAuthCheck
{

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if (! Session::has('not_first')) {
			Session::put('not_first', true);
			if(! Auth::check() && ! in_array($request->path(), [
                    Config::get('laravel-oauth2-client.client_app_login'),
                    Config::get('laravel-oauth2-client.client_app_logout'),
                ])) {
				return redirect(Config::get('laravel-oauth2-client.client_app_host') .
                    Config::get('laravel-oauth2-client.client_app_login') .
                    '?target_url=' . $request->url() . '&auth_checkup=1');
			}
		}

		return $next($request);
	}

}
