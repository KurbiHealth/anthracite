<?php

class authentication{
	private static $instance;
	
	private $firephp = '';
	private $currSession;
	private $queryString = '';
	private $page = '';
	private $action = '';
	private $Request = '';
	private $Session = '';
	
	public $currUserId;
	public $isLoggedIn = NULL;
	
	private function __construct(){
		global $firephp;
		$this->firephp = $firephp;
		$Reg = registry::singleton();
		$this->Request = $Reg->get('requestSingleton');
		$this->Session = $Reg->get('sessionSingleton');
	}
	
/**
 * MAIN FUNCTIONS
 */

	public function pageCanBeSentToUser(){
		$return = TRUE;

		if(USE_FIREPHP){$this->firephp->log( 'in class Authentication, line '.__LINE__ );}
		
		// if user is requesting the sign up or sign in pages, then this is allowed
		if($this->queryString == SIGN_IN_URL || $this->queryString == SIGN_UP_URL){			
			if(USE_FIREPHP){$this->firephp->log( $this->queryString,'--Page requested is sign_in or sign_up, CONTINUING THROUGH, line '.__LINE__ );}
			$return = TRUE;
		}

		// if protected page, and no session established (user hasn't logged in or doesn't have an account), do not allow
		if($this->pageIsProtected() && ($this->Session->getLoggedInStatus() == FALSE)){
			if(USE_FIREPHP){$this->firephp->log('--Page is protected, and not logged in, line '.__LINE__);}
			$this->Session->set('queryString',$this->queryString);
			$return = FALSE;
		}
		
		// if is protected, and session exists, allow page
		if($this->pageIsProtected() && $this->Session->getLoggedInStatus() == TRUE){
			if(USE_FIREPHP){$this->firephp->log('--Page is protected, session exists, continue on, line '.__LINE__);}
			$return = TRUE;
		}
		
		// if page is not protected, allow page
		if(!$this->pageIsProtected()){
			if(USE_FIREPHP){$this->firephp->log('--Page is NOT protected, continue on, line '.__LINE__);}
			$return = TRUE;
		}

		return $return;
	}

	public function skipFrontcontrollerAuthentication(){
		return FALSE;
	}
	
	public function postAuthenticationActions(){
		return TRUE;
	}
	
/**
 * HELPERS
 */

	public function pageIsProtected(){
		$reg = getRegistryClass();
		$Request = $reg->get('requestSingleton');
		$page = $Request->getPageVar();
		$protectedPages = $reg->get('protectedPages');

		// if it's in the array it means it's protected, so proceed to see whether the SKIP_LOGIN_FOR_DEV is defined 
		// and TRUE, which would tell the app to behave as if the page isn't protected
		if(in_array($page, $protectedPages)){//echo 'line-47<br/>';
			if(defined(SKIP_LOGIN_FOR_DEV)){//echo 'line -47<br/>';
				if(SKIP_LOGIN_FOR_DEV)
					return FALSE;
			}else{//echo 'line -51<br/>';
				return TRUE;
			}
		}else{//echo 'line -54<br/>';
			// if not in the array, it means the page is NOT protected
			return FALSE;
		}
	}

/**
 * GET & SET
 */

	public function get($key){
		if(!isset($this->currSession[$key]) || $this->currSession[$key] == '')
			return FALSE;
		else
			return $this->currSession[$key];
	}
	
	public function set($key,$value,$override=TRUE){
		if(isset($this->currSession[$key]) && $this->currSession[$key] != '' && $override == FALSE)
			return FALSE;
		else{
			$this->currSession[$key] = $value;
			return TRUE;
		}
	}
	
	public function setInitialVariables($queryString,$page,$action){
		$this->queryString = $queryString;
		$this->page = $page;
		$this->action = $action;
	}
	
/**
 * MISC
 */

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