<?php

class appController extends coreController {
	protected $post;
	protected $action;
	
	function __construct(){}

	function start($page,$action,$data=NULL){
		$this->_startInit($page, $action, $data);
		
		$pageData = $this->MODEL->$action($data);

		if(get_class($this) == $page.'Controller')
			$this->$action($pageData);

		$this->VIEW->renderPage($page,$action,$pageData);
	}
	
	function _startInit($page,$action,$data=NULL){
		$Reg = registry::singleton();
		$Request = $Reg->get('requestSingleton');
		$Loader = $Reg->get('loaderSingleton');
		if(!empty($Request->values['POST']))
			$this->post = $Request->values['POST'];
		else
			$this->post = NULL;
		
		// there's always an action, or collection of functionality, and the name of the action must correspond to a function 
		// name in the model. 
		if(empty($action))
			$this->action == 'index'; // so there is a default action
		
		if($action == 'list')
			$this->action = 'all'; // list is a protected keyword in PHP, can't be a function name
	}

}

?>