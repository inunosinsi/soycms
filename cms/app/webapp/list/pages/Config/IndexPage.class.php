<?php

class IndexPage extends WebPage{
	
	private $configDao;
	
	function doPost(){
		
		if(soy2_check_token()){
			
			$config = $_POST["Config"];
			
			//サイズの書式チェック
			$resize_x = (int)mb_convert_kana($config["resize"]["width"],"a");
			$resize_y = (int)mb_convert_kana($config["resize"]["height"],"a");
			$config["resize"]["width"] = ($resize_x > 0) ? $resize_x : null;
			$config["resize"]["height"] = ($resize_y > 0) ? $resize_y : null;
			
			$obj = new SOYList_Config();
			$obj->setConfig(soy2_serialize($config));
			
			try{
				$this->configDao->update($obj);
			}catch(Exception $e){
				var_dump($e);
			}
			CMSApplication::jump("Config?updated");
		}
		
	}
	
	function __construct(){
		$this->configDao = SOY2DAOFactory::create("SOYList_ConfigDAO");
		$obj = $this->configDao->get();
		$config = $obj->getConfigArray();
		
		WebPage::WebPage();
		
		$this->addModel("updated",array(
    		"visible" => (isset($_GET["updated"]))
    	));
    	
    	$this->addForm("form");
    	
    	$this->addInput("resize_x",array(
    		"name" => "Config[resize][width]",
    		"value" => (isset($config["resize"]["width"])) ? $config["resize"]["width"] : ""
    	));
    	
    	$this->addInput("resize_y",array(
    		"name" => "Config[resize][height]",
    		"value" => (isset($config["resize"]["height"])) ? $config["resize"]["height"] : ""
    	));
	}
	
}

?>