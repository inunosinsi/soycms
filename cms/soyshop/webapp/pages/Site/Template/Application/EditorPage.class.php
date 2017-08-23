<?php

class EditorPage extends WebPage{
	
	private $args;
	private $filepath;
	
	function doPost(){
		
		if(soy2_check_token()){
			file_put_contents($this->filepath,$_POST["template_content"]);
		}
		
		$url = SOY2PageController::createLink("Site.Template.Application.Editor.-.");
		for($i = 0; $i < count($this->args); $i++){
			$url .= $this->args[$i];
			if($i !== count($this->args)) $url .= "/";
		}

		SOY2PageController::redirect($url . "?updated");
	}
	
	function __construct($args){
		$this->args = $args;
		
		$tmpLogic = SOY2Logic::createInstance("logic.site.template.TemplateLogic");
		$this->filepath = $tmpLogic->getTemplateFile($args);
		
		//ファイルが取得できなければ、アプリケーションテンプレート一覧に戻る
		if(is_null($this->filepath)) SOY2PageController::jump("Site.Template.Application");
		
		parent::__construct();
		
		$this->addForm("form");
		
		$this->addLabel("template_path", array(
			"text" => $this->filepath
		));
		
		$this->addTextArea("template_content", array(
			"name" => "template_content",
			"value" => file_get_contents($this->filepath)
		));
	}
}
?>