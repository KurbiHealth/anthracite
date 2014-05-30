<?php

class request{
	
	private static $instance;
	
	public  $values;
	private $postGlobals = NULL;
	private $getGlobals = NULL;
	private $requestGlobals = NULL;
	private $server = NULL;
	private $originalUrl = '';
	private $originalQuery = '';
	private $referralQuery = '';
	
	private $page;
	private $action;
	private $params;
	
	private $isAjaxStatus;
	private $isNoTemplateStatus;
	
	private function __construct(){
		if(!defined('USE_FIREPHP'))
			define('USE_FIREPHP',FALSE);
		if(USE_FIREPHP)
			global $firephp;
		
		if(USE_FIREPHP){$firephp->log(' CURR - request.php, __construct');}
		
		// delete original global variables, $_SERVER, etc.
		$this->processCurrentRequest();
		
		// get Original URL (url requested)
		if(isset($this->server['SCRIPT_URI']))
			$this->originalUrl = $this->server['SCRIPT_URI'];
		elseif(isset($this->server['REQUEST_URI']))
			$this->originalUrl = $this->server['REQUEST_URI'];
		
		// get Referring URL
		if(isset($this->server['HTTP_REFERER'])){
			$tempReferer = $this->server['HTTP_REFERER']; // SITE_ROOT_URL
			$this->referralQuery = substr($tempReferer,strlen(ROOT_URL),(strlen($tempReferer)-strlen(ROOT_URL)));
		}else{
			$this->referralQuery = '';
		}
		
		// get Full Original Query
		$this->originalQuery = $this->server['QUERY_STRING'];
		if(($this->originalQuery == '' || $this->originalQuery == '/') && DEFAULT_PAGE != '')
		 	$this->originalQuery = DEFAULT_PAGE;
		$url = $this->originalUrl;
		
		// $queryString goes to FrontController, which uses it to choose how to handle request
		$queryString = $this->originalQuery;
		
		/**
		 * GET PARAMETERS FROM QUERY
		 */
		$params = explode('/',$queryString); // $params[0] will be blank if there is a "/" first in theß string
		// There could be an empty url segment in the first position if index.php hasn't
		// been removed from the url via .htaccess
		if(empty($params[0]) || $params[0]==''){
			$startKey = 1;
			unset($params[0]);
		}else{
			$startKey = 0;
		}
		// The actual parsing done here. 
		$page = NULL; $action = NULL; $data = NULL;
		$page = $params[$startKey];

		$this->page = $page;

		if(!empty($params[$startKey+1]))
			$action = $params[$startKey+1];
		else
			$action = NULL;

		$this->action = $action;
		
		if(!empty($params[$startKey + 2])){
			// there may be data in the 3rd segment of url ($params[3]), followed by data elements
			unset($params[$startKey]);
			unset($params[$startKey+1]);
			// There could be an infinite number of url segments, so go through each one and add to the $data array. Omit "no_template" if it
			// is one of the url segments. That's a message to the framework, not data
			foreach($params as $key=>$value){
				if($value == 'no_template')
					$this->isNoTemplate = TRUE;
				else
					$data[] = $value;
			}
		}else
		 	$data = NULL;

		$this->params = $data;

		/**
		 * Check for Ajax request
		 */
		if(substr($action,0,4) == 'ajax'){
		  	$this->isAjaxStatus = TRUE;
			preg_match('`ajax_(.*)`',$action,$match);
			$this->action = $match[1];
			error_reporting(0);
		}

		/**
		 * Check for NoTemplate request
		 */
		if(substr($action,0,10) == 'notemplate'){
			$this->isNoTemplateStatus = TRUE;
			preg_match('`notemplate_(.*)`',$action,$match);
			$this->action = $match[1];
			error_reporting(0);
		}
	}
	
	function processCurrentRequest($destroy=FALSE){
		$this->requestGlobals = $_REQUEST;
		$this->postGlobals = $_POST;
		$this->server = $_SERVER;
		$this->getGlobals = $_GET;
		
		if($destroy)
			$this->_unsetAll();
		
		return TRUE;
	}
	
	private function _unsetAll(){
		unset($_REQUEST);
		unset($_POST);
		unset($_SERVER);
		unset($_GET);
		//unset($_FILES);
	}

//		"IS" FUNCTIONS		//

	function isPost(){
		if(isset($this->postGlobals))
			return TRUE;
		else
			FALSE;
	}
	
	public function isGet(){
		if(isset($this->getGlobals))
			return TRUE;
		else
			return FALSE;
	}
	
	public function isAjax(){
		return $this->isAjaxStatus;
	}
	
	public function isNoTemplate(){
		return $this->isNoTemplateStatus;
	}

//		"GET" FUNCTIONS		//

	public function getReferringQuery(){
		return $this->referralQuery;
	}
	
	public function getPost(){
		return $this->postGlobals;
	}
	
	public function getGet(){
		return $this->getGlobals;
	}

	public function getRequest(){
		return $this->requestGlobals;
	}
	
	public function getServer(){
		return $this->server;
	}
	
	public function getPageVar(){
		return $this->page;
	}
	
	public function getActionVar(){
		return $this->action;
	}
	
	public function getParams(){
		return $this->params;
	}
	
	public function getValues(){
		return $this->values;
	}
	
	public function getOriginalUrl(){
		return $this->originalUrl;
	}
	
	public function getOriginalQuery(){
		return $this->originalQuery;
	}

//		UTILITY FUNCTIONS		//

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

?>