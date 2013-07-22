<?php

if(USE_FIREPHP){$firephp->log('CURR - bootstrap.php');}

/**
 * APP GROUP CONFIGURATIONS
 * this serves as a way to have a group of apps that all share the same core configurations
 */
if(defined('APP_GROUP_CONFIGURATIONS') && APP_GROUP_CONFIGURATIONS != '')
	require_once ANTHRACITE_ROOT_PATH.'anthracite_configurations/'.APP_GROUP_CONFIGURATIONS.'.php';

/**
 * REGISTRY
 * Set up the registry class.
 */
require_once(MVC_CORE_PATH.'registry.php');
$reg = registry::singleton();
if(!is_object($reg)){die("Dying in /anthracite_mvc/bootstrap, line __LINE__, because I could not instantiate the Registry class.");}

/**
 * LOADER
 * for loader to work, the convention is: file name is $className.'.php'. 
 * Pass the class name to the Loader function.
 */
require_once MVC_CORE_PATH.'loader.php';
$Loader = Loader::singleton();
if(!$reg->set('loaderSingleton',$Loader)) die("Fatal error inserting Loader into Registry on line __LINE__ of bootstrap");

/**
 * UTILITIES
 * Get helper utilities
 */
require_once MVC_CORE_PATH.'utilities.php';

/**
 * PROTECTED & PUBLIC PAGES
 * Add list of protected pages (i.e. pages that require a log-in to be viewed) to registry
 */
if(!empty($protectedPages)){
	$reg->set('protectedPages',$protectedPages);
}
if(isset($$publicPages) && !empty($publicPages)){
	$reg->set('publicPages',$publicPages);
}

/**
 * DATABASE CONNECTION
 * Add database information (array) to the registry, values set in index.php 
 * If the database connection doesn't work here, the __construct() of CoreModel will also attempt to make a connection
 */

if(!$reg->set('db_conf',$DB)){die('Unable to save database configuration information to registry');}

$connString = $DB[ENVIRONMENT]['db_host'].':'.$DB[ENVIRONMENT]['db_port'].','.$DB[ENVIRONMENT]['db_user'].','.$DB[ENVIRONMENT]['db_pass'];

$conn = mysql_connect($DB[ENVIRONMENT]['db_host'],$DB[ENVIRONMENT]['db_user'],$DB[ENVIRONMENT]['db_pass']);
if(!$conn){die('Unable to connect to the database: '.mysql_error().', in bootstrap, line '.__LINE__.'.');}

$db_selected = mysql_select_db($DB[ENVIRONMENT]['db_name'],$conn);
if(!$db_selected){die('Unable to use the database: '.$this->dbname.' : '.mysql_error().' in bootstrap.');}

if(!$reg->set('databaseConnectionSingleton',$conn)){die('Unable to save the database connection to registry in boostrap.');}

/**
 * ERROR MANAGEMENT 
 * add errorManagement class to registry. The error management class was initialized in the index.php file
 * checks constants DEBUG and DEV_DEBUG and adjusts error reporting settings appropriately
 */

if(!$reg->set('errorSingleton', $Errors)) die("Fatal error inserting Error Management class into the Registry in line ".__LINE__." of bootstrap");

/**
 * REQUEST
 * the class Request's __construct function parses the server and post values. We'll need to clean parameters and 
 * POST values in later code
 */
$Request = $Loader->loadClass('request');
if($Request == FALSE || !$reg->set('requestSingleton',$Request)) die("Fatal error inserting Request class into the Registry at line __LINE__ of bootstrap");
if(USE_FIREPHP){$firephp->log($Request,'class $Request');}

/**
 * SESSION
 * Load the session class, which encapsulates the session variable and provides some helper functions.
 */

$Session = $Loader->loadClass('session');
if(!$reg->set('sessionSingleton',$Session)) die("Fatal error inserting Session class into the Registry at line __LINE__ of boostrap");

/**
 * AUTHENTICATION
 * Load the authentication library so it can be used in controllers and elsewhere.
 * @todo Create a UserRightsMgmt class? Include it in the authentication class?
 */
$Authentication = $Loader->loadClass('authentication');
$reg->set('authenticationSingleton',$Authentication);


/**
 * FRONT CONTROLLER
 * Last step is to load the Front Controller which takes care of dispatching the system.
 */
if(USE_FIREPHP){$firephp->log($_COOKIE,'CURR bootstrap.php, $_COOKIE at line '.__LINE__);}

if(!class_exists('coreController'))
	require_once MVC_CORE_PATH.'coreController.php';
	
$frontControllerName = 'frontController';
$FrontController = $Loader->loadClass($frontControllerName);
$FrontController->start();

?>