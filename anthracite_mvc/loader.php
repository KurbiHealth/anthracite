<?php

class Loader{
	private static $instance;
	
	function __construct(){
	}
	
	public static function singleton(){
		if(!isset(self::$instance)){
			$className = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
	}
	
	function loadClass($className,$params=''){
		// @todo Consider adding another parameter, which is params to pass to $className constructor
		$path = MVC_CORE_PATH;
		$fileName = $path.$className.'.php';
		if(is_file($fileName)){
			require_once $fileName;
			$fileContents = file_get_contents($fileName);
		}else{
			return false;
		}
		// check that $className is a valid class
		if(preg_match("`class\s*?$className`",$fileContents) > 0){
			if(method_exists($className,'singleton'))
				$obj = call_user_func(array($className,'singleton'));
			else {
				$obj = new $className;
			}
			return $obj;
		}else{
			return false;
		}
	}
	
	function loadClassNonSingleton(){}
	
	function loadAppController($className){
		$path = MVC_APP_PATH.'app/controllers'; // @todo can have classes in different directories
		$fileName = $path.'/'.$className.'.php';

		if(is_file($fileName))
			require_once $fileName;
		else
			return false;
		$fileContents = file_get_contents($fileName);
		if(preg_match("`class\s?$className`",$fileContents) > 0){
			if(method_exists($className,'singleton'))
				$obj = class_user_func(array($className,'singleton'));
			else
				$obj = new $className;
			return $obj;
		}else{
			return false;
		}
	}

	function loadAppModel($name){
		$path = MVC_APP_PATH.'app/models/';
	
		if(!class_exists('coreModel')){
			require_once MVC_CORE_PATH.'coreModel.php';
		}
		
		// make sure class is in memory
		if(!class_exists('appModel')){
			require_once $path.'appModel.php';
		}
	
		// instantiate the class
		$fileName = $path.$name.'.php';
		if(is_file($fileName))
			require_once $fileName;
		else
			return FALSE;

		$obj = new $name;
		return $obj;
	}
	
	function loadAppView($name){
		$path = MVC_APP_PATH.'app/views';
		if(empty($name))
			$name = 'appView';
		if(!class_exists('coreView'))
			require_once MVC_CORE_PATH.'coreView.php';
		if($name != 'appView' && !class_exists('appView'))
			require_once MVC_APP_PATH.'app/views/appView.php';
		$fileName = $path.'/'.$name.'.php';
		if(is_file($fileName))
			require $fileName;
		else
			return FALSE;
		$obj = new $name;
		return $obj;
	}

	function loadAppLibraryClass($name){
		// check if it's in app's library file, else check anthracite_library file
		$appPath = MVC_APP_PATH.'library';
		$mvcPath = SERVER_ROOT_PATH.'anthracite_library/';
		if(empty($name))
			return FALSE;
		// need to know the path for that particular library, use array from bootstrap??
		$fileName = $path.$name.'.php';
		if(is_file($fileName))
			require $fileName;
		else
			return FALSE;
		$obj = new $name;
		return $obj;
	}
	
	function loadController($name){
		if(!class_exists('appController')){
			require_once MVC_APP_PATH.'app/controllers/appController.php';
		}
		
		if(is_file(MVC_APP_PATH.'app/controllers/'.$name.'.php')){
			if(!class_exists($name.'Controller'))
				require_once MVC_APP_PATH.'app/controllers/'.$name.'.php';
			$class = $name.'Controller';
			$obj = new $class;
			return $obj;
		}else{
			return FALSE;
		}
	}
	
	function __clone(){
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}
	function __wakeup(){
		trigger_error('Unserializing is not allowed.',E_USER_ERROR);
	}
}

?>