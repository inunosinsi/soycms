<?php
SOYCMS_SwitchEditor::registerPlugin();

class SOYCMS_SwitchEditor{

	const PLUGIN_ID = "soycms_switch_editor";

	private $labelConfig = array();
	private $WYSIWYGConfig;

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"エディタ切り替えプラグイン",
			"type" => Plugin::TYPE_ENTRY,
			"description"=>"ラベル毎にWYSIWYGエディタを使用するかどうか切り替えます。<br />選択できるWYSIWYGエディタは<ul>" .
					"<li>tinyMCE</li>" .
					"</ul>",
			"author"=>"株式会社Brassica",
			"url"=>"https://brassica.jp/",
			"mail"=>"soycms@soycms.net",
			"version"=>"2.2.2"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this,"config_page"
			));

			CMSPlugin::setEvent("onSetupWYSIWYG", self::PLUGIN_ID, array($this, "onSetupWYSIWYG"));
		}
	}

	function getId(){
		return self::PLUGIN_ID;
	}

	function config_page(){
		//不要なファイルは削除する
		self::_removeUnnecessaryFiles();

		SOY2::import("site_include.plugin.soycms_switch_editor.config.SwitchEditorConfigPage");
		$form = SOY2HTMLFactory::createInstance("SwitchEditorConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function onSetupWYSIWYG(array $args){
		$entryId = $args["id"];
		$labelIds = $args["labelIds"];
		if(!isset($labelIds) || !is_array($labelIds)) $labelIds = array();

		$labels = SOY2DAOFactory::create("cms.EntryLabelDAO")->getByEntryId($entryId);
		if(count($labels)){
			foreach($labels as $label){
				$labelIds[] = $label->getLabelId();
			}
		}


		$out = array();
		if(is_array($this->labelConfig) && count($this->labelConfig)){
			foreach($this->labelConfig as $labelId => $on){
				if($on){
					$out[] = $labelId;
				}
			}
		}


		if(is_null($this->WYSIWYGConfig)){
			$_COOKIE["entry_text_editor"] = "tinyMCE";
		}else{
			//CKEditorの廃止に伴って、前バージョンでCKEditorを選んでいた場合はtinyMCEにする
			if($this->WYSIWYGConfig === "CKEditor") $this->WYSIWYGConfig = "tinyMCE";
			$_COOKIE["entry_text_editor"] = $this->WYSIWYGConfig;
		}

		if(count($labelIds)){
			foreach($labelIds as $labelId){
				if(in_array($labelId, $out)){
					$_COOKIE["entry_text_editor"] = "plain";
					break;
				}
			}
		}
	}

	private function _removeUnnecessaryFiles(){
		$dir = dirname(__FILE__) . "/";
		foreach(array("html", "php") as $ext){
			if(file_exists($dir . "config." . $ext)){
				@unlink($dir . "config." . $ext);
			}
		}
	}

	function getLabelConfig() {
		return $this->labelConfig;
	}
	function setLabelConfig($labelConfig) {
		$this->labelConfig = $labelConfig;
	}

	function getWYSIWYGConfig() {
		return $this->WYSIWYGConfig;
	}
	function setWYSIWYGConfig($WYSIWYGConfig) {
		$this->WYSIWYGConfig = $WYSIWYGConfig;
	}


	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		//管理側のみ
		if(defined("_SITE_ROOT_") || (defined("CMS_PREVIEW_MODE") && CMS_PREVIEW_MODE)) return;

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new SOYCMS_SwitchEditor();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj, "init"));
	}
}
