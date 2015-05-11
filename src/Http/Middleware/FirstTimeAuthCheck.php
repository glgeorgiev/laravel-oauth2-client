<?php namespace GLGeorgiev\LaravelOAuth2Client\Http\Middleware;

use Auth;
use Closure;
use Config;
use Session;

/**
 * Class FirstTimeAuthCheck
 * @author Georgi Georgiev georgi.georgiev@delta.bg
 * @package DeltaCMS\Http\Middleware
 */
class FirstTimeAuthCheck {

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
			if(! Auth::check()) {
				Session::put('current_url', $request->url());
				return redirect(Config::get('laravel-oauth2-client.login_path'));
			}
		}

		return $next($request);
	}

}