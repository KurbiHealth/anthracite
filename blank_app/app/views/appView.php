<?php

class appView extends coreView{
	function __construct(){ }
	
	function renderPage($page,$action,$pageData){
		// any ajax request should send data straight to browser, it assumes that the data coming from Model is in json form
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
		
		if(is_file($template)){
			$body = $template;
		}elseif($pageData != ''){
			$body = $pageData;
		}else{
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
}

?>