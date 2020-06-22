<?php

function soycms_multiple_page_form($html, $page){

	SOY2::import("site_include.plugin.multiple_page_form.util.MultiplePageFormUtil");

	//soy2_tokenがある場合は次のページを調べてリダイレクト GET版
	if(isset($_GET["soy2_token"]) && soy2_check_token()){
		$nextToken = $_GET["next"];
		// @ToDo routeを記録
		header("Location:" . $_SERVER["REDIRECT_URL"]);	//とりあえずGETパラメータ付きのページは禁止
		exit;
	}

	//@ToDo セッションに何も値がない場合は1ページ目を表示する　1ページ目を取得する方法は要検討
	if(true){
		$pages = MultiplePageFormUtil::getPageList();
		if(!count($pages)) multiple_page_form_empty_echo();

		$hash = array_keys($pages)[0];
	}else{	//2ページ目以降

	}

	//@ToDo 前のページがある場合
	$prev = null;

	$cnf = MultiplePageFormUtil::readJson($hash);
	switch($cnf["type"]){
		case MultiplePageFormUtil::TYPE_CHOICE:
			if(!isset($cnf["choice"]) || !is_array($cnf["choice"]) || !count($cnf["choice"])) multiple_page_form_empty_echo();

			$description = htmlspecialchars($cnf["description"], ENT_QUOTES, "UTF-8");
			$items = MultiplePageFormUtil::sortItems($cnf["choice"]);	//項目

			$templateDir = MultiplePageFormUtil::getTemplateDir() . "choice/";
			include_once($templateDir . "default.php");	//@ToDo テンプレートの差し替えをできるようにしたい
			exit;
		case MultiplePageFormUtil::TYPE_FORM:
			break;
		case MultiplePageFormUtil::TYPE_EXTEND:
			SOY2::import("site_include.plugin.multiple_page_form.util.MPFTypeExtendUtil");
			if(!isset($cnf["extend"]) || !strlen($cnf["extend"])) multiple_page_form_empty_echo();

			$classFilePath = MPFTypeExtendUtil::getPageDir() . $cnf["extend"] . ".class.php";
			if(!file_exists($classFilePath)) multiple_page_form_empty_echo();

			include_once($classFilePath);
			$form = SOY2HTMLFactory::createInstance($cnf["extend"]);
			$form->execute();
			echo $form->getObject();
			exit;
		default:	//確認用のページを出力する
			echo "";
			exit;
	}
}

//終了の際に使用する
function multiple_page_form_empty_echo(){
	echo "error";
	exit;
}
