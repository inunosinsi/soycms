<?php

class EditPage extends WebPage{
	
	var $target;
	
	function doPost(){
		
		$target = $this->target;
  		$dir = SOY2::RootDir() . "template/";
  		if(!file_exists($dir . $target) || !is_writable($dir.$target)){
  			CMSApplication::jump("Template");
  			exit;
  		}
  		
  		$path = $dir . $target;
  		
  		//bk
  		$content = file_get_contents($path);
  		file_put_contents($path . "_" . date("YmdHis"),$content);
  		
  		$content = $_POST["content"];
  		file_put_contents($path,$content);
		
		CMSApplication::jump("Template");
		exit;
	}
	
    function __construct() {
  		
  		$target = @$_GET["target"];
  		$this->target = $target;
  		$dir = SOY2::RootDir() . "template/";
  		if(!file_exists($dir . $target) || !is_writable($dir.$target)){
  			CMSApplication::jump("Template");
  			exit;
  		}
  		
  		WebPage::__construct();
  		
  		$path = $dir . $target;
  		
  		$content = file_get_contents($path);
  		
  		$this->createAdd("target","HTMLLabel",array(
  			"text" => $target
  		));
  		
  		$this->createAdd("content","HTMLTextArea",array(
  			"name" => "content",
  			"value" => $content
  		));
	}
}
?>