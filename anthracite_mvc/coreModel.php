<?php

/**
 * NOTE:
 * The bootstrap file loads core Model first so it can be used to load anything needed from the database into the Registry.
 * The app will have an _app_model file, which extends this class. This way we can have core Model functionality available to all
 * apps, and each app can also have Model functions specific to that app.
 */
 
 class coreModel{
 	/**
	 * VARS
	 */
	private $dbLink;
	 
	private $dbhost;
	private $dbport;
	private $dbuser;
	private $dbpass;
	private $dbname;
	
	private $sql;
	 
	public $errors = FALSE;
	public $sqlError = FALSE;
	public $sqlInsertId;
	public $sqlCount;
	
	public $firephp;
	 
	public function __construct(){
		$this->setHost();
		$this->setPort();
		$this->setUser();
		$this->setPass();
		$this->setDatabase();
		
		$reg = registry::singleton();
		$conn = $reg->get('databaseConnectionSingleton');
		if($conn != NULL){
			$this->dbLink = $conn;
		}else{
			$this->dbLink = mysql_connect("$this->dbhost:$this->dbport",$this->dbuser,$this->dbpass);
			if(!$this->dbLink){
				die('Unable to connect to the database: '.mysql_error());
			}
		}
		
		$db_selected = mysql_select_db($this->dbname,$this->dbLink);
		if(!$db_selected){
			die('Unable to use the database: '.$this->dbname.' : '.mysql_error());
		}
		
		//$this->firephp = $reg->get('firephp');
		global $firephp;
		$this->firephp = $firephp;
	}
	
	public function __destruct(){
		
	}
	
	public function doInsert($table = '',$arr = array()){
		global $firephp;
		$this->setErrorFlag(FALSE);
		$this->setError(array());
		$this->sqlInsertId == '';
		
		$valueList = '';
		$fieldList = '';
		
		if($table == '' || empty($arr))
			return FALSE;
		$count = 0;

		foreach($arr as $field=>$value){
			$value = $this->sanitize($value);
			if($count == 0)
				$comma = '';
			else
				$comma = ',';
			$fieldList .= $comma.$field;
			if(is_string($value))
				$valueList .= $comma.'"'.$value.'"';
			else
				$valueList .= $comma.$value;
			$count++;
		}
		
		$sql = 'INSERT INTO '.$table.' ('.$fieldList.') VALUES ('.$valueList.')';
		$this->sql = $sql;
		
		// DO QUERY
		$result = mysql_query($sql);

		if(mysql_errno() != 0){
			// I'm trying to decouple the parts of this function, and also make the code easier to read
			$this->setError(array(
				'error'=>mysql_error(),
				'sql'=>$sql
			));
			$this->setErrorFlag(TRUE);
			if(USE_FIREPHP){$firephp->log( array('error'=>mysql_error(),'sql'=>$sql),'CURR coreModel.php,SQL ERROR');}
			return FALSE;
		}else{
			$this->sqlInsertId = mysql_insert_id();
			if($this->sqlInsertId == '' || $this->sqlInsertId == NULL){
				if(USE_FIREPHP){$firephp->log('did not insert the new id into $this->sqlInsertId.');}
				return FALSE;
			}
			return TRUE;
		}	
	}
	
	public function doUpdate($table = '',$arr = array(),$id=''){
		$firephp = $this->firephp;
		$count = 0;
		$this->setErrorFlag(FALSE);
		
		if($table == '' || empty($arr) || $id == '')
			return FALSE;
		
		$sql = 'UPDATE '.$table.' SET ';
		
		foreach($arr as $field=>$value){
			$value = $this->sanitize($value);
			if($count == 0)
				$comma = '';
			else
				$comma = ',';
			$sql .= $comma.$field.'="'.$value.'"';
			$count++;
		}
		
		$sql .= " WHERE id=".$id;
		// $firephp->log($sql,'COREMODEL -- $sql in doUpdate()');
		$this->sql = $sql;
		
		// DO QUERY
		$result = mysql_query($sql);
		
		if(mysql_errno() != 0){
			// I'm trying to decouple the parts of this function, and also make the code easier to read
			$this->setError(array('Sql'=>$sql,'Error'=>mysql_error()));
			$this->setErrorFlag(TRUE);
			if(USE_FIREPHP){$firephp->log( array('error'=>mysql_error(),'sql'=>$sql),'CURR coreModel.php,SQL ERROR');}
			return FALSE;
		}else{
			return TRUE;
		}
	}
	
	public function doDelete($table='',$id=''){
		if($table == '' || $id == '')
			return FALSE;
		global $firephp;
		$sql = 'DELETE FROM '.$table.' WHERE id='.$id;
		$this->sql = $sql;
		
		// DO QUERY
		$result = mysql_query($sql);
		
		if(mysql_errno() != 0){
			// I'm trying to decouple the parts of this function, and also make the code easier to read
			$this->setError(array('Sql'=>$sql,'Error'=>mysql_error()));
			$this->setErrorFlag(TRUE);
			if(USE_FIREPHP){$firephp->log( array('error'=>mysql_error(),'sql'=>$sql),'CURR coreModel.php,SQL ERROR');}
			return FALSE;
		}else{
			return TRUE;
		}
	}
	
	public function doQuery($sql){
		global $firephp;
		
		// INITIALIZE VARIABLES
		$err = FALSE;
		$this->setError(array());
		$this->setErrorFlag(FALSE);
		
		// PRE-PROCESS THE QUERY
		
		/**
		 * @todo Include sanitization steps into the PRE-PROCESS snd the PROCESS steps of this function. In pre-process focus on sql injections, and in both pre and post process focus on cross-site injections.
		 */
		
		if(preg_match('`select`',$sql,$matches) || preg_match('`SELECT`',$sql,$matches)){
			
		}
		if(preg_match('`update`',$sql,$matches) || preg_match('`UPDATE`',$sql,$matches)){
			
		}
		if(preg_match('`insert\sinto`',$sql,$matches) || preg_match('`INSERT\sINTO`',$sql,$matches)){
			
		}
		if(preg_match('`delete`',$sql,$matches) || preg_match('`DELETE`',$sql,$matches)){
			
		}
		$this->sql = $sql;
		
		// DO QUERY
		$result = mysql_query($sql);

		// PROCESS THE RESULT OF THE QUERY
		if(mysql_errno() != 0 || !is_resource($result)){
			// I'm trying to decouple the parts of this function, and also make the code easier to read
			$err = TRUE;
		}
		
		if(preg_match('`select`',$sql,$matches) || preg_match('`SELECT`',$sql,$matches)){
			if(!$err){
				$num_rows = mysql_num_rows($result);
				
			}
		}
		if(preg_match('`update`',$sql,$matches) || preg_match('`UPDATE`',$sql,$matches)){
			
		}
		if(preg_match('`insert\sinto`',$sql,$matches) || preg_match('`INSERT\sINTO`',$sql,$matches)){
			if(!$err)
				$insertId = mysql_insert_id($result);

		}
		if(preg_match('`delete`',$sql,$matches) || preg_match('`DELETE`',$sql,$matches)){
			
		}

		// SET RESPONSE DATA INTO CLASS VARIABLES
		
		// number of rows
		if(!isset($num_rows)) 
			$this->setCount(0);
		else 
			$this->setCount($num_rows);
		
		// error codes
		if($err){
			$this->setError(array(
				'error'=>mysql_error(),
				'sql'=>$sql
			));
			$this->setErrorFlag(TRUE);
			if(USE_FIREPHP){$firephp->log( array('error'=>mysql_error(),'sql'=>$sql),'CURR coreModel.php,SQL ERROR');}
		}
		
		// insert ID
		if(isset($insertId))
			$this->sqlInsertId = $insertId;
		
		if(!$err)
			return $result;
		else
			return FALSE;
	}
	
	private function setError($error){
		$this->errors[] = $error;
	}
	
	private function setErrorFlag($flag){
		$this->sqlError = $flag;
	}
	
	public function sanitize($theValue){
		if(is_array($theValue)){
			foreach($theValue as $index => $value){
				if(is_array($value)){
					foreach($value as $key=>$data){
						if(is_numeric($data)) $clean[$index][$key] = trim($data);
						else $clean[$index][$key] = mysql_real_escape_string(trim($data));
					}
				}elseif(is_numeric($value)) {$clean[$index] = trim($value);}
				elseif($index != ('extraHTML' || 'theLimiter')){ $clean[$index][$key] = mysql_real_escape_string(trim($value));}
				else{$clean[$index] = trim($value); }
			}
		}else{
			$clean = mysql_real_escape_string(trim($theValue));
		}
		return $clean;
	}
	
	public function stripSpecialChars($theValue){
		$result = preg_replace('/[^a-z0-9 ]/i','',$theValue);
		$result = trim($result);
		return $result;
	}
	 	
	private function setHost(){
		$reg = registry::singleton();
		$db = $reg->get('db_conf');
		if(ENVIRONMENT == 'prod'){
			$this->dbhost = $db['prod']['db_host'];
		}
		if(ENVIRONMENT == 'dev'){
			$this->dbhost = $db['dev']['db_host'];
		}
	}
 	
	private function setPort(){
		$reg = registry::singleton();
		$db = $reg->get('db_conf');
		if(ENVIRONMENT == 'prod'){
			$this->dbport = $db['prod']['db_port'];
		}
		if(ENVIRONMENT == 'dev'){
			$this->dbport = $db['dev']['db_port'];
		}
	}
 	
	private function setUser(){
		$reg = registry::singleton();
		$db = $reg->get('db_conf');
		if(ENVIRONMENT == 'prod'){
			$this->dbuser = $db['prod']['db_user'];
		}
		if(ENVIRONMENT == 'dev'){
			$this->dbuser = $db['dev']['db_user'];
		}
	}
 	
	private function setPass(){
		$reg = registry::singleton();
		$db = $reg->get('db_conf');
		if(ENVIRONMENT == 'prod'){
			$this->dbpass = $db['prod']['db_pass'];
		}
		if(ENVIRONMENT == 'dev'){
			$this->dbpass = $db['dev']['db_pass'];
		}
	}
 	
	private function setDatabase(){
		$reg = registry::singleton();
		$db = $reg->get('db_conf');
		if(ENVIRONMENT == 'prod'){
			$this->dbname = $db['prod']['db_name'];
		}
		if(ENVIRONMENT == 'dev'){
			$this->dbname = $db['dev']['db_name'];
		}
	}
	
	private function setSQL($sql){
		$this->sql = $sql;
	}
	
	private function setInsertId(){
		$this->sqlInsertId = mysql_insert_id();
	}
	
	private function setCount($num){
		$this->sqlCount = $num;
	}
	
	public function getTimestamp(){
		$now = time(); // current timestamp
		
		$day = date('d',$now);
		$month = date('m',$now);
		$year = date('Y',$now);
		
		$hour = date('G',$now);
		$minute = date('i',$now);
		$second = date('s',$now);
		
		$return = $year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second;
		return $return;
	}
 }

?>
