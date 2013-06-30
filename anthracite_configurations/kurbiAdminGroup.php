<?php

if(!defined('PHP_BASE_PATH')) define ('PHP_BASE_PATH', '');

define('DEFAULT_PAGE','/dashboard/home');
					
$developers[] = 'meckman'; // a liner per user name of programmers that will see values from constant above

define('AUTO_SESSION', TRUE); // if true session created for every page call, if false then control given to the Session class or the Controller class

define('USER_GROUPS_IN', 'database'); // if 'database' user rights/groups loaded from database; for now that's  the only option, but could use 'ldap' in future

$DB['dev']['db_host'] = 'localhost';
$DB['dev']['db_port'] = '8889';
$DB['dev']['db_user'] = 'root';
$DB['dev']['db_pass'] = 'sR03ttg3r12:)';
$DB['dev']['db_name'] = 'kurbi';

$DB['prod']['db_host'] = '';
$DB['prod']['db_port'] = '3306';
$DB['prod']['db_user'] = '';
$DB['prod']['db_pass'] = '';
$DB['prod']['db_name'] = '';

$GLOBALS['errors'] = array();

define('PATH_TO_MVC_LIBRARIES',SERVER_ROOT_PATH.'anthracite_libraries/');
define('PATH_TO_APP_LIBRARIES',MVC_APP_PATH.'libraries/');

$protectedPages = array(
	'dashboard',
	'users'
);

define('SIGN_IN_URL','/site/sign_in/no_template');

define('ROOT_URL','http://kurbi:8888/');

?>