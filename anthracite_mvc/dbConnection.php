<?php

class dbConnection {
	private $connection;
	private static $instance;
	
	function __construct(){
		$reg = getRegistryClass();
		$dbConf = $reg->get('db_conf');
		$this->connection = mysqli_connect("$this->dbhost:$this->dbport",$this->dbuser,$this->dbpass);
		if(!$this->connection){
			die('Unable to connect to the database in anthracite_mvc/dbConnection at line : '.__LINE__;
		}
		
		$db_selected = mysqli_select_db($this->dbname,$this->connection);
		if(!$db_selected){
			die('Unable to use the database: '.$this->dbname.' : '.mysqli_error());
		}
	}
	
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