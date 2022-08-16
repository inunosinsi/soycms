<?php
function soycms_user_register($html, $page){

	$obj = $page->create("user_register", "HTMLTemplatePage", array(
		"arguments" => array("user_register", $html)
	));

	//プラグインがアクティブかどうか？
	if(CMSPlugin::activeCheck("AspPlugin")){
		$obj->addLabel("form", array(
			"html" => SOY2Logic::createInstance("site_include.plugin.asp.logic.BuildAspFormLogic")->build(),
			"soy2prefix" => "cms"
		));
	}

	$obj->display();
}
