<?php

/**
 * Babelium Project open source collaborative second language oral practice - http://www.babeliumproject.com
 *
 * Copyright (c) 2011 GHyM and by respective authors (see below).
 *
 * This file is part of Babelium Project.
 *
 * Babelium Project is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Babelium Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Zend/Rest/Server.php';
require_once 'Zend/Json.php';
/**
 * A customized version of Zend's Rest Server class that allows to receive base64 encoded json parameters
 * and return json and xml responses
 *
 * @author Babelium Team
 *
 */
class ZendRestJson extends Zend_Rest_Server
{

	protected $_faultResult = false;
	
	private $allowed_time_skew = 900; //allowed time skew in seconds
	
	private function setHeaders(){
		$this->_headers = array('Content-Type: application/json');
	}
	
	/**
	 * Implement Zend_Server_Interface::handle()
	 *
	 * @param  array $request
	 * @throws Zend_Rest_Server_Exception
	 * @return string|void
	 */
	public function handle($request = false)
	{
		$this->setHeaders();
		if (!$request) {
			$request = $_REQUEST;
		}

		//We can sanitize $_GET and $_POST before using their contents or let each method deal with its parameters' cleanliness.
		$g = array();
		$p = array();
		if(isset($_GET) && count($_GET) > 0){
			$g = $_GET;
		}
		if(isset($_POST) && count($_POST) > 0){
			$p = $_POST;
		}

		//$_POST should always contain at least 'method' and 'header' properties
		if(count($g) == 1 && count($p) > 1){
			$m = array_keys($g);
			$this->_method = $m[0];
			if(isset($this->_functions[$this->_method])){
				if($this->_functions[$this->_method] instanceof Zend_Server_Reflection_Function || $this->_functions[$this->_method] instanceof Zend_Server_Reflection_Method && $this->_functions[$this->_method]->isPublic()){

					//Check if the request is valid					
					try{
						$this->_validateRequest($p);
					

						//Retrieve the request parameters, if any
						$request_params = array();
						if(array_key_exists('parameters',$p)){
							$request_params = $this->_parseParams($p['parameters']);
							//error_log(print_r($request_params,true),3,"/tmp/test.log");
							$request_keys = array_keys($request_params);
							array_walk($request_keys, array(__CLASS__, "lowerCase"));
							$request_params = array_combine($request_keys, $request_params);
						}
						$func_args = $this->_functions[$this->_method]->getParameters();
						$calling_args = array();
						$missing_args = array();
						foreach($func_args as $arg){
							if(isset($request_params[strtolower($arg->getName())])){
								$calling_args[] = $request_params[strtolower($arg->getName())];
							} elseif( $arg->isOptional()){
								$calling_args[] = $arg->getDefaultValue();
							} else {
								$missing_args[] = $arg->getName();
							}
						}

						foreach ($request_params as $key => $value) {
							if (substr($key, 0, 3) == 'arg') {
								$key = str_replace('arg', '', $key);
								$calling_args[$key] = $value;
								if (($index = array_search($key, $missing_args)) !== false) {
									unset($missing_args[$index]);
								}
							}
						}

						// Sort arguments by key -- @see ZF-2279
						ksort($calling_args);

						$result = false;
						if (count($calling_args) < count($func_args)) {
							require_once 'Zend/Rest/Server/Exception.php';
							$result = $this->fault(new Zend_Rest_Server_Exception('Invalid Method Call to ' . $this->_method . '. Missing argument(s): ' . implode(', ', $missing_args) . '.'), 400);
						}

						if (!$result && $this->_functions[$this->_method] instanceof Zend_Server_Reflection_Method) {
							// Get class
							$class = $this->_functions[$this->_method]->getDeclaringClass()->getName();

							if ($this->_functions[$this->_method]->isStatic()) {
								// for some reason, invokeArgs() does not work the same as
								// invoke(), and expects the first argument to be an object.
								// So, using a callback if the method is static.
								try {
									$result = $this->_callStaticMethod($class, $calling_args);
								} catch(Exception $e) {
									$result = $this->fault($e);
								}
							} else {
								// Object method
								try {
									$result = $this->_callObjectMethod($class, $calling_args);
								} catch(Exception $e){
									$result = $this->fault($e);
								}
							}
						} elseif (!$result) {
							try {
								$result = call_user_func_array($this->_functions[$this->_method]->getName(), $calling_args); //$this->_functions[$this->_method]->invokeArgs($calling_args);
							} catch (Exception $e) {
								$result = $this->fault($e);
							}
						}
					} catch (Exception $e){
						$result = $this->fault($e);
					}	
				} else {
					require_once "Zend/Rest/Server/Exception.php";
					$result = $this->fault(new Zend_Rest_Server_Exception("Unknown Method '$this->_method'."),404);
				}
			} else {
				require_once "Zend/Rest/Server/Exception.php";
				$result = $this->fault(new Zend_Rest_Server_Exception("Unknown Method '$this->_method'."),404);
			}
		} else {
			require_once "Zend/Rest/Server/Exception.php";
			$result = $this->fault(new Zend_Rest_Server_Exception("Malformed request."),400);
		}

	
		//error_log("REQUEST RESULT:\n".print_r($result,true)."\n",3,"/tmp/test.log");
		
		if (!$this->_faultResult){
			if (is_array($result) || is_object($result)) {
				$response = $this->_handleStruct($result);
			} else {
				$response = $this->_handleScalar($result);
			}
		} else {
			$response = $result;
		}

		$response = Zend_Json::encode($response,false);
		$response = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', create_function('$match', 'return mb_convert_encoding(pack("H*", $match[1]), "UTF-8", "UCS-2BE");'), $response);

		if (!$this->returnResponse()) {
			if (!headers_sent()) {
				foreach ($this->_headers as $header) {
					header($header);
				}
			}
			echo $response;
			return;
		}

		return $response;
	}

