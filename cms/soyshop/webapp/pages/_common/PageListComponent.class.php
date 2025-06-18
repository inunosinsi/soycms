<?php

class PageListComponent extends HTMLList{

	protected function populateItem($entity){

		$this->addCheckBox("item_check", array(
			"name" => "pages[]",
            "value" => $entity->getId(),
            "visible" => AUTH_OPERATE
		));

		$this->addLabel("update_date", array(
			"text" => print_update_date($entity->getUpdateDate())
		));

		$this->addLabel("name", array(
			"text" => $entity->getName()
		));

		//多言語
		$langs = self::_multiLanguagePages($entity->getUri());
		$this->addModel("is_other_page", array(
			"visible" => (count($langs) > 0)
		));

		$this->createAdd("language_Link_list", "_common.PageLanguageLinkListComponent", array(
			"list" => $langs
		));

		$this->addLink("uri", array(
			"text" => "/" . $entity->getUri(),
			"link" => SOYSHOP_SITE_URL . $entity->getUri(),
			"target" => "_blank"
		));

		$this->addLabel("type_text", array(
			"text" => $entity->getTypeText()
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Site.Pages.Detail." . $entity->getId())
		));
z
		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Site.Pages.Remove." . $entity->getId())
		));
	}

	private function _multiLanguagePages(string $uri){
		if(!strlen($uri)) return array();
		$pageCnf = ltrim(str_replace(array(SOYShop_Page::URI_HOME, ".", "/"), "_", $uri)."_page.conf", "_");

		//多言語設定がある分だけ
		$prefixList = self::_getLanguagePrefixList();
		if(!count($prefixList)) return array();
		
		$mobilePrefixList = self::_getCarrierPrefixList();

		$allows = array();

		$dir = SOYSHOP_SITE_DIRECTORY.".page/";
		foreach($mobilePrefixList as $prefix){
			$filename = ltrim($prefix."_".$pageCnf,"_");
			if(file_exists($dir.$filename)){
				$allows[] = ltrim($prefix,"/");
			}
			
			foreach($prefixList as $lang){
				$filename = ltrim($prefix."_".$lang."_".$pageCnf,"_");
				if(file_exists($dir.$filename)){
					$allows[] = ltrim($prefix."/".$lang,"/");
				}
			}
		}

		if(!count($allows)) return array();

		if($uri == SOYShop_Page::URI_HOME){
			$sql = "SELECT id, uri FROM soyshop_page WHERE uri = '".SOYShop_Page::URI_HOME."'";
			foreach($allows as $prefix){
				$sql .= " OR uri = '".$prefix."'";
			}
		
			try{
				$res = soyshop_get_hash_table_dao("page")->executeQuery($sql);
			}catch(Exception $e){
				$res = array();
			}
		}else{
			try{
				$res = soyshop_get_hash_table_dao("page")->executeQuery(
					"SELECT id, uri FROM soyshop_page ".
					"WHERE uri LIKE :uri ",
					array(":uri" => "%".$uri)
				);
			}catch(Exception $e){
				$res = array();
			}
		}

		if(!count($res)) return array();

		$_arr = array();
		foreach($res as $v){
			$_prefix = str_replace("/".$uri, "", $v["uri"]);
			if(is_numeric(array_search($_prefix, $allows))){
				$_arr[(int)$v["id"]] = $_prefix;
			}
		}
		return $_arr;
	}

	private function _getLanguagePrefixList(){
		static $list;
		if(is_null($list)){
			$list = array();
			if(!SOYShopPluginUtil::checkIsActive("util_multi_language")) return $list;
			SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
			$confs = UtilMultiLanguageUtil::getConfig();
			foreach($confs as $lang => $conf){
				if(!isset($conf["prefix"]) || !strlen($conf["prefix"])) continue;
				if(!isset($conf["is_use"]) || (int)$conf["is_use"] != 1) continue;
				$list[] = $conf["prefix"];
			}
		}
		return $list;
	}

	private function _getCarrierPrefixList(){
		static $list;
		if(is_null($list)){
			$list = array("");
			if(!SOYShopPluginUtil::checkIsActive("util_mobile_check")) return $list;
			SOY2::import("module.plugins.util_mobile_check.util.UtilMobileCheckUtil");
			$cnf = UtilMobileCheckUtil::getConfig();
			$list[] = $cnf["prefix"];
			if(is_bool(array_search($cnf["prefix_i"], $list))){
				$list[] = $cnf["prefix_i"];
			}
		}
		return $list;
	}
}
