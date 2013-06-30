<?php

class coreView{
	function renderPage($page,$action,$pageData){
		
	}
	
	function getFlashMessage(){
		$reg = Registry::singleton;
		$session = $reg->get('sessionSingleton');
		// the function "getFlashMessage" will either return FALSE or a string
		$msg = $session->getFlashMessage();
		return $msg;
	}
}

?>