	public function fault($exception = null, $code = null)
	{
		if (isset($this->_functions[$this->_method])) {
			$function = $this->_functions[$this->_method];
		} elseif (isset($this->_method)) {
			$function = $this->_method;
		} else {
			$function = 'unknown';
		}

		if ($function instanceof Zend_Server_Reflection_Method) {
			$class = $function->getDeclaringClass()->getName();
		} else {
			$class = false;
		}

		if ($function instanceof Zend_Server_Reflection_Function_Abstract) {
			$method = $function->getName();
		} else {
			$method = $function;
		}

		$json = array();
                $json['header']['method'] = $method;
                //$json['header']['session'] = session_id();


		if ($exception instanceof Exception) {
			$json['response'] = array("message" => $exception->getMessage());
			$code = $exception->getCode();
		} elseif (($exception !== null) || 'rest' == $function) {
			$json['response'] = array("message" => 'An unknown error occurred. Please try again.');
		} else {
			$json['response'] = array("message" => 'Call to ' . $method . ' failed.');
		}

		$json['status'] = 'failure';

		// Headers to send
		if ($code === null){
			$this->_headers[] = 'HTTP/1.0 400 Bad Request';
		} else {
			$message = $exception->getMessage();
			switch($code){
				case 403:
					$this->_headers[] = empty($message) ? 'HTTP/1.0 403 Forbidden' : 'HTTP/1.0 403 '.$message;
					break;
				case 404:
					$this->_headers[] = empty($message) ? 'HTTP/1.0 404 File Not Found' : 'HTTP/1.0 404 '.$message;
					break;
				case 500:
					$this->_headers[] = empty($message) ? 'HTTP/1.0 500 Internal Server Error' : 'HTTP/1.0 500 '.$message;
					break;
				case 400:
					$this->_headers[] = empty($message) ? 'HTTP/1.0 400 Bad Request' : 'HTTP/1.0 400 '.$message;
					break;
				default:
					$this->_headers[] = 'HTTP/1.0 400 Bad Request';
					break;
			}
		}
		$this->_faultResult = true;
		return $json;
	}

	/**
	 * Handle an array or object result
	 *
	 * @param array|object $struct Result Value
	 * @return string XML Response
	 */
	protected function _handleStruct($struct)
	{
		$function = $this->_functions[$this->_method];
		$method = $function->getName();
		 
		$json = array();
		$json['header']['method'] = $method;
		//$json['header']['session'] = session_id();

		if(isset($struct)){
			if((is_array($struct) && count($struct) >= 1) || is_object($struct)){
				$json['response'] = (object) $struct;
			}
		} else {
			$json['response'] = null;
		}

		$json['status'] = 'success';

		return $json;
	}

	protected function _handleScalar($value)
	{
		$function = $this->_functions[$this->_method];
		$method = $function->getName();
		 
		$json = array();
		$json['header']['method'] = $method;
		//$json['header']['session'] = session_id();

		 
		if ($value === false) {
			$value = 0;
		} elseif ($value === true) {
			$value = 1;
		}

		if (isset($value)) {
			$json['response'] = $value;
		} else {
			$json['response'] = null;
		}

		$json['status'] = 'success';

		return $json;
	}

