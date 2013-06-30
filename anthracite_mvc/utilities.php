<?php

function __autoload($className){
	$fileName = MVC_CORE_PATH . 'class'.$className.'.php';
	if(file_exists($fileName))
		include_once $fileName;
}

function getRegistryClass(){
	$reg = registry::singleton();
	return $reg;
}

function currentUser(){
	global $firephp;
	if(USE_FIREPHP){$firephp->log('CURR utilities.php, currentUser(), line '.__LINE__);}
	
	$reg = registry::singleton();
	
	if(!isset($_SESSION['userId']))
		return FALSE;
	
	$userId = $_SESSION['userId'];
	if(USE_FIREPHP){$firephp->log($userId,'--$userId at line '.__LINE__);}

	$dbConn = $reg->get('databaseConnectionSingleton');
	$sql = 'SELECT *,people.id AS person_id FROM patients JOIN people ON (patients.person_id=people.id) WHERE patients.id='.$userId;
	if(USE_FIREPHP){$firephp->log(array($dbConn,$sql),'--$dbConn and $sql at line '.__LINE__);}
	$result = mysql_query($sql,$dbConn);
	
	if(is_resource($result)){
		$user = mysql_fetch_assoc($result);
		if(USE_FIREPHP){$firephp->log($user,'--$user at line '.__LINE__);}
	}else{
		if(USE_FIREPHP){$firephp->log('--Mysql call did not work, at line '.__LINE__);}
		return FALSE;
	}
	
	if($user == NULL)
		return FALSE;
	else
		return $user;
}

function redirect($url){
	// erase the output buffer, since the current page is going away, so we don't need any output
	ob_get_clean();
	
	// make sure the cookie made it to the browser, in case this redirect is for the sign-in process
	$params = session_get_cookie_params();
	setcookie(session_name(), session_id(), 0,$params["path"], $params["domain"],$params["secure"]);
	
	// redirect the url
	header('Location: '.$url);
}

function send_email($text,$to=array(),$from=array(),$subject='',$htmlText){
	
	require_once PATH_TO_MVC_LIBRARIES.'/swiftMailer/Swift-4.3.1/lib/swift_required.php';
	
	/**
	 * Create the message
	 * @tutorial http://swiftmailer.org/docs/messages.html
	 */
	 
	if($subject == '')
		$subject = 'A Message From A Member Of Kurbi - MS Comunication Platform';
	$message = Swift_Message::newInstance()
	->setSubject($subject)
	->setFrom($from) // array('john@doe.com' => 'John Doe')
	->setTo($to) // array('receiver@domain.org', 'other@domain.org' => 'A name')
	->setBody($text)
	->addPart($htmlText, 'text/html') // And optionally an alternative body
	// ->attach(Swift_Attachment::fromPath('my-document.pdf')) // Optionally add any attachments
	;
	
	/**
	 * Create the Transport
	 * @tutorial http://swiftmailer.org/docs/sending.html
	 */

	// For when we have SMTP available 
	$transport = Swift_SmtpTransport::newInstance('mail.gokurbi.com', 587) // port:25 or 587
	->setUsername('invitation@gokurbi.com')
	->setPassword('qBpPRxys')
	;
	/* // If SMTP doesn't work
	 $transport = Swift_MailTransport::newInstance(); */
	
	// Create the Mailer using your created Transport
	$mailer = Swift_Mailer::newInstance($transport);
	
	// Send the message
	$result = $mailer->send($message);

	return $result;
}

function cleanMysqlDate($date){
	$tempDate = explode('-',$date);
	if(isset($tempDate[1]) && isset($tempDate[2]) && isset($tempDate[0]))
		$cleanDate = $tempDate[1].'/'.$tempDate[2].'/'.$tempDate[0];
	else
		return FALSE;
	return $cleanDate;
}

/**
 * format should come in as 05/29/1973, and should get converted to 1973-05-29
 */
function convertToMysqlDate($date){
	// check to make sure it's not already in mysql format, i.e. has "-" instead of "/"
	if(substr_count($date,'/') > 0){
		$tempDate = explode('/',$date);
		$cleanDate = $tempDate[2].'-'.$tempDate[0].'-'.$tempDate[1];
		return $cleanDate;
	}elseif(substr_count($date,'-') > 0){
		return $date;
	}else{
		return FALSE;
	}
}

/**
 * resize an image
 */
function resize_and_move_image($filename='',$filepath='',$targetheight=60,$targetwidth=53,$targetPath='',$targetName=''){
	if($targetName == ''){$targetName = $filename;}
	
	/**
	 * Figure out the extension
	 */
	$i = strrpos($filename,".");
    if (!$i) { return ""; } 
    $l = strlen($filename) - $i;
    $extension = substr($filename,$i+1,$l);
	$extension = strtolower($extension);
	
	/**
	 * Create $src from uploaded file (temp folder) dependent on image type (jpg,png,gif)
	 */
	if($extension=="jpg" || $extension=="jpeg" ){
		$uploadedfile = $filepath.$filename;
		$src = imagecreatefromjpeg($uploadedfile);
	}else if($extension=="png"){
		$uploadedfile = $filepath.$filename;
		$src = imagecreatefrompng($uploadedfile);
	}else{
		$src = imagecreatefromgif($uploadedfile);
	}
 
 	/**
	 * Manipulate Width and Height
	 * keep ratio of height to width in resizing
	 */
	// is width wider then height?
	list($width,$height) = getimagesize($uploadedfile);
	$ratio = $height / $width;
	// $newwidth = $width * $ratio;
	// $newheight = $ratio * $targetheight;
	$newwidth = $targetwidth;
	$newheight = $targetheight;
	
	/**
	 * Make temporary version of the new, resized image
	 */
	$tmp = imagecreatetruecolor($newwidth,$newheight);
	imagecopyresampled($tmp,$src,0,0,0,0,$newwidth,$newheight,$width,$height);

	/**
	 * Create the image in final directory
	 */
	$filename = $targetPath.$targetName;	
	imagejpeg($tmp,$filename,100);
	
	/**
	 * 
	 */
	imagedestroy($src);
	imagedestroy($tmp);
}
?>