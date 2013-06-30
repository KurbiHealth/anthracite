<?php

class coreController {
	public $session;
	public $VIEW;
	public $MODEL;
	
	function __construct(){
		// not used for now
	}
	
	function loadModel($model,$function,$params){
		
	}
	
	function loadPage($page,$pageData){
		
	}
	
	function setView($view){
		$this->VIEW = $view;
	}
	
	function setModel($model){
		$this->MODEL = $model;
	}
	
	
}
