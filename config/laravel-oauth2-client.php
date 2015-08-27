<?php

return [
	'server_url_authorize' 		=> 'https://example.com/authorize',
	'server_url_access_token' 	=> 'https://example.com/access_token',
	'server_url_user_details' 	=> 'https://example.com/user_details',

	'client_app_id'				=> env('APP_ID', 'id'),
	'client_app_secret'			=> env('APP_SECRET', 'secret'),
	'client_app_url' 			=> 'https://example.com/login',
	'client_app_scopes'			=> ['uid'],

	'login_path'				=> '/login',

	'redirect_is_route'			=> true,
	'redirect_route'            => 'admin.index',
	'redirect_path'				=> 'admin/dashboard',
];