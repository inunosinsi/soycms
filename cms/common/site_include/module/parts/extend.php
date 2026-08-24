<?php
class LangLabel extends HTMLLabel {

	private $htmlObj;

	function execute(){	
		$this->setText($this->_soy2_innerHTML);
		parent::execute();
	}

	function setHtmlObj(HTMLPage $htmlObj){
		$this->htmlObj = $htmlObj;
	}
}

function soycms_extend(string $html, HTMLPage $htmlObj){

	$obj = $htmlObj->create("soycms_extend", "HTMLTemplatePage", array(
		"arguments" => array("soycms_extend", $html)
	));

	// 下記のコードで soy:id="test"タグが使用できるようになる
	$obj->createAdd("test", "LangLabel", array(
		"soy2prefix" => "cms",
		"htmlObj" => $htmlObj		
	));

	for($i = 0; $i < 5; $i++){
		$obj->createAdd("test_".$i, "LangLabel", array(
			"soy2prefix" => "cms",
			"htmlObj" => $htmlObj		
		));	
	}

	// createAddを使う場合は最後に下記のコードを加えておく
	$obj->display();
}
