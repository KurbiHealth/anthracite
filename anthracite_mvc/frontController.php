<?php

class frontController extends coreController{
	/**
	 * __construct
	 */
	 function __construct(){
	 	
	 }
	 
	 /**
	  * @param  
	  */
	 function start(){
	 	if(!defined('USE_FIREPHP'))
			define('USE_FIREPHP',FALSE);
		
		if(USE_FIREPHP){
			global $firephp;
			$firephp->log('-----------------------STARTING FRONTCONTROLLER');
			$firephp->log($_SESSION,'$_SESSION, line '.__LINE__);
		}
		
	 	/**
		 * Get needed classes from the Registry. 
		 */
	 	$Reg = registry::singleton();
		
		$Request = $Reg->get('requestSingleton');
		$Error = $Reg->get('errorSingleton');
		$Loader = $Reg->get('loaderSingleton');
		$Session = $Reg->get('sessionSingleton');
		$Authentication = $Reg->get('authenticationSingleton');

		if(USE_FIREPHP){$firephp->log(  
		array(
			'$Request'=>$Request,
			'$Error'=>$Error,
			'$Session'=>$Session,
			'$Authentication'=>$Authentication
		),
		'$Request, $Error, $Session, $Authentication at line '.__LINE__);}

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
			if(USE_FIREPHP){$firephp->log(  array('page'=>$page,'action'=>$action),'$page and $action');}
		}
		
		$data = $Request->getParams();

		$Session->set('currPageRequested',$page);
		
		/**
		 * Ajax calls.
		 * It checks to see whether the $action segment starts with 'ajax_'. If it does, it turns off error
		 * reporting and defines a constant that can be used in the model to determine whether to return a Json
		 * string or not.
		 * @todo Determine whether to further automate Ajax functionality in the Front Controller. For example, we could require that json be returned form the model.
		 */
		if(USE_FIREPHP){$firephp->log('checking if request has AJAX tag, line '.__LINE__);}
		if($Request->isAjax())
			define('AJAX_REQUEST',TRUE);
		else
			define('AJAX_REQUEST',FALSE);
		
		/**
		 * No Template calls
		 */
		if(USE_FIREPHP){$firephp->log('checking if page requested without master template, line '.__LINE__);}
		if($Request->isNoTemplate())
			define('DO_NOT_USE_TEMPLATE',TRUE);
		else
			define('DO_NOT_USE_TEMPLATE',FALSE);
		
		/**
		 * Clean $queryString if it has Ajax string or NoTemplate string
		 */
		if(preg_match('`ajax_`',$queryString,$match) > 0){
			str_replace('ajax_', '', $queryString);
		}
		if(preg_match('`notemplate_`',$queryString,$match) > 0){
			$queryString = str_replace('notemplate_', '', $queryString);
		}
		if(USE_FIREPHP){$firephp->log($queryString, '$queryString after cleanup, line '.__LINE__);}

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

		if(USE_FIREPHP){
			if($Authentication->pageIsProtected())
				$firephp->log('Page shows as protected (i.e. user must log in), line '.__LINE__); 
			else
				$firephp->log('Page does NOT show as protected (i.e. user must log in), line '.__LINE__);
			
			if($Session->getLoggedInStatus())
				$firephp->log('Session shows user is logged in');
			else
				$firephp->log('Session shows user is NOT logged in');
		}

