<?php

class errorManagement{
	public $lastEnvironment;
	private static $instance;
	
	public function __construct(){

		/*if(DEBUG || DEV_DEBUG){
			error_reporting(E_ALL);
			ini_set('display_errors','on');
		}*/
		error_reporting(E_ALL);

		//fastcgi_finish_request();
		register_shutdown_function(array($this, 'shutdown_handler'));
	}
	
	public function shutdown_handler(){
		$lastErr = error_get_last();
		
		if(!empty($lastErr)){
			if(ENVIRONMENT == 'dev'){
				if(ob_get_contents() != ''){
					ob_end_clean();
				}
				echo '<h1>LAST ERROR</h1>';
				echo '<pre>';
				var_dump($lastErr);
				echo '</pre>';
			}
			if(ENVIRONMENT == 'prod'){
				if(ob_get_contents() != ''){
					ob_clean();
				}
				$body = '<div id="error-wrapper">
				<h1>ERROR</h1>
				<hr/>
				<p>Unfortunately we had an issue with this page, but our programmers have been alerted and are working on it. 
				Please hit the back button on your browser to go the page you were on previously.</p>
				</div>';
				//$body .= implode('//', $lastErr);
				if(defined(AJAX_REQUEST) && AJAX_REQUEST == FALSE)
					include(MVC_APP_PATH.'app/views/master_template.php');
			}
		}
	}
	
//-----------------------------------------------------------------------------------------------------------------

	public static function singleton(){
		if(!isset(self::$instance)){
			$className = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
	}
	
	public function setupDevDebug($devs = array(), $session){
		if(in_array($session->currUserId,$devs) && DEV_DEBUG){
			// set up any extra functionality for dev debugging on production, if needed, hopefully this will never be needed
			// and I can hold up my head because we have a dev environment
		}
	}
	
	public function setEnv($env = NULL){
		$this->lastEnvironment = $env;
	}
	
	public function appDie($msg = ''){
		if(DEV_DEBUG && DEBUG){
			if(!empty($this->lastEnvironment)){
				echo 'Error happened here:<br/><br/>';
				var_dump($this->lastEnvironment);
			}
			die($msg);
		}
	}
	
	public function __clone(){
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}
	
	public function __wakeup(){
		trigger_error('Unserializing is not allowed',E_USER_ERROR);
	}
	
	
}

?>