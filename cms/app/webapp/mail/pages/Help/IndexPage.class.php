<?php

class IndexPage extends WebPage{

    function __construct() {
    	//SUPER USER以外には表示させない
    	if(CMSApplication::getAppAuthLevel() != 1)CMSApplication::jump("");
    	
    	parent::__construct();
    	    	
    	$this->addInput("crontab_exe_path", array(
    		"value" => $this->getCrontabExePath(),
    		"readonly" => true,
    		"onclick" => "this.select();",
    		"size" => 60
    	));
    	
    	$this->addLabel("crontab_exe_path_2", array(
    		"text" => $this->getCrontabExePath()
    	));
    	
    	
    }
    
    /**
     * @return string ジョブ実行スクリプトのパス
     */
    function getCrontabExePath(){
		$path = dirname(dirname(dirname(__FILE__))) . "/bin/cronjob.php";
    	return $path;
    }
}
?>