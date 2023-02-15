<?php

class Logger
{
	private $logfile;
	private $logtag;
	
	public function __construct($dfile){
		$this->setFile($dfile);
	}

	public function setFile($file){
		$this->logfile = $file;
	}

	public function setTag($dtag){
		$this->logtag = $dtag;
	}

	public function setMessage($str){
		$this->err_message = $str;
	}

	public function getMessage(){
		return $this->err_message;
	}

	public function logMessage($str){
		if (empty($str) ) return;
		if( !file_exists($this->logfile) ) $this->logfile = "/home/msg/logs/agent.log";

		if( !is_writable($this->logfile) ){
			echo "Error - Unable to update log file, PERMISSION DENIED.";
			return;
		}
		
		if( !($fh = fopen($this->logfile, "a")) ){
			echo "Error - Unable to open log file.";
			return;
		}

		if( !empty($this->logtag) )
			$str = strftime("%Y-%m-%d %H:%M:%S", time()) . " " . $this->logtag . " " . $str . "\n";
		else	
			$str = strftime("%Y-%m-%d %H:%M:%S", time()) . " " . $str . "\n";
		
		if( fwrite($fh, $str) === FALSE ){
			echo "Error - Unable to log message.";	
			return;
		}

		fclose($fh);
	}
}
	
?>