		if($queryString == SIGN_IN_URL || $queryString == SIGN_UP_URL){
			
// PAGE IS: sign_up or sign_in, continue
			
			if(USE_FIREPHP){$firephp->log( $queryString,'page requested is sign_in or sign_up, CONTINUING THROUGH, line '.__LINE__ );}

		}else{
		
// IF NOT THE SIGN UP OR SIGN IN PAGE

// USER IS: signing IN, /site/sign_in_result
			if(USE_FIREPHP){$firephp->log(array('ReferringQueryURL'=>$Request->getReferringQuery(), 'SIGN_IN_URL'=>SIGN_IN_URL, 'SIGN_UP_URL'=>SIGN_UP_URL, '$queryString'=>$queryString),'--Variables at line '.__LINE__);}

			if($Request->getReferringQuery() == SIGN_IN_URL && $queryString == SIGN_IN_URL.'_result'){
				if(USE_FIREPHP){$firephp->log('--At SIGN_IN_RESULT, line '.__LINE__);}	
						
				// if current request is coming from the sign-in url, then use the ->checkIsUser function to check
				// whether the user has valid credentials
				if($user = $Authentication->checkIsUser()){
					if(USE_FIREPHP){$firephp->log('checked that user credentials are in database, signing in');}

	 				// last authenticated page saved in Session, so that ajax calls from that page can come through without needing authentication
					$Session->set('currAuthenticatedPage',$page);
					$Session->initializeSession($user);
					
					if(USE_FIREPHP){$firephp->log($_SESSION,'$_SESSION, line '.__LINE__);}
					
					$homeUrl = ROOT_URL.LOGGED_IN_HOME_URL;
					if(USE_FIREPHP){$firephp->log('redirecting to HOME_URL');}
					ob_end_flush();
					redirect($homeUrl);
				}else{
					$Session->set('queryString',$queryString);
					if(USE_FIREPHP){$firephp->log('--User credentials not found in db, redirecting to sign_in page');}
					ob_end_flush();
					redirect(ROOT_URL.SIGN_IN_URL);
				}
				
				if(USE_FIREPHP){$firephp->log( 'got to line '.__LINE__);}

// USER IS: signing UP, /site/sign_up_result, i.e. user is in process of creating an account

			}elseif($Request->getReferringQuery() == SIGN_UP_URL && $queryString == SIGN_UP_URL.'_result'){
				
				if(USE_FIREPHP){$firephp->log('--At SIGN_UP_RESULT, line '.__LINE__);}

				// if current request is coming from the sign-in url, then use the ->checkIsUser function to check
				// whether the user has valid credentials
				if($user = $Authentication->checkSignUpIsValid()){
					$Session->initializeSession($user);
					// don't redirect the sign-up page to the user's home page, the sign_up_result page has links for a first time user
					
				}else{
					$signUpUrl = ROOT_URL.SIGN_UP_URL;
					ob_end_flush();
					redirect($signUpUrl);
				}
			}elseif(SKIP_LOGIN_FOR_DEV){
// USER IS: allowed through, skipping login requirement for dev purposes

				if(USE_FIREPHP){$firephp->log('--Skipping login for dev purposes, line '.__LINE__);}

// PAGE IS: protected. NO session.

			}elseif(($Authentication->pageIsProtected() == TRUE) && ($Session->getLoggedInStatus() == FALSE)){
				if(USE_FIREPHP){$firephp->log('--Page is protected, and not logged in, line '.__LINE__);}
				// SKIP_LOGIN_FOR_DEV, if set to TRUE, will cause the app to skip this step
				$Session->setFlashMessage('You must sign in to see that page');
				$Session->set('queryString',$queryString);
				if(USE_FIREPHP){$firephp->log('redirecting user to sign_in page, line '.__LINE__);}
				ob_end_flush();
				redirect(ROOT_URL.SIGN_IN_URL);

// PAGE IS: protected. Session EXISTS, continue
	
			}elseif($Authentication->pageIsProtected() && $Session->getLoggedInStatus() == TRUE){
				if(USE_FIREPHP){$firephp->log('--Page is protected, session exists, continue on, line '.__LINE__);}

// PAGE IS NOT protected, continue

			}elseif(!$Authentication->pageIsProtected()){
				if(USE_FIREPHP){$firephp->log('--Page is NOT protected, continue on, line '.__LINE__);}

// SOME WEIRD CONDITION I COULDN'T COME UP WITH
				
			}else{
				// save current query in session flash variable
				$Session->set('queryString',$queryString);
				$Session->setFlashMessage('You must log in to see that page.');
				// send to default sign-in page
				$signinUrl = ROOT_URL.SIGN_UP_URL;
				ob_end_flush();
				redirect($signinUrl);
			}
			
			if(USE_FIREPHP){$firephp->log('Finished deciding what path to take, line '.__LINE__);}

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
		 * Set up core MVC classes: Controller, Model, View
		 */
		if(USE_FIREPHP){$firephp->log('loading mvc VIEW class, line '.__LINE__);}
		$VIEW = $Loader->loadAppView('appView');

		if(USE_FIREPHP){$firephp->log('loading mvc MODEL class, line '.__LINE__);}
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
		  
		  $firephp->log('-----------------------end FRONTCONTROLLER');
		  
		  /**
		   * Kick off the next phase in the life of a page request coming through the loving hands of the Anthracite framework
		   */
		  $CONTROLLER->start($page,$action,$data);
	 }

}

?>