	protected function _parseParams($parameters){
		$result_parameter = $parameters;
		//This means we have more than one parameter and thus, we want it to be an object
		if(is_array($result_parameter) && count($result_parameter) > 1){
			$object_parameter = (object)$parameters;
			//$result_parameter = $object_parameter;
			$result_parameter = array('arg'=>$object_parameter);
		}
		return $result_parameter;
	}
	
	protected function _validateRequest($request){
		$request_method = array();
		$request_header = array();
		$result = FALSE;
		//Check if minimum required fields are present in the request
		if( array_key_exists('method',$request) && array_key_exists('header',$request) ){
			$request_method = $request['method'];
			$request_header = $request['header'];
			if( ($this->_method == $request_method) && array_key_exists('authorization', $request_header) && array_key_exists('date', $request_header) ){
				$client_authorization = trim($request_header['authorization']);
				$client_authorization = str_replace(array("\r","\n","\t"), '', $client_authorization);
				$client_date = trim($request_header['date']);
				if( preg_match('/BMP ([^:]+):(.+)/s', $client_authorization, $matches) ){
					$client_access_key = $matches[1];
					$client_signature = $matches[2];
					$result = $this->_validateAuthorization($client_access_key, $client_signature, $client_date, $request_method);	
				} else {
					throw new Exception("Malformed authorization header",400);
				}
			} else {
				throw new Exception("Missing headers",400);
			}
		} else {
			throw new Exception("Malformed request",400);
		}
		return $result;
	}
	
	private function _validateAuthorization($cl_access_key, $cl_signature, $cl_date, $r_method){
		if(!$cl_access_key || !$cl_signature || !$cl_date || !$r_method )
			throw new Exception("Malformed authorization header",400);
		
		//Date header check
		date_default_timezone_set('UTC');
		//$s_date = date(DATE_RFC1123);
		$s_timestamp = time();	
		if(($cl_timestamp = strtotime($cl_date)) == FALSE)
			throw new Exception("Malformed date header",400);
			
		//Check if the provided access key is registered in our database and if the request is coming from the expected referer
		require_once dirname(__FILE__) . '/services/utils/Config.php';
		require_once dirname(__FILE__) . '/services/utils/Datasource.php';
		try{
			$settings = new Config();
			$db = new DataSource($settings->host, $settings->db_name, $settings->db_username, $settings->db_password);
			
			//Query the DB for the provided accessKey
			$sql = "SELECT access_key, secret_access_key, allowed_referer, fk_user_id FROM moodle_api WHERE access_key = '%s' LIMIT 1";
			$result = $db->_singleSelect($sql,$cl_access_key);
		} catch(Exception $e){
			throw new Exception("Not found",404);
		}
		//error_log("QUERY ACCESS:".print_r($result,true),3,"/tmp/test.log");
		if($result){
			$s_secret_access_key = $result->secret_access_key;
			$s_access_key = $result->access_key;
			$s_referer = $result->allowed_referer;
		} else {
			throw new Exception("Invalid credentials provided",403);
		}
		
		//error_log("TIMESTAMPS: client: ".$cl_timestamp." server: ".$s_timestamp."\n",3,"/tmp/test.log");
	
		//Check if the request is skewed in time to avoid replication
		if( $cl_timestamp > ($s_timestamp - $this->allowed_time_skew) &&
		    $cl_timestamp < ($s_timestamp + $this->allowed_time_skew) ){

		    $s_stringtosign = utf8_encode($r_method . "\n" . $cl_date . "\n" . $s_referer);
		    	
			$digest = hash_hmac("sha256", $s_stringtosign, $s_secret_access_key, false);
			$s_signature = base64_encode($digest);
			
			$headers = apache_request_headers();
			$r_referer = isset($headers['Host']) ? $headers['Host'] : '';

			error_log(
				"REQUEST VALIDATION INFORMATION:\n
					\tMethod: ".$r_method."\n
					\tDate: ".$cl_date."\n
					\tDatabase allowed domain: ".$s_referer."\n
					\tRequest header domain: ".$r_referer."\n
					\tSecret access key: ".$s_secret_access_key."\n
					\tresulting signature: ".$s_signature."\n",3,"/tmp/moodle.log");
			if ($cl_signature == $s_signature){
				PluginSubset::$userId = $result->fk_user_id;
				//error_log("VALID SIGNATURE => USERID: ".$result->fk_user_id."\n",3,"/tmp/test.log");
				return TRUE;
			} else {
				throw new Exception("Invalid signature",403);
			}
		}else{
			throw new Exception("Request date is too skewed",403);
		}
	}

}
?>
