<?php

class registry{
	private static $instance;
	private $storageArray = array();
	
	private function __construct(){
		
	}
	
	public function isValid($key){
		return array_key_exists($key,$this->storageArray);
	}
	
	public static function singleton(){
		if(!isset(self::$instance)){
			$className = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
	}
	
	public function set($key,$value,$override=TRUE){
		if(empty($key)) return FALSE;
		if(empty($value)) return FALSE;
		if(array_key_exists($key, $this->storageArray) && $override == FALSE) return FALSE;
		
		$this->storageArray[$key] = $value;
		return TRUE;
	}
	
	public function get($key){
		if(array_key_exists($key, $this->storageArray))
			return $this->storageArray[$key];
		else
			return FALSE;
	}
	
	function getAll(){
		return $this->storageArray;
	}
	
	function initialize($model){
		//$configs = $this->loadConfigurationsFromDb($model);
		//$this->storageArray = $configs;
	}
	
	private function loadConfigurationsFromDb($model){
		// if there are configurations in the db, load them here
		// @todo Determine if this function is a sound architectural decision
	}
}
