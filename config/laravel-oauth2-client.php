<?php

return [
	'server_url_authorize' 		=> env('URL_AUTHORIZE',     'https://server.com/authorize'),
	'server_url_access_token' 	=> env('URL_ACCESS_TOKEN',  'https://server.com/access_token'),
	'server_url_user_details' 	=> env('URL_USER_DETAILS',  'https://server.com/user_details'),

	'client_app_id'				=> env('APP_ID',            'id'),
	'client_app_secret'			=> env('APP_SECRET',        'secret'),
	'client_app_uri' 			=> env('APP_URI',           'https://client.com/login'),
	'client_app_logout'			=> env('APP_LOGOUT',		'/logout'),
	'client_app_scopes'			=> ['uid'],

	'redirect_is_route'			=> false,
	'redirect_route'            => 'home',
	'redirect_path'				=> 'home',
];