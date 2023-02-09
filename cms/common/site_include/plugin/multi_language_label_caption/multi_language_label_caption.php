<?php

class MultiLanguageLabelCaptionPlugin{

	const PLUGIN_ID = "multi_language_label_caption";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"多言語ラベルプラグイン",
			"type" => Plugin::TYPE_LABEL,
			"description"=>"ラベル名の多言語化",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/article/4972",
			"mail"=>"info@saitodev.co",
			"version"=>"0.1"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){

			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this,"config_page"
			));

			if(defined("_SITE_ROOT_")){	// 公開側
				CMSPlugin::setEvent('onLabelOutput', self::PLUGIN_ID, array($this, "onLabelOutput"));

			}else{	// 管理画面側
				CMSPlugin::setEvent('onLabelUpdate', self::PLUGIN_ID, array($this, "onLabelUpdate"));
				CMSPlugin::setEvent('onLabelCreate', self::PLUGIN_ID, array($this, "onLabelUpdate"));

				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Label.Caption", array($this, "onCallCustomField"));
			}
		}
	}

	function onLabelOutput($args){
		if(!defined("SOYCMS_PUBLISH_LANGUAGE")) define("SOYCMS_PUBLISH_LANGUAGE", "jp");
	
		$labelId = $args["labelId"];
		$htmlObj = $args["SOY2HTMLObject"];

		SOY2::import("site_include.plugin.util_multi_language.util.SOYCMSUtilMultiLanguageUtil");
		$caption = (is_numeric($labelId) && SOYCMS_PUBLISH_LANGUAGE != "jp") ? soycms_get_label_attribute_value($labelId, SOYCMSUtilMultiLanguageUtil::LANGUAGE_FIELD_KEY.SOYCMS_PUBLISH_LANGUAGE, "string") : "";
		if(is_numeric($labelId) && !strlen($caption)) {
			$caption = soycms_get_label_object($labelId)->getBranchName();
		}

		$htmlObj->createAdd("label_caption","CMSLabel",array(
			"text" => $caption,
			"soy2prefix" => "cms"
		));
	}

	/**
	 * ラベル更新時
	 */
	function onLabelUpdate($args){
		if(!isset($args["label"])) return false;
		
		$labelId = (int)$args["label"]->getId();
		if($labelId === 0) return false;
		
		SOY2::import("site_include.plugin.util_multi_language.util.SOYCMSUtilMultiLanguageUtil");
		foreach(array(SOYCMSUtilMultiLanguageUtil::LANGUAGE_EN) as $lng){
			$attr = soycms_get_label_attribute_object($labelId, SOYCMSUtilMultiLanguageUtil::LANGUAGE_FIELD_KEY.$lng);
			$attr->setValue(trim($_POST["Lang"][$lng]));
			soycms_save_label_attribute_object($attr);
		}
		return true;
	}

	/**
	 * ラベル編集画面
	 * @return string HTMLコード
	 */
	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$labelId = (isset($arg[0])) ? (int)$arg[0] : 0;
		SOY2::import("site_include.plugin.".self::PLUGIN_ID.".form.MultiLanguageLabelCaptionFormPage");
		$form = SOY2HTMLFactory::createInstance("MultiLanguageLabelCaptionFormPage");
		$form->setLabelId($labelId);
		$form->execute();
		return $form->getObject();
	}

	/**
	 *
	 * @return $html
	 */
	function config_page($message){
return <<<HTML
現在は英語サイト用のラベル名の設定のみ。<br>
他言語化を加味して出力される<strong>cms:id="label_caption"</strong>が使えます。
HTML;
	}


	
	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new MultiLanguageLabelCaptionPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}

MultiLanguageLabelCaptionPlugin::register();