<?php
/**
 * Clustering Functions for PHP interface
 * @package Cluster
 * @author Jorain Chua
 */
class Cluster {
			 
	const CLUSTER_LOCAL = "/home/msg/tmp/cluster.local";
	const CLUSTER_REMOTE = "/home/msg/tmp/cluster.remote";
	const CLUSTER_PREV = "/home/msg/tmp/cluster.prev";
	const CLUSTER_PORT = 9180;
	const CLUSTER_CONNECTTIMEOUT = 5;
	const CLUSTER_TIMEOUT = 5;
	
	protected $config_enable_file = '/home/msg/conf/cluster';
	protected $config_file = "/home/msg/conf/clusterconfig.xml";
	protected $pathlist_file = "/home/msg/conf/pathlist.xml";
	
	public function __construct() {
		$clusterconfig = simplexml_load_file($this->config_file);
		if ($clusterconfig === false) {
			foreach(libxml_get_errors() as $error) {
				error_log($error->message);
			}
		}
		$this->clusterconfig = $clusterconfig;
		
		$pathlistconfig = simplexml_load_file($this->pathlist_file);
		if ($pathlistconfig === false) {
			foreach(libxml_get_errors() as $error) {
				error_log($error->message);
			}
		}
		$this->pathlistconfig = $pathlistconfig;
		
	}
	 
	public function checkClusterFlag()
	{
		$flag = `cat $this->config_enable_file`;
		$flag = trim($flag);
		if ($flag == '1' || strtolower($flag) == 'y' || strtolower($flag) == 'yes'){
			return 1;
		}else{
			return 0;
		}
	}

	public function getClusterMode()
	{
		return $this->clusterconfig['system_mode'];
	}
	
	public function SyncConfig()
	{
        // Here we tell the secondary to download the system configuration
        // files and keep a backup
		if( $this->CallSecondary("setcf") != 1 ){
                return 0;
        }
        return 1;
	}
	
	public function SyncLocalBackup()
	{
		$ha_target_file = "/home/msg/conf/ha/targetlist";
		
		if(!file_exists($ha_target_file)){
			return;
		}
		$fh = @fopen($ha_target_file, "r");
		if($fh){
			while($line = fgets($fh)){
				$line = trim($line);
				$cmd = "cp /home/msg/conf/$line /home/msg/backup/orig/";
				exec($cmd);
			}
			fclose($fh);
		}
	}
	
	
	public function CallSecondary($action){
		$remote_ip = $this->clusterconfig['remote_ip'];
		$port = self::CLUSTER_PORT;
		$path = $this->pathlistconfig->$action;
		
		if(!isset($path) || strlen(trim($path))==0){
			error_log("PATH not found!");
			return 0;
		}
		
		$url = "http://" . $remote_ip . ':' . $port . $path;
		$data = array('m' => '1');
		$data_string = "m=1";
		
        //error_log("NOTE Accessing URL: $url");
        try{
			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,self::CLUSTER_CONNECTTIMEOUT);
			curl_setopt($ch, CURLOPT_TIMEOUT, self::CLUSTER_TIMEOUT);
			
			$res = curl_exec($ch);
			
			if (curl_errno($ch)) {
				error_log("Error: " . curl_error($ch)); 
				return 0;
			} else { 
				error_log("RES:".$res);
				//var_dump($res); 
				curl_close($ch); 
			}
		}catch(Exception $e){
			error_log("ERROR:".$e->getMessage());
			return 0;
		}
		
        return 1;
	}
	
}

?>
