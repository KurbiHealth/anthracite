<?php

class coreView{
	
	function renderPage($page,$action,$pageData){
		// any ajax request should send data straight to browser, it assumes that the data 
		// coming from Model is in json form
		if(AJAX_REQUEST == TRUE){
			header('Content-Type: application/json;charset=utf8');
			if(is_array($pageData)){
				echo json_encode($pageData);
			}else{
				echo $pageData;
			}
			die();
		}

		$template = MVC_APP_PATH.'app/views/'.$page.'/'.$action.'.php';
		
		// get the flashmessage array and include in $pageData
		// NOTE: flash messages don't apply when request is ajax
		$flashMsg = $this->getFlashMessage();

		// create the $body variable by putting the template into it, or showing 
		// $pageData if there is no template to be found
		if(is_file($template)){
			$body = $template;
		}elseif($pageData != ''){
			// if there is a $pageData variable, it was put there by the model, 
			// so pass it along. This allows for a page to work even without a view.
			$body = $pageData;
		}else{
			// This step should be showing a 404 page, something prettier then this
			$body = 'There is no page to be found at that url.';
		}
	
		if(defined('DO_NOT_USE_TEMPLATE') && DO_NOT_USE_TEMPLATE === TRUE)
			require_once MVC_APP_PATH.'/app/views/'.$page.'/'.$action.'.php';
		else
			require_once MVC_APP_PATH.'/app/views/master_template.php';
		
		$cache = ob_get_contents();
		if($cache != '')
			ob_end_flush();
	}
	
	function getFlashMessage(){
		$reg = registry::singleton();
		$session = $reg->get('sessionSingleton');

		// the function "getFlashMessage" will either return FALSE or a string
		// the value of $msg will be an array, with the following keys:
		// 	[type] = 'url' or 'text'
		//	[title]
		//	[url] or [text], goes along with [type]
		$msg = $session->getFlashMessage();
		return $msg;
	}
}

?>