<?php

class IndexPage extends WebPage{

	function doPost(){
		
		if(soy2_check_token()&&$_POST["Config"]){
			$dao = SOY2DAOFactory::create("SOYLpo_ConfigDAO");
			$config = $_POST["Config"];
			if(!isset($config["wisywig"])){
				$config["wisywig"]="0";
			}
			$config = SOY2::cast("SOYLpo_Config",$config);
			
			try{
				$dao->update($config);
			}catch(Exception $e){
				var_dump($e);
			}
			
			CMSApplication::jump("Config?updated");
		}
		
	}

    function __construct() {
    	//SUPER USER以外には表示させない
    	if(CMSApplication::getAppAuthLevel() != 1)CMSApplication::jump("");
    	
    	WebPage::WebPage();
    	
    	$config = $this->getConfig();
    	
    	$this->createAdd("updated","HTMLModel",array(
    		"visible" => (isset($_GET["updated"]))
    	));
    	
    	$this->createAdd("form","HTMLForm");
    	$this->createAdd("wisywig","HTMLCheckBox",array(
    		"name" => "Config[wisywig]",
    		"value" => 1,
    		"selected" => ($config->getWisywig()==1),
    		"label" => "WISYWIGを利用する"
    	));
    }
    
    function getConfig(){
    	$dao = SOY2DAOFactory::create("SOYLpo_ConfigDAO");
    	try{
    		$result = $dao->get();
    		$config = $result[0];
    	}catch(Exception $e){
    		$config = new SOYLpo_Config();
    	}
    	return $config;
    }
}
?>