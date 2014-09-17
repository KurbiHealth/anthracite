<?php

class session{
	private static $instance;
	public $currUserId;
	public $currPatientId;
	public $isLoggedIn;
	private $currSession;
	
	private function __construct(){
		global $firephp;
		if(USE_FIREPHP){$firephp->log('CURR session.php->__construct() at '.__LINE__);}
		$this->startSession();
	}
	
// 		SESSION-SPECIFIC FUNCTIONS		//

	public function startSession(){
		global $firephp;
		
		// reset variable(s) that might be there from previous page visit
		$_SESSION['queryString'] = '';
		
		if(!isset($_SESSION['loggedIn']) || empty($_SESSION['loggedIn']))
			$this->isLoggedIn = FALSE;
		else
			$this->isLoggedIn = TRUE;
	}

	public function getLoggedInStatus(){
		// this needs to be expanded to check whether session has expired or not
		
		// THESE LINES ONLY WORK IN 5.4
		// PHP_SESSION_NONE if sessions are enabled, but none exists.
		// PHP_SESSION_ACTIVE if sessions are enabled, and one exists.
		if(session_status() == PHP_SESSION_NONE){
			return FALSE;
		}else{
			if($this->isLoggedIn == TRUE){
				return TRUE;
			}elseif($this->isLoggedIn == FALSE){
				return FALSE;
			}
		}
	}
	
	public function setFlashMessage($type = '',$title = '',$message){
		// $message should be an array like:
		// 'type' => $type,
		// 'title' => $title,
		// 'body' => $payload
		$_SESSION['flashMessage'] = array(
			'body' => $message,
			'type' => $type,
			'title' => $title
		);
	}
	
	public function getFlashMessage(){
		if(isset($_SESSION['flashMessage']) && $_SESSION['flashMessage'] != ''){
			$msg = $_SESSION['flashMessage'];
			// clear out the flashmessage, only should be 1 in session at any point in time
			$_SESSION['flashMessage'] = '';
			return $msg;
		}
	}

//		GET & SET VALUES		//
	
	public function get($key){
		if(!isset($_SESSION[$key]) || $_SESSION[$key] == '')
			return FALSE;
		else
			return $_SESSION[$key];
	}
	
	public function set($key,$value,$override=TRUE){
		if(isset($_SESSION[$key]) && $_SESSION[$key] != '' && $override == FALSE)
			return FALSE;
		else{
			$_SESSION[$key] = $value;
			return TRUE;
		}
	}

// 		UTILITY FUNCTIONS		//
	public static function singleton(){
		if(!isset(self::$instance)){
			$className = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
	}
	
	public function __clone(){
		trigger_error('Cloning not allowed', E_USER_ERROR);
	}
	public function __wakeup(){
		trigger_error('Unserializing not allowed');
	}
}
