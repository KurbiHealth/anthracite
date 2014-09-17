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
			$this->dbLink = mysqli_connect("$this->dbhost:$this->dbport",$this->dbuser,$this->dbpass,$this->dbname);
			if(!$this->dbLink){
				die('Unable to connect to the database in CoreModel at line: '.__LINE__);
			}
		}
		
		//$this->firephp = $reg->get('firephp');
		global $firephp;
		$this->firephp = $firephp;

// set hash for url encrypt/decrypt here
$this->urlkey = md5('d,k72@sKp1Q94', true); // For demonstration purpose
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
		$result = mysqli_query($this->dbLink,$sql);

		if(mysqli_errno($this->dbLink) != 0){
			// I'm trying to decouple the parts of this function, and also make the code easier to read
			$this->setError(array(
				'error'=>mysqli_error($this->dbLink),
				'sql'=>$sql
			));
			$this->setErrorFlag(TRUE);
			if(USE_FIREPHP){$firephp->log( array('error'=>mysqli_error($this->dbLink),'sql'=>$sql),'CURR coreModel.php,SQL ERROR');}
			return FALSE;
		}else{
			$this->sqlInsertId = mysqli_insert_id($this->dbLink);
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
		$result = mysqli_query($this->dbLink,$sql);
		
		if(mysqli_errno($this->dbLink) != 0){
			// I'm trying to decouple the parts of this function, and also make the code easier to read
			$this->setError(array('Sql'=>$sql,'Error'=>mysqli_error($this->dbLink)));
			$this->setErrorFlag(TRUE);
			if(USE_FIREPHP){$firephp->log( array('error'=>mysqli_error($this->dbLink),'sql'=>$sql),'CURR coreModel.php,SQL ERROR');}
			return FALSE;
		}else{
			return TRUE;
		}
	}
	
	public function doSelect($table = '',$where = ''){
		$firephp = $this->firephp;
		$count = 0;
		$this->setErrorFlag(FALSE);
		
		if($table == '' || empty($where))
			return FALSE;
		
		$sql = 'SELECT FROM '.$table.' WHERE ';
		
		foreach($arr as $field=>$value){
			$value = $this->sanitize($value);
			if($count == 0)
				$comma = '';
			else
				$comma = ',';
			$sql .= $comma.$field.'="'.$value.'"';
			$count++;
		}

		$this->sql = $sql;
		
		// DO QUERY
		$result = mysqli_query($this->dbLink,$sql);
		
		// GET ARRAY FROM $result
		
		
		// DECRYPT FIELDS IF NEEDED
		
		
		// ERROR CHECKING
		if(mysqli_errno($this->dbLink) != 0){
			// I'm trying to decouple the parts of this function, and also make the code easier to read
			$this->setError(array('Sql'=>$sql,'Error'=>mysqli_error($this->dbLink)));
			$this->setErrorFlag(TRUE);
			if(USE_FIREPHP){$firephp->log( array('error'=>mysqli_error($this->dbLink),'sql'=>$sql),'CURR coreModel.php,SQL ERROR');}
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
		$result = mysqli_query($this->dbLink,$sql);
		
		if(mysqli_errno($this->dbLink) != 0){
			// I'm trying to decouple the parts of this function, and also make the code easier to read
			$this->setError(array('Sql'=>$sql,'Error'=>mysqli_error($this->dbLink)));
			$this->setErrorFlag(TRUE);
			if(USE_FIREPHP){$firephp->log( array('error'=>mysqli_error($this->dbLink),'sql'=>$sql),'CURR coreModel.php,SQL ERROR');}
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
		 * @todo Include sanitization steps into the PRE-PROCESS snd the PROCESS steps of this function. In pre-process 
		 * focus on sql injections, and in both pre and post process focus on cross-site injections.
		 */
		
		if(preg_match('`select`',$sql,$matches) || preg_match('`SELECT`',$sql,$matches)){
			
		}
		if(preg_match('`update`',$sql,$matches) || preg_match('`UPDATE`',$sql,$matches)){
			// decode PPI if needed
			if(function_exists(array($this,'decodePPI'))){
				$result = $this->decodePPI($result);
			}
		}
		if(preg_match('`insert\sinto`',$sql,$matches) || preg_match('`INSERT\sINTO`',$sql,$matches)){
// decode PPI if needed
/*if(function_exists(array($this,'encodePPI'))){
	$result = $this->encodePPI($result);
}*/
		}
		if(preg_match('`delete`',$sql,$matches) || preg_match('`DELETE`',$sql,$matches)){
			
		}
		$this->sql = $sql;
		
		// DO QUERY
		$result = mysqli_query($this->dbLink,$sql);

		// PROCESS THE RESULT OF THE QUERY
		if(mysqli_errno( $this->dbLink ) != 0 || !is_object($result)){
			// I'm trying to decouple the parts of this function, and also make the code easier to read
			$err = TRUE;
		}
		
		if(preg_match('`select`',$sql,$matches) || preg_match('`SELECT`',$sql,$matches)){
			if(!$err){
				$num_rows = mysqli_num_rows($result);
				
			}
		}
		if(preg_match('`update`',$sql,$matches) || preg_match('`UPDATE`',$sql,$matches)){
			
		}
		if(preg_match('`insert\sinto`',$sql,$matches) || preg_match('`INSERT\sINTO`',$sql,$matches)){
			if(!$err)
				$insertId = mysqli_insert_id($result);

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
				'error'=>mysqli_error($this->dbLink),
				'sql'=>$sql
			));
			$this->setErrorFlag(TRUE);
			if(USE_FIREPHP){$firephp->log( array('error'=>mysqli_error($this->dbLink),'sql'=>$sql),'CURR coreModel.php,SQL ERROR');}
		}
		
		// insert ID
		if(isset($insertId))
			$this->sqlInsertId = $insertId;
		
// decode PPI if needed
/*if(function_exists(array($this,'decodePPI'))){
	$result = $this->decodePPI($result);
}*/
		
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
						else $clean[$index][$key] = mysqli_real_escape_string($this->dbLink,trim($data));
					}
				}elseif(is_numeric($value)) {$clean[$index] = trim($value);}
				elseif($index != ('extraHTML' || 'theLimiter')){ $clean[$index][$key] = mysqli_real_escape_string($this->dbLink,trim($value));}
				else{$clean[$index] = trim($value); }
			}
		}else{
			$clean = mysqli_real_escape_string($this->dbLink,trim($theValue));
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
		$this->sqlInsertId = mysqli_insert_id($this->dbLink);
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

/**
 * ENCRYPTION
 */

	var $skey = "k20#di4kewx*02PD233!@F^"; // you can change it
	
	public function encrypt($value){ 
        if(!$value){
        	return false;
		}
        $text = $value;
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->skey, $text, MCRYPT_MODE_ECB, $iv);
        return trim($crypttext); 
    }

    public function decrypt($value){
        if(!$value){return false;}
        $crypttext = $value; 
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->skey, $crypttext, MCRYPT_MODE_ECB, $iv);
        return trim($decrypttext);
    }

	var $urlkey = ''; // the hash value of this variable is set in __construct()

	function encryptUrlKey($id){
	    $id = base_convert($id, 10, 36); // Save some space
	    $data = mcrypt_encrypt(MCRYPT_BLOWFISH, $this->urlkey, $id, 'ecb');
	    $data = bin2hex($data);

	    return $data;
	}

	function decryptUrlKey($encrypted_id){
	    $data = pack('H*', $encrypted_id); // Translate back to binary
	    $data = mcrypt_decrypt(MCRYPT_BLOWFISH, $this->urlkey, $data, 'ecb');
	    $data = base_convert($data, 36, 10);

	    return $data;
	}
	
}