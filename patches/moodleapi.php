<?php 

//
// Main
//

session_start();

$output = null;
$success = false;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';

// validate action so as to default to the login screen
if ( !in_array($action, array('login'), true) )
	$action = 'login';

$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
switch ($action) {
	case 'login' :
	default:
		$response = '';
		// If the user wants ssl but the session is not ssl, force a secure cookie.
		if ( !empty($_POST['log']) && !empty($_POST['referer'])) {
			//Sanitize these fields to avoid DB hijacking
			$user_name = trim(escapeshellcmd($_POST['log']));
			$unparsed_url = trim(escapeshellcmd($_POST['referer']));
			$user_domain = get_host($unparsed_url);
			$success = getAPIKeys($user_name, $user_domain, $unparsed_url);
		}
		break;
}

//
// Functions
//

/**
 * Generates a set of API keys for a user that's already registered in Babelium Project.
 * Keys are only generated and sent once.
 * @param String $user
 * 	Username or email of a registered Babelium Project user 
 * @param String $domain
 *	The domain the API requests will come from. Requests made from domains other than this will be rejected
 */
function getAPIKeys($user, $domain, $raw_domain){

	global $output;

	$output = "There was an error while processing your request. Please try again later.";

	//Ensure all the classes exist and are readable
	$c_path = dirname(__FILE__) . "/services/utils/Config.php";
	$d_path = dirname(__FILE__) . "/services/utils/Datasource.php";
	$m_path = dirname(__FILE__) . "/services/utils/Mailer.php";

	if( !is_readable($c_path) || !is_readable($d_path) || !is_readable($m_path) ){
		return;
	}

	//Require some classes that we use for the RPC services
	require_once $c_path; 
	require_once $d_path; 
	require_once $m_path;

	try{
		//Configuration instance
		$conf = new Config();
		//Database handling instance
		$db = new Datasource($conf->host, $conf->db_name, $conf->db_username, $conf->db_password);
	} catch (Exception $e){
		return;
	}

	//Check whether the user provided a username or an email
	$field = Mailer::checkEmail($user) ? "email" : "name";
	$sql = "SELECT ma.allowed_referer, u.ID, u.name FROM moodle_api ma RIGHT OUTER JOIN users u ON ma.fk_user_id=u.ID WHERE u.%s='%s' AND u.active=1";
	if(!$query_result = $db->_singleSelect($sql, $field, $user)){
		return;
	}

	if($query_result->allowed_referer){
		$output = "API Keys are only sent once. Check in your email account.";
		return;
	}
	
	$sql = "INSERT INTO moodle_api (`access_key`, `secret_access_key`, `allowed_referer`, `raw_referer`, `fk_user_id`, `date_created`)
		VALUES ('%s', '%s', '%s', '%s', '%s', DEFAULT)"; 
	$userId = $query_result->ID;
	$accesskey = str_makerand(20,true,true,false);
	$secretaccesskey = str_makerand(40,true,true,true);

	$db->_startTransaction();

	$insert_result = $db->_insert($sql,$accesskey,$secretaccesskey,$domain,$raw_domain,$userId);
	if(!$insert_result){
		$db->_failedTransaction();
		return;
	}

	//Mail handling instance
	$mail = new Mailer($user);

	$subject = 'Babelium Project: API Access Keys';

	$args = array(
			'PROJECT_NAME' => 'Babelium Project',
			'USERNAME' => $query_result->name,
			'DOMAIN' => $domain,
			'ACCESS_KEY' => $accesskey,
			'SECRET_ACCESS_KEY' => $secretaccesskey,
			'SIGNATURE' => 'The Babelium Project Team');

	if( !$mail->makeTemplate("api_keys", $args, 'en_US') ){
		$db->_failedTransaction();
		return;
	}


	if (!$mail->send($mail->txtContent, $subject, $mail->htmlContent)){
		$db->_failedTransaction();
		return;
	} else {
		$db->_endTransaction();
		$output = "API keys have been successfully generated. Check your email account.";
		return true;
	}
}

/**
 * Parses the given url address and returns its domain name
 * 
 * @author nirazuelos@gmail.com
 * 		http://www.php.net/manual/en/function.parse-url.php#93983
 * @param String $address
 * 		The url address to be parsed
 * @return string
 * 		The domain name of the provided address
 */
function get_host($address) {
	$parseUrl = parse_url(trim($address));
	
	return trim(isset($parseUrl['host']) && $parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
}

/**
 * Makes random strings of the requested length and character set
 * @param int $length
 * 	The length of the random string
 * @param boolean $useupper
 *	Determines if upper case characters are allowed in the random string
 * @param boolean $usenumbers
 *	Determines if numbers are allowed in the random string
 * @param boolean $usesymbols
 *	Determines if special symbol characters are allowed in the random string
 * @return String $key
 *	The random key of the specified length 
 */
function str_makerand ($length, $useupper, $usenumbers, $usesymbols)
{
	$key= '';
	$charset = "abcdefghijklmnopqrstuvwxyz";
	if ($useupper)
		$charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	if ($usenumbers)
		$charset .= "0123456789";
	if($usesymbols)
		//This are all the printable symbols of the ASCII table except ", ' and space left out for sequence escaping reasons
		$charset .= "!#$%&()*+,-.:;=?@[]^_`{|}~";

	for ($i=0; $i<$length; $i++)
		$key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
	return $key;
}

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
<head>
<title>Babelium Project: Get Babelium API Keys</title>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel='stylesheet' id='login-css'  href='css/login.css' type='text/css' media='all' />
<meta name='robots' content='noindex,nofollow' />

</head>
<body class="login">

<div id="login">

<h1><a href="http://www.babeliumproject.com/" title="Babelium Project">Babelium Project</a></h1>
<h1>Babelium API Key Signup</h1>

<?php if(!$success) { ?> 
	<form name="loginform" id="loginform" action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
	<p>
	<label>Username or email<br />
	<input type="text" name="log" id="user_login" class="input" value="<?php echo isset($user_name) ? $user_name : ''; ?>" size="20" tabindex="10" /></label>
	</p>
	<p>
	<label>Domain (ex. :  www.mymooodle.com ) <br />
	<input type="text" name="referer" id="user_referer" class="input" value="<?php echo isset($user_referer) ? $user_referer : ''; ?>" size="20" tabindex="10" /></label>
	</p>

	<br />
	<br />
	<p class="submit">
	<input type="submit" name="bp-submit" id="bp-submit" class="button-primary" value="Get Babelium API Keys" tabindex="100" />
	</p>
	</form>
</div>
<?php if($output) { ?>
<div id="login_error">
<p><?php echo $output; ?></p>
</div>
<?php } ?>
<script type="text/javascript">
	function wp_attempt_focus(){ setTimeout( function(){ try{ d = document.getElementById('user_login'); d.focus(); d.select(); } catch(e){} }, 200); }
	wp_attempt_focus();
</script>
<?php } else { ?>
<div id="login_success">
	<p><?php echo $output; ?></p>
</div>
<?php } ?>
</body>
</html>
