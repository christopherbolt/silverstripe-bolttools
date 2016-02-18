<?php

class BoltEnvironment {
	
	public static function set_dev_servers($servers) {
	
		if(isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], $servers))  {
			global $databaseConfig, $database;
			$database = $databaseConfig['database'];
			//Director::set_environment_type("dev");
			// Include SS environment to override the database settings
			require_once("conf/ConfigureFromEnv.php");
		}
		
	}
}