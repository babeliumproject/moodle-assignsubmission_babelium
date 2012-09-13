<?php

require_once 'Datasource.php';
require_once 'Config.php';

class ACL{

	private $settings;
	private $db;

	public function __construct(){
		//Database connection
		try{
			$this->settings = new Config();
			$this->db = new DataSource($this->settings->host, $this->settings->db_name, $this->settings->db_username, $this->settings->db_password);
		} catch(Exception $e){
			throw new Exception($e->getMessage());
		}

	}
	
	public function moodleApiGrant($userId, $referer){
		if(!$userId || !$referer)
			return "You must provide all the parameters";
			
		$accesskey = $this->str_makerand(20,true,true,false);
		$secretaccesskey = $this->str_makerand(40,true,true,true);
		$result = $this->db->_insert($sql,$accesskey,$secretaccesskey,$referer,$userId);
		if($result){
			echo "Successfully granted moodle access to the user ".$userId."for the domain ".$referer."\n";
			echo "\tAccessKey: ".$accesskey."\n";
			echo "\tSecretAccessKey: ".$secretaccesskey."\n";
		}
	}

	//A method to generate random keys of the desired length
	private function str_makerand ($length, $useupper, $usenumbers, $usesymbols)
	{
		$key= '';
		$charset = "abcdefghijklmnopqrstuvwxyz";
		if ($useupper)
		$charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if ($usenumbers)
		$charset .= "0123456789";
		if($usesymbols)
		//This are all the printable symbols of the ASCII table except " left out for sequence escaping reasons
		$charset .= " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";

		for ($i=0; $i<$length; $i++)
		$key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
		return $key;
	}


}