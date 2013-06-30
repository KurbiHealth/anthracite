<?php

class authentication{
	private static $instance;
	public $currUserId;
	public $isLoggedIn=NULL;
	private $currSession;
	
	private function __construct(){}
	
//			LOG-IN FUNCTIONS			//

	public function checkIsUser(){
		$reg = getRegistryClass();
		$Request = $reg->get('requestSingleton');
		
		/**
		 * Check if Post is set, meaning there's a log-in action going on, and then check the database.
		 */
		$post = $Request->getPost();
		$dbConn = $reg->get('databaseConnectionSingleton');

/**
 * @todo having the field names of the form manually coded here limits portability, i.e. another app might have 
 * different field names. Need to come up with a way of making the field names a configuration value. One possibility 
 * would be to have the next few lines of code be a helper function that's configured from the index file.
 */
		if($post['email_address'] == '' || $post['password'] == '')
			return FALSE;
		$sql = 'SELECT * FROM people WHERE email=\''.$post['email_address'].'\' AND password=\''.$post['password'].'\' LIMIT 1';

		$result = mysql_query($sql,$dbConn);
		if(is_resource($result)){
			if(mysql_num_rows($result) > 0){
				$userInfo = mysql_fetch_assoc($result);
				$reg->set('userRecord',$userInfo);
				return $userInfo;
			}else
				return FALSE;
		}else{
			return FALSE;
		}
	}

	public function checkSignUpIsValid(){
		$reg = getRegistryClass();
		$Request = $reg->get('requestSingleton');
		
		/**
		 * Check if Post is set, meaning there's a sign-up action going on
		 */
		$post = $Request->getPost();
		if(!empty($post))
			return TRUE;
		else
			return FALSE;
	}
	
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
	
	public function checkUserCanAccessPage(){
		$reg = getRegistryClass();
		$Request = $reg->get('requestSingleton');
		$page = $Request->getPageVar();
		
		// get user ID from session
		
		// get user rights from database
		
		// if current page is in user rights (i.e. user belongs to a group that can see that page), return TRUE
		
		return TRUE; // this function not used yet, so always returns TRUE, 8/30/2012 ME
	}

//			GET & SET FUNCTIONS			//

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
	
// 			UTILITY FUNCTIONS 			//

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
