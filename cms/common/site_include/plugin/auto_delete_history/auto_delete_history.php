<?php
AutoDeleteHistoryPlugin::register();
class AutoDeleteHistoryPlugin{

	const PLUGIN_ID = "auto_delete_history";

	private $isEntryDelete = 0;	//記事履歴の自動削除
	private $entryCdate = 365;	//○日前の履歴はすべて自動削除
	private $entryCount = 10;	//記事に紐付いた履歴は○件まで保持

	private $isTemplateDelete = 0;	//テンプレートの変更履歴の自動削除
	private $templateCdate = 365;	//○日前の履歴はすべて自動削除
	private $templateCount = 10;	//テンプレートに紐付いた履歴は○件まで保持

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name" => "履歴自動削除プラグイン",
			"type" => Plugin::TYPE_OPTIMIZE,
			"description" => "記事とテンプレートの変更履歴を自動で削除する",
			"author"=>"齋藤毅",
			"url"=>"http://saitodev.co/",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.5"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			//設定画面
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this, "config_page"
			));

			//管理画面
			if(!defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onAdminTop', self::PLUGIN_ID, array($this, "onAdminTop"));
			}
		}
	}

	function onAdminTop(){
		//管理画面トップで何も表示しないが、管理画面トップで履歴の自動削除の仕組みを設けたい
		SOY2::import("site_include.plugin.auto_delete_history.util.AutoDeleteHistoryUtil");

		//記事の編集履歴
		if($this->isEntryDelete == AutoDeleteHistoryUtil::ACTIVE){
			$logic = SOY2Logic::createInstance("site_include.plugin.auto_delete_history.logic.EntryHistoryDeleteLogic");

			//何も変更していない履歴を削除
			$logic->deleteNoChangeHistory();

			//日による履歴の自動実行の方が高速で行われるので先に行う
			if((int)$this->entryCdate > 0){
				$logic->deleteHistory((int)$this->entryCdate);
			}

			//記事毎の履歴の保持設定
			if((int)$this->entryCount > 0){
				$logic->deleteHistoryEachEntryIds((int)$this->entryCount);
			}
		}

		//テンプレートのへ高履歴
		if($this->isTemplateDelete == AutoDeleteHistoryUtil::ACTIVE){
			$logic = SOY2Logic::createInstance("site_include.plugin.auto_delete_history.logic.TemplateHistoryDeleteLogic");

			//日による履歴の自動実行の方が高速で行われるので先に行う
			if((int)$this->templateCdate > 0){
				$logic->deleteHistory($this->templateCdate);
			}

			//テンプレート毎の履歴の保持設定
			if((int)$this->templateCount > 0){
				$logic->deleteHistoryEachPageIds($this->templateCount);
			}
		}

		return array("title" => null, "content" => null);
	}

	function config_page(){
		SOY2::import("site_include.plugin.auto_delete_history.config.AutoDeleteHistoryConfigPage");
		$form = SOY2HTMLFactory::createInstance("AutoDeleteHistoryConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function getIsEntryDelete(){
		return $this->isEntryDelete;
	}
	function setIsEntryDelete($isEntryDelete){
		$this->isEntryDelete = $isEntryDelete;
	}

	function getEntryCdate(){
		return $this->entryCdate;
	}
	function setEntryCdate($entryCdate){
		$this->entryCdate = $entryCdate;
	}

	function getEntryCount(){
		return $this->entryCount;
	}
	function setEntryCount($entryCount){
		$this->entryCount = $entryCount;
	}

	function getIsTemplateDelete(){
		return $this->isTemplateDelete;
	}
	function setIsTemplateDelete($isTemplateDelete){
		$this->isTemplateDelete = $isTemplateDelete;
	}

	function getTemplateCdate(){
		return $this->templateCdate;
	}
	function setTemplateCdate($templateCdate){
		$this->templateCdate = $templateCdate;
	}

	function getTemplateCount(){
		return $this->templateCount;
	}
	function setTemplateCount($templateCount){
		$this->templateCount = $templateCount;
	}

	/**
	 * プラグインの登録
	 */
	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(AutoDeleteHistoryPlugin::PLUGIN_ID);
		if(is_null($obj)) $obj = new AutoDeleteHistoryPlugin();
		CMSPlugin::addPlugin(AutoDeleteHistoryPlugin::PLUGIN_ID, array($obj, "init"));
	}
}
