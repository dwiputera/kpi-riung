<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// pages
$route['quiz']                  = 'quiz/index';
$route['quiz/host']             = 'quiz/host';
$route['quiz/host/(:num)']      = 'quiz/host/$1';        // NEW: buka host untuk quiz tertentu
$route['quiz/play']             = 'quiz/play';
$route['quiz/leaderboard/(:num)'] = 'quiz/leaderboard/$1';

// apis - player/host (seperti sebelumnya)
$route['quiz/api/host_state']['get']   = 'quiz/api_host_state';
$route['quiz/api/join_by_pin']['post'] = 'quiz/api_join_by_pin';
$route['quiz/api/current']['get']      = 'quiz/api_current';
$route['quiz/api/answer']['post']      = 'quiz/api_answer';
$route['quiz/api_leaderboard/(:num)']['get'] = 'quiz/api_leaderboard/$1';

$route['quiz/api/quiz_create']['post'] = 'quiz/api_quiz_create';
$route['quiz/api/start']['post']       = 'quiz/api_start';
$route['quiz/api/next']['post']        = 'quiz/api_next';
$route['quiz/api/end']['post']         = 'quiz/api_end';
$route['quiz/api/reset']['post']       = 'quiz/api_reset';
