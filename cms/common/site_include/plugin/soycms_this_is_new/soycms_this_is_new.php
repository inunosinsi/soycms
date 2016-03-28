<?php

SOYCMS_ThisIsNew_Plugin::register();

class SOYCMS_ThisIsNew_Plugin{

	const PLUGIN_ID = "SOYCMS_ThisIsNew";
	const CMS_ID = "this_is_new";
	public $daysToBeNew = 0;
	public $ignoreFutureEntry = 1;

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"新着マーク表示プラグイン",
			"description"=>"記事を表示するときに作成日時から一定期間の間だけ表示される部分を指定することができるようになります。新着画像の表示などに便利です。",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.1"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::setEvent('onEntryOutput',self::PLUGIN_ID,array($this,"display"));
		}
	}

	public static function register(){
		include_once(dirname(__FILE__)."/config_form.php");

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new SOYCMS_ThisIsNew_Plugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}

	public function getId(){
		return self::PLUGIN_ID;
	}

	function config_page($message){
		$form = SOY2HTMLFactory::createInstance("SOYCMS_ThisIsNew_Plugin_FormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public function display($arg){
		$visible = false;
		$now = time();

		$entryId = $arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];

		$entry = $this->getEntry($entryId);

		if(isset($entry)){
			$visible = ($entry->getCdate() + $this->getTime()) > $now ;
			if( $this->ignoreFutureEntry && ( $entry->getCdate() > $now ) ){
				$visible = false;
			}
		}

		$htmlObj->createAdd(SOYCMS_ThisIsNew_Plugin::CMS_ID,"HTMLModel",array(
			"visible" => $visible,
			"soy2prefix" => "cms",
		));

	}

	private function getTime(){
		static $time;
		if(!$time){
			if( strlen($this->daysToBeNew) ){
				$time = $this->daysToBeNew * 60*60*24;
			}else{
				$time = 0;
			}
		}

		return $time;
	}

	private function getEntry($entryId){
		try{
			$dao = SOY2DAOFactory::create("cms.EntryDAO");
			$entry = $dao->getById($entryId);
		}catch(Exception $e){
			return null;
		}
		return $entry;
	}

	public function setDaysToBeNew($value){
		$this->daysToBeNew = $value;
	}
	public function setIgnoreFutureEntry($value){
		$this->ignoreFutureEntry = $value;
	}

}
?>