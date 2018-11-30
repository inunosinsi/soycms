<?php
function soycms_user_app_register($html, $page){

	$obj = $page->create("user_app_register", "HTMLTemplatePage", array(
		"arguments" => array("user_app_register", $html)
	));

	//プラグインがアクティブかどうか？
	if(file_exists(_SITE_ROOT_ . "/.plugin/AspAppPlugin.active")){

		$obj->addLabel("form", array(
			"html" => SOY2Logic::createInstance("site_include.plugin.asp_app.logic.BuildAspAppFormLogic")->build(),
			"soy2prefix" => "cms"
		));
	}

	$obj->display();
}
