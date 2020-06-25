<?php

function soycms_multiple_page_form($html, $page){

	SOY2::import("site_include.plugin.multiple_page_form.util.MPFRouteUtil");
	MPFRouteUtil::doPost();	//GETの方も同時に調べる

	$hash = MPFRouteUtil::getPageHash();
	if($hash == "error") multiple_page_form_empty_echo();

	//前のページがある場合
	$prev = MPFRouteUtil::getPrevPageHash();
	MPFRouteUtil::getReplacementStringList();

	SOY2::import("site_include.plugin.multiple_page_form.util.MultiplePageFormUtil");
	$cnf = MultiplePageFormUtil::readJson($hash);
	switch($cnf["type"]){
		case MultiplePageFormUtil::TYPE_CHOICE:
			if(!isset($cnf["choice"]) || !is_array($cnf["choice"]) || !count($cnf["choice"])) multiple_page_form_empty_echo();

			$description = htmlspecialchars($cnf["description"], ENT_QUOTES, "UTF-8");
			$items = MultiplePageFormUtil::sortItems($cnf["choice"]);	//項目

			$values = MPFRouteUtil::getValues($hash);

			$templateDir = MultiplePageFormUtil::getTemplateDir() . $cnf["type"] . "/";
			include_once($templateDir . "default.php");	//@ToDo テンプレートの差し替えをできるようにしたい
			exit;
		case MultiplePageFormUtil::TYPE_FORM:
			if(!isset($cnf["item"]) || !is_array($cnf["item"]) || !count($cnf["item"])) multiple_page_form_empty_echo();

			$description = htmlspecialchars($cnf["description"], ENT_QUOTES, "UTF-8");
			$items = MultiplePageFormUtil::sortItems($cnf["item"]);	//項目

			$values = MPFRouteUtil::getValues($hash);
			$isFirstView = (!count($values));	//はじめてフォームのページを開いた時

			$templateDir = MultiplePageFormUtil::getTemplateDir() . "form/";
			include_once($templateDir . "default.php");	//@ToDo テンプレートの差し替えをできるようにしたい
			exit;
		case MultiplePageFormUtil::TYPE_EXTEND:
			SOY2::import("site_include.plugin.multiple_page_form.util.MPFTypeExtendUtil");
			if(!isset($cnf["extend"]) || !strlen($cnf["extend"])) multiple_page_form_empty_echo();

			$classFilePath = MPFTypeExtendUtil::getPageDir() . $cnf["extend"] . ".class.php";
			if(!file_exists($classFilePath)) multiple_page_form_empty_echo();

			include_once($classFilePath);
			$form = SOY2HTMLFactory::createInstance($cnf["extend"]);
			$form->setHash($hash);
			$form->execute();
			echo $form->getObject();
			exit;
		case MultiplePageFormUtil::TYPE_CONFIRM:
			$description = htmlspecialchars($cnf["description"], ENT_QUOTES, "UTF-8");
			$templateDir = MultiplePageFormUtil::getTemplateDir() . $cnf["type"] . "/";
			include_once($templateDir . "default.php");	//@ToDo テンプレートの差し替えをできるようにしたい
			exit;
		case MultiplePageFormUtil::TYPE_COMPLETE:
			MPFRouteUtil::clear();	//ルート等を削除

			$description = (isset($cnf["description"])) ? htmlspecialchars($cnf["description"], ENT_QUOTES, "UTF-8") : "";
			$templateDir = MultiplePageFormUtil::getTemplateDir() . $cnf["type"] . "/";
			include_once($templateDir . "default.php");	//@ToDo テンプレートの差し替えをできるようにしたい
			exit;
	}
}

//終了の際に使用する
function multiple_page_form_empty_echo(){
	echo "error";
	exit;
}
