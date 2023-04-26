<?php
/*
 * Created on 2010/07/24
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

SOYShopItemImportPlugin::register();

class SOYShopItemImportPlugin{

	const PLUGIN_ID = "SOYShopItemImport";
	private $siteId = "shop";
	private $prefix;

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"SOYShop商品紹介プラグイン",
			"type" => Plugin::TYPE_SOYAPP,
			"description"=>"SOY Shopで登録した商品をSOY CMSのブログで紹介する",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co",
			"mail"=>"info@saitodev.co",
			"label" => "",
			"entry" => "",
			"version"=>"0.11"
		));

		//二回目以降の動作
		if(CMSPlugin::activeCheck($this->getId())){

			//SOY Shopがインストールされていれば動く
			if(!class_exists("SOYShopUtil")) SOY2::import("util.SOYShopUtil");
			if(SOYShopUtil::checkSOYShopInstall()){
				CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
					$this,"config_page"
				));

				if(!defined("_SITE_ROOT_")){	//管理画面側
					CMSPlugin::addCustomFieldFunction($this->getId(), "Entry.Detail", array($this, "onCallCustomField"));
					CMSPlugin::addCustomFieldFunction($this->getId(), "Blog.Entry", array($this, "onCallCustomField_inBlog"));

					//記事作成時にキーワードとdescriptinをDBに挿入する
					CMSPlugin::setEvent('onEntryUpdate', $this->getId(), array($this, "onEntryUpdate"));
					CMSPlugin::setEvent('onEntryCreate', $this->getId(), array($this, "onEntryUpdate"));

				}else{	//公開画面側
					//公開側のページを表示させたときに動作する
					CMSPlugin::setEvent('onPageOutput', self::PLUGIN_ID, array($this, "onPageOutput"));
				}
			}
		}
	}

	/**
	 * 公開時はEntryテーブルの値をそのまま表示する
	 * 商品の価はシリアライズして保存しておいて、
	 * 公開時にアンシリアライズして表示する。
	 */
	function onPageOutput($obj){
		/** 詳細ページもしくはカテゴリページでしか動作しません **/
		if(!$obj instanceof CMSBlogPage || !property_exists($obj, "mode")) return;
		if($obj->mode != "_entry_" && $obj->mode != "_category_") return;
		
		if(!defined("SOYCMS_PUBLISH_LANGUAGE")) define("SOYCMS_PUBLISH_LANGUAGE", "jp");
		if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", SOYCMS_PUBLISH_LANGUAGE);

		//詳細ページの場合はDNS切替をする前に商品情報を取得
		$entryId = ($obj->mode == "_entry_" && property_exists($obj, "entry")) ?  (int)$obj->entry->getId() : 0;
		$item = self::_getItemByEntryId($entryId);
		
		$old = SOYShopUtil::switchShopMode($this->siteId);

		self::_constantSOYShopConfig();	//SOY Shopと連携するために必要なファイル群を読み込む

		//カテゴリページの場合はDNS切替をした後に商品情報を取得
		if($obj->mode == "_category_") $item = self::_getItemByName(trim($obj->label->getCaption()));

		$obj->addModel("item", array(
			"soy2prefix" => "i_block",
			"visible" => (is_numeric($item->getId()))
		));

		soyshop_output_item($obj, $item);
		
		// @ToDo カスタムフィールドの値を取得してみる

		//カスタムサーチフィールドの値を取得してみる
		if(SOYShopPluginUtil::checkIsActive("custom_search_field")){
			self::checkMultiLanguagePrefix();

			SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
			$values = SOY2Logic::createInstance("module.plugins.custom_search_field.logic.DataBaseLogic")->getByItemId($item->getId());

			foreach(CustomSearchFieldUtil::getConfig() as $key => $field){
				if(!isset($values[$key])) continue;
				$csfValue = $values[$key];
				$obj->addModel($key . "_visible", array(
					"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
					"visible" => (strlen($csfValue))
				));

				$obj->addLabel($key, array(
					"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
					"html" => (isset($csfValue)) ? $csfValue : null
				));

				switch($field["type"]){
					case CustomSearchFieldUtil::TYPE_CHECKBOX:
					//多言語対応
						if(isset($field["option"]) && count($field["option"])){
							$vals = explode(",", $csfValue);
							$opts = explode("\n", $field["option"][SOYCMS_PUBLISH_LANGUAGE]);
							foreach($opts as $i => $opt){
								$opt = trim($opt);
								$obj->addModel($key . "_"  . $i . "_visible", array(
									"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
									"visible" => (in_array($opt, $vals))
								));

								$obj->addLabel($key . "_" . $i, array(
									"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
									"text" => $opt
								));
							}
						}
						break;
				}
			}
		}

		SOYShopUtil::resetShopMode($old);
	}

	/**
	 * SOY Shopと連携するために必要なファイル群を読み込む
	 */
	private function _constantSOYShopConfig(){
		if(!defined("SOYSHOP_SITE_PREFIX")) define("SOYSHOP_SITE_PREFIX", "cms");
		SOY2::import("logic.plugin.SOYShopPlugin");
		SOY2::import("base.site.classes.SOYShop_ItemListComponent");
		SOY2::import("base.func.common", ".php");
		SOY2::imports("domain.config.*");
		SOY2::imports("domain.shop.*");
		SOY2::import("util.SOYShopPluginUtil");

		if(!defined("SOYSHOP_IS_ROOT")){
			$file = @file_get_contents($_SERVER["DOCUMENT_ROOT"] . "index.php");
			if(isset($file) && preg_match('/\("(.*)\//', $file, $res)){
				$isRoot = ($res[1] == $this->siteId) ? true : false;
			}else{
				$isRoot = false;
			}
			define("SOYSHOP_IS_ROOT", $isRoot);
		}

		include_once(SOY2::RootDir() . "module/site/common/output_item.php");
	}

	/**
	 * doPost代わり
	 * doPost時にの設定を変えて、ショップから商品情報を取得し、Entryテーブルに保存
	 */
	function onEntryUpdate($arg){

		$old = SOYShopUtil::switchShopMode($this->siteId);

		//商品コードの取得
		$code = trim($_POST["item_code"]);

		try{
			$item = SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getByCode($code);
		}catch(Exception $e){
			$item = new SOYShop_Item();
		}

		//設定を元に戻す
		SOYShopUtil::resetShopMode($old);

		$entry = $arg["entry"];
		$entryAttributeDao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		try{
			$entryAttributeDao->delete($entry->getId(), self::PLUGIN_ID);
		}catch(Exception $e){
			//
		}

		//商品コードがあれば登録
		if(!is_null($item->getCode())){

			$attr = new EntryAttribute();
			$attr->setEntryId($entry->getId());
			$attr->setFieldId(self::PLUGIN_ID);
			$attr->setValue($item->getCode());

			try{
				$entryAttributeDao->insert($attr);
			}catch(Exception $e){
				return false;
			}
		}

		return true;
	}

	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : 0;

		$item = self::_getItemByEntryId($entryId);

		ob_start();
		include(dirname(__FILE__) . "/form.php");
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : 0;

		$item = self::_getItemByEntryId($entryId);

		ob_start();
		include(dirname(__FILE__) . "/form.php");
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * @param int
	 * @return SOYShop_Item
	 */
	private function _getItemByEntryId(int $entryId){
		$code = ($entryId > 0) ? (string)soycms_get_entry_attribute_object($entryId, self::PLUGIN_ID)->getValue() : "";

		$old = SOYShopUtil::switchShopMode($this->siteId);
		try{
			$item = SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getByCode($code);
		}catch(Exception $e){
			$item = new SOYShop_Item();
		}
		SOYShopUtil::resetShopMode($old);

		return $item;
	}

	/**
	 * @param string
	 * @return SOYShop_Item
	 */
	private function _getItemByName(string $name){
		try{
			return SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getByName($name);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}

	private function checkMultiLanguagePrefix(){
		//多言語の方も念のため
		if(!defined("SOYCMS_PUBLISH_LANGUAGE")) define("SOYCMS_PUBLISH_LANGUAGE", "jp");
		if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", SOYCMS_PUBLISH_LANGUAGE);

		//多言語化のプレフィックスでも調べてみる
		if(is_null($this->prefix)){
			SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
			$config = UtilMultiLanguageUtil::getConfig();
			$prefix = (isset($config[SOYCMS_PUBLISH_LANGUAGE]["prefix"])) ? trim($config[SOYCMS_PUBLISH_LANGUAGE]["prefix"]) : SOYCMS_PUBLISH_LANGUAGE;
		}
	}

	function config_page(){
		include_once(dirname(__FILE__) . "/config/SOYShopItemImportConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("SOYShopItemImportConfigFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new SOYShopItemImportPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj, "init"));
	}

	function getSiteId(){
		return $this->siteId;
	}
	function setSiteId($siteId){
		$this->siteId = $siteId;
	}
}
