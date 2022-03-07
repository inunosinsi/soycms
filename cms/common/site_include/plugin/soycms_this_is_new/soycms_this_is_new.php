<?php

SOYCMS_ThisIsNew_Plugin::register();

class SOYCMS_ThisIsNew_Plugin{

	const PLUGIN_ID = "SOYCMS_ThisIsNew";
	const CMS_ID = "this_is_new";
	public $daysToBeNew = 0;
	public $ignoreFutureEntry = 1;
	private $news = array();	//新着記事のID一覧

	public function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name"=>"新着マーク表示プラグイン",
			"description"=>"記事を表示するときに作成日時から一定期間の間だけ表示される部分を指定することができるようになります。新着画像の表示などに便利です。",
			"author"=>"株式会社Brassica",
			"url"=>"https://brassica.jp/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.3.1"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this, "config_page"
			));

			CMSPlugin::setEvent('onEntryListBeforeOutput', self::PLUGIN_ID, array($this, "onEntryListBeforeOutput"));
			CMSPlugin::setEvent('onEntryOutput', self::PLUGIN_ID, array($this, "onEntryOutput"));
		}
	}

	function getId(){
		return self::PLUGIN_ID;
	}

	function config_page($message){
		include_once(dirname(__FILE__)."/config_form.php");
		$form = SOY2HTMLFactory::createInstance("SOYCMS_ThisIsNew_Plugin_FormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * onEntryListBeforeOutput
	 */
	function onEntryListBeforeOutput($arg){
		$entries = &$arg["entries"];
		$entryIds = soycms_get_entry_id_by_entries($entries);
		if(!count($entryIds)) return;
		
		$t = time() - (int)$this->daysToBeNew * 60 * 60 * 24;
		$sql = "SELECT id FROM Entry WHERE cdate > " . $t . " AND id IN (" . implode(",", $entryIds) . ")";
		if(is_numeric($this->ignoreFutureEntry) && $this->ignoreFutureEntry == 1) $sql .= " AND cdate < " . time();

		try{
			$res = soycms_get_hash_table_dao("entry")->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return;
		
		foreach($res as $v){
			$this->news[] = (int)$v["id"];
		}
	}

	function onEntryOutput($arg){
		$entryId = (int)$arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];
		
		$htmlObj->addModel("this_is_new", array(
			"visible" => ($entryId > 0 && count($this->news) && is_numeric(array_search($entryId, $this->news))),
			"soy2prefix" => "cms",
		));

	}


	function setDaysToBeNew($value){
		$this->daysToBeNew = $value;
	}
	function setIgnoreFutureEntry($value){
		$this->ignoreFutureEntry = $value;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new SOYCMS_ThisIsNew_Plugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
