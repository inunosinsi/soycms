<?php

class EditPage extends WebPage{
	
	var $target;
	
	function doPost(){
		// Referer, Token Check
		$referer = parse_url($_SERVER['HTTP_REFERER']);
		$_host = ($referer['host'] === $_SERVER['HTTP_HOST']);
		$_path = (($referer['path'] . "?" . $_SERVER['QUERY_STRING']) === $_SERVER['REQUEST_URI']);
		$_token = soy2_check_token();
		if(!($_host && $_path && $_token)){
  			CMSApplication::jump("Template");
			exit;
		}

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
  		$target = str_replace(Array("..", "\\"), "", $target);
  		$this->target = $target;
  		$dir = SOY2::RootDir() . "template/";
  		if(!file_exists($dir . $target) || !is_writable($dir.$target)){
  			CMSApplication::jump("Template");
  			exit;
  		}
  		
  		parent::__construct();
  		
  		$path = $dir . $target;
  		
  		$content = file_get_contents($path);
  		

  		$this->addInput("soy2_token", array(
			"name" => "soy2_token",
			"value" => soy2_get_token()
		));

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
