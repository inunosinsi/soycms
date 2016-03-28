<?php
/*
 * Created on 2009/05/16
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
SOYCMS_SwitchEditor::registerPlugin();

class SOYCMS_SwitchEditor{
	
	const PLUGIN_ID = "soycms_switch_editor";
	
	private $labelConfig = array();
	private $WYSIWYGConfig;
	
	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"エディタ切り替えプラグイン",
			"description"=>"ラベル毎にエディタを切り替えます。<br />選択できるWYSIWYGエディタは<ul>" .
					"<li>tinyMCE</li>" .
					"<li>CKEditor</li>" .
					"</ul>",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"2.0"
		));
		
		if(CMSPlugin::activeCheck($this->getId())){
			CMSPlugin::addPluginConfigPage($this->getId(),array(
				$this,"config_page"
			));
			
			CMSPlugin::setEvent("onSetupWYSIWTG",$this->getId(),array($this,"onSetupWYSIWTG"));
		}
	}
	
	function getId(){
		return SOYCMS_SwitchEditor::PLUGIN_ID;
	}
		
	function config_page(){
		
		//WYSIWYGの選択
		if(isset($_POST["WYSIWYGConfig"])){
			$this->setWYSIWYGConfig($_POST["WYSIWYGConfig"]);
		}
		
		//各ラベルの設定
		if(isset($_POST["labelConfig"])){
			
			$this->setLabelConfig($_POST["labelConfig"]);
			
			CMSPlugin::savePluginConfig($this->getId(),$this);
			
			CMSPlugin::redirectConfigPage();
		}
		
		$labels = SOY2DAOFactory::create("cms.LabelDAO")->get();
		$config = $this->getLabelConfig();
		
		$html = file_get_contents(dirname(__FILE__)."/config.html");
		$page = new HTMLPage();
		$obj = $page->create("config_page","HTMLTemplatePage",array(
			"arguments" => array("config_page",$html)
		));
		
		//WYSIWYG
		$obj->createAdd("editor_select", "HTMLSelect", array(
			"options" => self::getEditors(),
			"name" => "WYSIWYGConfig",
			"selected" => $this->getWYSIWYGConfig()
		));
		
		$obj->createAdd("labels", "LabelConfigList", array(
			"list" => $labels,
			"config" => $config
		));
		
		$obj->execute();
		return $obj->getObject();	

	}
	
	function onSetupWYSIWTG($args){
		$entryId = $args["id"];
		$labelIds = $args["labelIds"];
		if(!isset($labelIds) OR !is_array($labelIds)) $labelIds = array();
		
		$labels = SOY2DAOFactory::create("cms.EntryLabelDAO")->getByEntryId($entryId);
		foreach($labels as $label){
			$labelIds[] = $label->getLabelId();
		}
		
		$out = array();
		foreach($this->labelConfig as $labelId => $value){
			if($value){
				$out[] = $labelId;
			}
		}
		
		if(is_null($this->WYSIWYGConfig)){
			$_COOKIE["entry_text_editor"] = "tinyMCE";
		}else{
			//CKEditorの廃止に伴って、前バージョンでCKEditorを選んでいた場合はtinyMCEにする
			if($this->WYSIWYGConfig === "CKEditor") $this->WYSIWYGConfig = "tinyMCE";
			$_COOKIE["entry_text_editor"] = $this->WYSIWYGConfig;
		}
		
		foreach($labelIds as $labelId){
			if(in_array($labelId,$out)){
				$_COOKIE["entry_text_editor"] = "plain";
				break;
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
		if(defined("_SITE_ROOT_") || defined("CMS_PREVIEW_MODE"))return;
		
		$obj = CMSPlugin::loadPluginConfig(SOYCMS_SwitchEditor::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new SOYCMS_SwitchEditor();
		}
		
		CMSPlugin::addPlugin(SOYCMS_SwitchEditor::PLUGIN_ID,array($obj,"init"));
	}

	public static function getEditors(){
		return array(
			"tinyMCE" => "tinyMCE",
//			"CKEditor" => "CKEditor",
			"plain" => "WYSIWYGを無効",
		);
	}
}

class LabelConfigList extends HTMLList{
	
	private $config;
	
	function populateItem($entity, $key){
		
		$iconPath = (!is_null($entity->getIcon())) ? $entity->getIcon() : "default.gif";
		
		//icon
		$this->createAdd("icon", "HTMLImage", array(
			"src" => CMS_LABEL_ICON_DIRECTORY_URL . $iconPath,
			"alt" => $entity->getCaption()
		));
		
		//名称
		$this->createAdd("caption", "HTMLLabel", array(
			"text" => $entity->getCaption()
		));
		
		if(array_key_exists($entity->getId(), $this->config)){
			$conf = $this->config[$entity->getId()];
		}else{
			$conf = 1;//デフォルト
		}
		
		//セレクトボックス
		$this->createAdd("config", "HTMLSelect", array(
			"options" => self::getConfig(),
			"name" => "labelConfig[". $entity->getId(). "]",
			"selected" => $conf,
			"indexOrder" => true
		));
	}
	
	public static function getConfig(){
		return array(
			"0" => "有効にする",
			"1" => "無効にする"
		);
	}
	
	function setConfig($config){
		$this->config = $config;
	}
	
}
?>