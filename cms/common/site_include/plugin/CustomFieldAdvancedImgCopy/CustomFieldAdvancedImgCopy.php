<?php
CustomFieldPluginAdvancedImgCopy::register();

class CustomFieldPluginAdvancedImgCopy{

	const PLUGIN_ID = "CustomFieldAdvancedCopy";

	private $postfix = "_copy";
	private $imgFieldIds;	//対象となるHTMLImageのfieldId
	private $imgProps = array();

	function getId(){
		return self::PLUGIN_ID;
	}
	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name" => "カスタムフィールドアドバンスド イメージフィールドコピー",
			"description" => "",
			"author" => "齋藤毅",
			"url" => "https://saitodev.co/article/3772",
			"mail" => "tsuyoshi@saitodev.co",
			"version"=>"0.1"
		));

		//プラグイン アクティブ
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){

			//管理側
			if(!defined("_SITE_ROOT_")){
				CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
					$this,"config_page"
				));
			//公開側
			}else{
				CMSPlugin::setEvent('onEntryOutput', self::PLUGIN_ID, array($this, "onEntryOutput"));
			}
		}
	}

	/**
	 * onEntryOutput
	 */
	function onEntryOutput($arg){
		$entryId = $arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];

		//設定内容を調べる
		$attrs = self::_getAttrs($entryId);
		if(count($attrs)){
			foreach($attrs as $fieldId => $attr){
				$props = $attr->getExtraValuesArray();
				$props["src"] = $attr->getValue();

				foreach($this->imgProps as $prop){
					if(!isset($props[$prop])) $props[$prop] = "";
				}
				$props["soy2prefix"] = "cms";

				$htmlObj->addImage($fieldId . $this->postfix, $props);
			}
		}
	}

	private function _getAttrs($entryId){
		self::_setImgFieldIds();
		if(!count($this->imgFieldIds)) return array();

		$attrDao = self::_dao();

		try{
			$results = $attrDao->executeQuery("SELECT * FROM EntryAttribute WHERE entry_id = :entryId AND entry_field_id IN (\"" . implode("\",\"", $this->imgFieldIds) . "\")", array(":entryId" => $entryId));
		}catch(Exception $e){
			$results = array();
		}

		$attrs = array();
		if(count($results)){
			foreach($results as $res){
				$attrs[$res["entry_field_id"]] = $attrDao->getObject($res);
			}
		}

		foreach($this->imgFieldIds as $fieldId){
			if(!isset($attrs[$fieldId])) $attrs[$fieldId] = new EntryAttribute();
		}

		return $attrs;
	}

	private function _setImgFieldIds(){
		if(!is_array($this->imgFieldIds)){
			$this->imgFieldIds = array();

			if(!class_exists("CustomFieldAdvanced")) SOY2::import("site_include.plugin.CustomFieldAdvanced.CustomFieldAdvanced", "php");
			$advObj = CMSPlugin::loadPluginConfig("CustomFieldAdvanced");
			if(count($advObj->customFields)){
				foreach($advObj->customFields as $fieldId => $field){
					if($field->getType() != "image") continue;	//取り急ぎHTMLImageのみ
					if(!strlen($field->getExtraOutputs())) continue;	//属性の設定をしていないものを除く
					$this->imgFieldIds[] = $fieldId;

					$outputs = explode("\n", $field->getExtraOutputs());
					if(count($outputs)){
						foreach($outputs as $out){
							$out = trim($out);
							if(is_numeric(array_search($out, $this->imgProps))) continue;
							$this->imgProps[] = $out;
						}
					}
				}
			}
		}
	}

	/**
	 * プラグイン管理画面の表示
	 */
	function config_page($message){
		SOY2::import("site_include.plugin.CustomFieldAdvancedImgCopy.config.CfacConfigPage");
		$form = SOY2HTMLFactory::createInstance("CfacConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		return $dao;
	}

	function getPostfix(){
		return $this->postfix;
	}
	function setPostfix($postfix){
		$this->postfix = $postfix;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new CustomFieldPluginAdvancedImgCopy();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
