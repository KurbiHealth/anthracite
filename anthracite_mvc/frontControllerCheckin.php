<?php

class frontControllerCheckin extends coreController{
	/**
	 * __construct
	 */
	 function __construct(){
	 	
	 }
	 
	 /**
	  * @param  
	  */
	 function start(){

	 	/**
		 * Get needed classes from the Registry. 
		 */
	 	$Reg = registry::singleton();
		$Reg->set('keyName',$value);
		
		$Request = $Reg->get('requestSingleton');
		$Error = $Reg->get('errorSingleton');
		$Loader = $Reg->get('loaderSingleton');
		$Session = $Reg->get('sessionSingleton');
		$Authentication = $Reg->get('authenticationSingleton');

		/**
		 * Parse out parameters from the url and populate the $page and $action variables. VERY IMPORTANT.
		 */
		$url = $Request->getOriginalUrl();
		$queryString = $Request->getOriginalQuery();
		$page = $Request->getPageVar();
		$action = $Request->getActionVar();
		
		if($page == '' || $action == '')
			die("Unable to get page_group and page values from url in Front Controller on line __LINE__.");
		else{

		}
		
		$data = $Request->getParams();

		$Session->set('currPageRequested',$page);

		/**
		 * Login functionality
		 * There is an array, set in index.php or in the configurations file in the folder "anthracite_configurations", 
		 * which lists which $page segments are protected, i.e. a user has to be logged in to see. Check if current 
		 * $page is in that array, and if so, see if user is logged in. If not, redirect to a log-in page.
		 * 
		 * If the request is coming to sign_up_result, it means that they just logged in. Check to see if there is a 
		 * session variable set with an originalQuery, which means the user wanted to go to another page and was 
		 * redirected because their session wasn't valid. If there is an "originalQuery", send the user to that page, 
		 * otherwise send them to the home page.
		 */

// 1. if not a protected page, continue
// 2. otherwise, check if session exists and user is logged in
// 		if YES, continue to page
//		if NO, redirect to SIGN-UP page

		$publicPages = $Reg->get('publicPages');
		
		if(SKIP_LOGIN_FOR_DEV){
			// continue to page

		}elseif(in_array($queryString, $publicPages)){
			// continue to page

		}elseif($Session->$Session->getLoggedInStatus() == TRUE){
			// continue to page	

		}else{

			// send to sign-up page
			header("Location: ".ROOT_URL.SIGN_IN_URL);
		}


		/**
		 * Check permissions
		 * Check permissions for logged in user, to see if user can see this page. If user not allowed, send 
		 * to a fail page and stop Front Controller right here.
		 * @todo build, and implement this functionality when needed. This belogns more to the admin panel, where
		 * user rights could vary by page. But the apps may need to have this fine grained granularity, especially
		 * when we have a doctor portal
		 */
		/*
		if(!$Authentication->checkUserCanAccessPage()){
			
		}
		*/
		  
		/**
		 * Ajax calls.
		 * It checks to see whether the $action segment starts with 'ajax_'. If it does, it turns off error
		 * reporting and defines a constant that can be used in the model to determine whether to return a Json
		 * string or not.
		 * @todo Determine whether to further automate Ajax functionality in the Front Controller. For example, we could require that json be returned form the model.
		 */
		if($Request->isAjax())
			define('AJAX_REQUEST',TRUE);
		else
			define('AJAX_REQUEST',FALSE);
		  
		if($Request->isNoTemplate())
			define('DO_NOT_USE_TEMPLATE',TRUE);
		else
			define('DO_NOT_USE_TEMPLATE',FALSE);
		  
		/**
		 * Set up core MVC classes: Controller, Model, View
		 */
		$VIEW = $Loader->loadAppView('appView');

		$Model = $Loader->loadAppModel($page);
		if(!$Model)
			die("Couldn't find that page and action (".$page.",".$action.") in the model directory in Front Controller on line 98.");
		  
		/**
		 * Load the Controller
		 * If there is a specific controller ($path=controller name file & $action = method name, a-la model convention),
		 * then load that, otherwise load the appController. Most pages just need data loaded and sent to the 
		 * view, so no need for a controller. Other pages will need logic that redirects to different pages
		 * based on conditions (Example when a user signs up for first time, they need to be redirected to the app home
		 * page after accepting the form submission).
		 */
  		  if(is_file(MVC_APP_PATH.'app/controllers/'.$page.'.php')){
  		  	// load controller class for the page
  		  	$tempPageController = $Loader->loadController($page);
			// NOTE: the actual function is called by the $CONTROLLER->start function below. This allows for the start
			// to run pre-method and post-method code that is specific to the application.
			if(method_exists($tempClass, $action)){
				$CONTROLLER = $tempPageController;
			}else{
				$CONTROLLER = $Loader->loadAppController('appController');
			}
  		  }else{
  		  	$CONTROLLER = $Loader->loadAppController('appController');
  		  }
		  $CONTROLLER->setView($VIEW);
		  $CONTROLLER->setModel($Model);

		  /**
		   * Error management if the core classes didn't load correctly
		   */
		  if(!$CONTROLLER || !$VIEW){
		  	$Error->setEnv(array('line'=>__LINE__,'file'=>__FILE__,'class'=>__CLASS__,'function'=>__FUNCTION__,'method'=>__METHOD__));
			$Error->appDie('Was unable to setup the core AnthraciteMVC files.');
		  }
//var_dump($CONTROLLER);exit;
//echo $page.'<br/>'.$action;exit;		  
		  /**
		   * Kick off the next phase in the life of a page request coming through the loving hands of the Anthracite framework
		   */
		  $CONTROLLER->start($page,$action,$data);
	 }

}

?>