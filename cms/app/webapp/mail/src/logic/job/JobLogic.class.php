<?php

class JobLogic extends SOY2LogicBase{

	/**
	 * 次のジョブを登録する
	 *
	 * @return 次回実行時刻(int)
	 */
	private $spanTime = 10;
    public function registNextJob(){

		$commands = array(
			"php",
			'"' . SOYMAIL_BIN_DIR . "/job.php" . '"',
			'-job'
		);
		$time = time() + 60 * $this->spanTime;	//10 minutes afrer

		$res = $this->execAt($commands);

		return ($res) ? $time : null;

    }
    
	/**
	 */
    private function execAt($commands){
		return (strpos(PHP_OS, "WINNT") !== false) ? $this->execAtOnWindows($commands) : $this->execAtOnLinux($commands);
    }

	/**
	 */
    private function execAtOnWindows($command){
		$command = "at " . date("H:i",time()+$this->spanTime*60) . " " . implode(" ", $command);
		$res = exec($command);

		if(strstr($res,"ID = ")){
			return true;
		}

		return false;
    }

	/**
	 */
	private function execAtOnLinux($command){
		$command = "echo '" . str_replace("\"","",implode(" ", $command)) . "' | at now + ". $this->spanTime."minute";
		$output = array();
		$var = array();
		$res = exec($command,$output,$var);
		return true;
		
    }
}
?>