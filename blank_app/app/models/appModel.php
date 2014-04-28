<?php

class appModel extends coreModel{
	
//	var $skey = "yourSecretKey"; // you can change it
	var $encryptTablesFields = array(
		'people' => array(
			'first_name',
			'last_name',
			'social_security',
			'birth_date'
		)
	);
	
	public function __construct(){
		parent::__construct();
		/*if(!isset($this->firephp) || empty($this->firephp)){
			$reg = registry::singleton();
			$this->firephp = $reg->get('firephp');
		}*/
	}
	
	public function __call($name,$arguments){
		$this->index($arguments);
	}
	
	public function index($args){
		return FALSE;
	}
	
	function getPost(){
		$Registry = Registry::singleton();
		$Request = $Registry->get('requestSingleton');
		$post = $Request->getPost();
		return $post;
	}
	
	public function convertToMysqlDateFormat($date){
		// mysql format is Y-m-d. Regular format is m-d-Y
		$d = explode('/', $date);
		$newDate = $d[2].'-'.$d[0].'-'.$d[1];
		return $newDate;
	}
	
/*	public function encodePPI($sql){
		//$this->encode();
 		// if one of the tables in $encryptTablesFields is in an 'INSERT' SQL statement, then encode that data before 
 		// saving the record(s)
 		// NOTE: 
	}
	
	public function decodePPI($result){
		//$this->decode();
	}
*/
}