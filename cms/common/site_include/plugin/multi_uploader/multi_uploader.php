<?php

MultiUploaderPlugin::register();
class MultiUploaderPlugin{

	const PLUGIN_ID = "multi_uploader";

	private $labelIds = array();

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name" => "記事毎画像アップロードプラグイン",
			"description" => "記事毎に複数画像ファイルをアップロードできるプラグイン",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/article/3150",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.7"
		));

		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			SOY2::import("site_include.plugin.multi_uploader.util.MultiUploaderUtil");
			SOY2::import("site_include.plugin.multi_uploader.component.MultiUploaderImageListComponent");
			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onEntryOutput', self::PLUGIN_ID, array($this, "onEntryOutput"));
			}else{
				CMSPlugin::setEvent("onEntryCreate", self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent("onEntryUpdate", self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_inBlog"));
			}
		}
	}

	function onEntryOutput($arg){
		$htmlObj = &$arg["SOY2HTMLObject"];
		$entryId = (isset($arg["entryId"]) && is_numeric($arg["entryId"])) ? (int)$arg["entryId"] : 0;

		$images = ($entryId > 0) ? MultiUploaderUtil::getImagePathes($entryId) : array();
		$isImages = (count($images));

		$htmlObj->addModel("is_multi_uploader_image", array(
			"soy2prefix" => "cms",
			"visible" => $isImages
		));

		$htmlObj->createAdd("multi_uploader_image_list", "MultiUploaderImageListComponent", array(
			"soy2prefix" => "p_block",
			"list" => ($isImages) ? $images : array(),
			"alts" => ($entryId > 0) ? MultiUploaderUtil::getAltList($entryId) : array()
		));
	}

	function onEntryUpdate($arg){
		$entry = &$arg["entry"];

		//画像の登録
		if(isset($_POST[MultiUploaderUtil::FIELD_ID]) && strlen($_POST[MultiUploaderUtil::FIELD_ID])){
			MultiUploaderUtil::save($entry->getId(), $_POST[MultiUploaderUtil::FIELD_ID]);
		}

		// @ToDo ソート

		//削除
		if(isset($_POST[MultiUploaderUtil::FIELD_ID . "_delete"])){
			$deleteList = array();
			foreach($_POST[MultiUploaderUtil::FIELD_ID . "_delete"] as $delHash => $on){
				if($on != 1) continue;
				$deleteList[] = $delHash;
			}

			if(count($deleteList)){
				$images = MultiUploaderUtil::getImagePathes($entry->getId());
				$leaveList = array();	//残すリスト
				foreach($images as $path){
					if(is_numeric(array_search(MultiUploaderUtil::path2Hash($path), $deleteList))) continue;
					$leaveList[] = $path;
				}

				MultiUploaderUtil::update($entry->getId(), array_unique($leaveList));
			}
		}

		//並び替え
		if(isset($_POST[MultiUploaderUtil::FIELD_ID . "_sort"])){
			$sortList = array();
			$doSort = false;
			foreach($_POST[MultiUploaderUtil::FIELD_ID . "_sort"] as $sortHash => $sort){
				if(is_numeric($sort) && $sort > 0){
					$doSort = true;
				}else{
					$sort = 999;
				}
				$sortList[$sortHash] = $sort;
			}

			//ソートする
			if($doSort){
				asort($sortList);
				$images = MultiUploaderUtil::getImagePathes($entry->getId());
				$resultList = array();	//結果リスト
				foreach($sortList as $sortHash => $s){
					foreach($images as $path){
						if(MultiUploaderUtil::path2Hash($path) == $sortHash){
							$resultList[] = $path;
							break;
						}
					}
				}

				MultiUploaderUtil::update($entry->getId(), array_unique($resultList));
			}
		}

		//alt
		if(isset($_POST[MultiUploaderUtil::FIELD_ID . "_alt"])){
			foreach($_POST[MultiUploaderUtil::FIELD_ID . "_alt"] as $hash => $alt){
				$alt = trim($alt);
				MultiUploaderUtil::updateAlt($entry->getId(), $hash, $alt);
			}
		}
	}

	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0]) && is_numeric($arg[0])) ? (int)$arg[0] : 0;

		SOY2::import("site_include.plugin.multi_uploader.component.MultiUploaderFormComponent");
		return MultiUploaderFormComponent::buildForm($entryId, $this->labelIds);
	}

	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1]) && is_numeric($arg[1])) ? (int)$arg[1] : 0;

		SOY2::import("site_include.plugin.multi_uploader.component.MultiUploaderFormComponent");
		return MultiUploaderFormComponent::buildForm($entryId, $this->labelIds);
	}

	function config_page(){
		SOY2::import("site_include.plugin.multi_uploader.config.MultiUploaderConfigPage");
		$form = SOY2HTMLFactory::createInstance("MultiUploaderConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function getLabelIds(){
		return $this->labelIds;
	}
	function setLabelIds($labelIds){
		$this->labelIds = $labelIds;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new MultiUploaderPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
