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
	
	public function userIsSigningIn(){
		if($this->Request->getReferringQuery() == SIGN_IN_URL && $this->queryString == SIGN_IN_URL.'_result')
			return TRUE;
		else
			return FALSE;
	}
	
	public function signInUser(){
		// if current request is coming from the sign-in url, then use the ->checkIsUser function to check
		// whether the user has valid credentials
		// NOTE: $user at the next line will have the contents of the "people" table ONLY

		if(!SKIP_LOGIN_FOR_DEV){
			
			$user = $this->checkIsUser();
			if($user){
				if(USE_FIREPHP){$this->firephp->log($user,'Authentication.php, retrieved $user, signing in at line '.__LINE__);}
				$this->initializeUser($user);
				return TRUE;
			}else{
				$this->Session->set('queryString',$queryString);
				return FALSE;
			}
			
		}else{
			// skipping login requirement for dev purposes
			if(USE_FIREPHP){$this->firephp->log('--Skipping login for dev purposes, line '.__LINE__);}
		}
	}
	
	public function userIsSigningUp(){
		$this->firephp->log(array(
			'refer query' =>$this->Request->getReferringQuery(),
			'SIGN_UP_URL'=>SIGN_UP_URL,
			'queryString'=>$this->queryString ),
		'referring query, SIGN_UP_URL, queryString, SIGN_UP_URL_result');
		
		if($this->Request->getReferringQuery() == SIGN_UP_URL && $this->queryString == SIGN_UP_URL.'_result'){
			if($this->checkThatRequestIsPost()){
				$return = TRUE;
			}else{
				$return = FALSE;
			}
		}else
			$return = FALSE;
		$this->firephp->log($return,'$return in authentication.php at line '.__LINE__);
		return $return;
	}
	
	public function signUpUser(){
		if(USE_FIREPHP){$this->firephp->log('--At Authentication->signUpUser(), line '.__LINE__);}
		
		$post = $this->Request->getPost();
		if(USE_FIREPHP){$this->firephp->log($post,'--Retrieved Post info at line '.__LINE__);}
		 
		/**
		 * Do validation
		 */
		if(empty($post["password"]) || empty($post['password_confirmation']) || 
		$post["password"] != $post['password_confirmation'] || empty($post["email"])){
			$this->Session->setFlashMessage('Your information was incomplete. Please fill all boxes in the form.');
			redirect(ROOT_URL.SIGN_IN_URL);
		}
		
		/**
		 * Check to see if user is already in database
		 */
		$sql = 'SELECT * FROM people WHERE email=\''.$post['email'].'\'';
		$dbConn = getDbConnection();
		$result = mysql_query($sql,$dbConn);	
		$row = mysql_fetch_assoc($result);
		
		if($row != FALSE){
			// using "echo" sends back the message to the originating ajax call
			$this->Session->setFlashMessage( 'Your information is already in our database. Please sign-in with this email and your password. If you do not remember your password, please click on "Password Recovery".');
			redirect(ROOT_URL.SIGN_IN_URL);
		}
		if(USE_FIREPHP){$this->firephp->log('--Checked database for existing account at line '.__LINE__);}

		/**
		 * Do insertion into 'people' table
		 */
		$sql = "INSERT INTO people (first_name,last_name,email,password) VALUES ('{$post['first_name']}','{$post['last_name']}','{$post['email']}','{$post['password']}')";
		$result = mysql_query($sql,$dbConn);
		if(USE_FIREPHP){$this->firephp->log(array('$this'=>$this),'--SQL, inserted into "people" table at line '.__LINE__);}
		$peopleId = mysql_insert_id($dbConn);

		if(mysql_errno() > 0 || $peopleId == ''){
			if(USE_FIREPHP){$this->firephp->log($this->sqlError,'--Problem inserting into "people" table at line '.__LINE__);}
			if(ob_get_contents() != ''){ob_flush();}
			$this->Session->setFlashMessage('There was an error with our system, we are working on fixing it. Please try again.');
			redirect(ROOT_URL.SIGN_IN_URL);
		}
		if(USE_FIREPHP){$this->firephp->log('--SQL, finished inserting into "people" table at line '.__LINE__);}

		/**
		 * Do insertion into role table
		 */
		$sql = "INSERT INTO ".USER_ROLE." (person_id) VALUES ('{$peopleId}')";
		$result = mysql_query($sql,$dbConn);
		$roleId = mysql_insert_id($dbConn);
		if(mysql_errno() > 0 || $roleId == ''){
			if(USE_FIREPHP){$this->firephp->log($this,'--SQL error, inserting into "patients" table at '.__LINE__);}
			$this->Session->setFlashMessage('There was an error with our system, we are working on fixing it. Please try again..');
			redirect(ROOT_URL.SIGN_IN_URL);
		}

		// log the user in
		$user = $this->checkIsUser();
		$this->initializeUser($user);
		
		redirect(ROOT_URL.'/site/user_agreement');

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

 	public function initializeUser($user){
		$this->Session->set('loggedIn','y');
		$this->isLoggedIn = TRUE;
		$this->Session->set('userId',$user['id']);
		$this->currUserId = $user['id'];
		$this->Session->set('userPersonId',$user['person_id']);
		$this->Session->set('userRole',$user['role']);
 	}

	public function checkIsUser(){		
		/**
		 * Check if Post is set, meaning there's a log-in action going on, and then check the database.
		 */
		$post = $this->Request->getPost();
		$reg = registry::singleton();
		$dbConn = $reg->get('databaseConnectionSingleton');

/**
 * @todo having the field names of the form manually coded here limits portability, i.e. another app might have 
 * different field names. Need to come up with a way of making the field names a configuration value. One possibility 
 * would be to have the next few lines of code be a helper function that's configured from the index file.
 */
		if(!isset($post['email_address'])){
			if(isset($post['email'])){
				$post['email_address'] = $post['email'];
				unset($post['email']); 
			}else{
				$post['email_address'] = '';
			}
		}
		if($post['email_address'] == '' || $post['password'] == '')
			return FALSE;
		$sql = 'SELECT * FROM people WHERE email=\''.$post['email_address'].'\' AND password=\''.$post['password'].'\' LIMIT 1';
		$result = mysql_query($sql,$dbConn);
		if(is_resource($result)){
			if(mysql_num_rows($result) > 0){
				$userInfo = mysql_fetch_assoc($result);
				$userInfo['role'] = USER_ROLE;
			}else
				return FALSE;
		}else{
			return FALSE;
		}

		// get role info
		$sql = 'SELECT * FROM '.USER_ROLE.' WHERE person_id='.$userInfo['id'];
		if(USE_FIREPHP){$this->firephp->log($sql,'Authentication.php, $sql at '.__LINE__);}
		$result = mysql_query($sql,$dbConn);
		if(is_resource($result) && mysql_num_rows($result) > 0){
			$roleInfo = mysql_fetch_assoc($result);
			$userInfo['role'] = USER_ROLE;
			foreach($roleInfo as $key=>$value){
				$userInfo[$key] = $value;
			}
		}else{
			if(USE_FIREPHP){$this->firephp->log($result,'WARNING! in Authentication.php unable to get role fields at line '.__LINE__);}
			//die('Cound not get your information from the database in Authentication at line '.__LINE__);
			redirect(ROOT_URL.SIGN_UP_URL);
		}

		$reg->set('userRecord',$userInfo);
		return $userInfo;
	}

	public function checkThatRequestIsPost(){		
		$post = $this->Request->getPost();
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