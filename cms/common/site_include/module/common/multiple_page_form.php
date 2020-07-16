<?php

function soycms_multiple_page_form($html, $page){

	SOY2::import("site_include.plugin.multiple_page_form.util.MPFRouteUtil");
	MPFRouteUtil::doPost();	//GETの方も同時に調べる

	$hash = MPFRouteUtil::getPageHash();
	if($hash == "error") {
		multiple_page_form_empty_echo();
		return;
	}

	//前のページがある場合
	$prev = MPFRouteUtil::getPrevPageHash();
	MPFRouteUtil::getReplacementStringList();

	SOY2::import("site_include.plugin.multiple_page_form.util.MultiplePageFormUtil");
	$cnf = MultiplePageFormUtil::readJson($hash);

	//リピートハッシュを試す→同じページを何度も表示することが可能となる。
	if(!count($cnf)){
		$basisHash = MPFRouteUtil::getBasisHashByRepeatHash($hash);
		$cnf = MultiplePageFormUtil::readJson($basisHash);
	}
	switch($cnf["type"]){
		case MultiplePageFormUtil::TYPE_EXTEND:
			SOY2::import("site_include.plugin.multiple_page_form.util.MPFTypeExtendUtil");
			if(!isset($cnf["extend"]) || !strlen($cnf["extend"])) {
				multiple_page_form_empty_echo();
				return;
			}

			$classFilePath = MPFTypeExtendUtil::getPageDir() . $cnf["extend"] . ".class.php";
			if(!file_exists($classFilePath)) {
				multiple_page_form_empty_echo();
				return;
			}

			include_once($classFilePath);
			$form = SOY2HTMLFactory::createInstance($cnf["extend"]);
			$form->setHash($hash);
			$form->execute();
			echo $form->getObject();
			return;
		default:
			switch($cnf["type"]){
				case MultiplePageFormUtil::TYPE_CHOICE:
				case MultiplePageFormUtil::TYPE_CONFIRM_CHOICE:
					if(!isset($cnf["choice"]) || !is_array($cnf["choice"]) || !count($cnf["choice"])) {
						multiple_page_form_empty_echo();
						return;
					}
					$items = MultiplePageFormUtil::sortItems($cnf["choice"]);	//項目
					$values = MPFRouteUtil::getValues($hash);
					break;
				case MultiplePageFormUtil::TYPE_FORM:
					if(!isset($cnf["item"]) || !is_array($cnf["item"]) || !count($cnf["item"])) {
						multiple_page_form_empty_echo();
						return;
					}
					$items = MultiplePageFormUtil::sortItems($cnf["item"]);	//項目
					$values = MPFRouteUtil::getValues($hash);
					$isFirstView = (!count($values));	//はじめてフォームのページを開いた時
					break;
				case MultiplePageFormUtil::TYPE_COMPLETE:
					MPFRouteUtil::clear();	//ルート等を削除
					break;
				default:
					//何もしない
			}

			$description = (isset($cnf["description"])) ? htmlspecialchars($cnf["description"], ENT_QUOTES, "UTF-8") : "";
			include_once(MultiplePageFormUtil::getTemplateFilePath($cnf));
			return;
	}
}

//終了の際に使用する
function multiple_page_form_empty_echo(){
	echo "error";
}
