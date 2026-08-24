<?php
OSMPlugin::register();

class OSMPlugin{

	const PLUGIN_ID = "openstreetmap";
	const DEBUG = 0;	//1を指定すると配列で出力する
	const SOY_ID = "osm";

	private $homePosition = array("zoom" => 14, "lat" => "35.025272", "lng" => "135.762321");
	private $iconConfig = array();

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name"=>"OpenStreetMapプラグイン",
			"type" => Plugin::TYPE_EXTERNAL,
			"description"=>"ブログとOpenStreetMapを連携する",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/article/6578",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.2"
		));
		
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this, "config_page"
			));

			if(defined("_SITE_ROOT_")){	//公開側
				SOY2::import("site_include.plugin.openstreetmap.util.OSMUtil");
				CMSPlugin::setEvent('onEntryListBeforeOutput', self::PLUGIN_ID, array($this, "onEntryListBeforeOutput"));
				CMSPlugin::setEvent('onEntryOutput', self::PLUGIN_ID, array($this, "onEntryOutput"));
			}else{	//管理画面側
				CMSPlugin::setEvent('onEntryUpdate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCreate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryRemove', self::PLUGIN_ID, array($this, "onEntryRemove"));

				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_inBlog"));
			}
		}else{
			CMSPlugin::setEvent('onActive', self::PLUGIN_ID, array($this, "createTable"));
		}
	}

	/**
	 * onEntryListBeforeOutput
	 */
	function onEntryListBeforeOutput($arg){
		$entries = &$arg["entries"];
		$entryIds = soycms_get_entry_id_by_entries($entries);

		if(count($entryIds)) SOY2Logic::createInstance("site_include.plugin.openstreetmap.logic.OSMLogic")->registerOSMValuesByEntryIds($entryIds);
	}

	function onEntryOutput($arg){
		static $iconCfg;
		if(is_null($iconCfg)){
			$iconCfg = array();
			if(is_array($this->iconConfig) && count($this->iconConfig)){
				$iconCfg = OSMUtil::organizeIconConfig($this->iconConfig);
			}
		}
	
		$entryId = (int)$arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];

		if(defined("SOYCMS_BLOG_PAGE_MODE") && SOYCMS_BLOG_PAGE_MODE == "_entry_"){
			$osm = SOY2Logic::createInstance("site_include.plugin.openstreetmap.logic.OSMLogic")->getByEntryId($entryId);
		}else{
			$osm = OSMUtil::getOSMObjectByEntryId($entryId);
		}

		$htmlObj->addLabel("osm_lat", array(
			"soy2prefix" => self::SOY_ID,
			"text" => $osm->getLatitude()
		));

		$htmlObj->addLabel("osm_lng", array(
			"soy2prefix" => self::SOY_ID,
			"text" => $osm->getLongitude()
		));

		$htmlObj->addLabel("osm_label", array(
			"soy2prefix" => self::SOY_ID,
			"text" => $osm->getLabel()
		));

		$htmlObj->addLabel("osm_icon_hash", array(
			"soy2prefix" => self::SOY_ID,
			"text" => $osm->getIcon()
		));

		$icon = (strlen((string)$osm->getIcon()) && isset($iconCfg[$osm->getIcon()])) ? $iconCfg[$osm->getIcon()] : array();

		$htmlObj->addLabel("osm_icon_filename", array(
			"soy2prefix" => self::SOY_ID,
			"text" => (isset($icon["filename"])) ? $icon["filename"] : ""
		));

		$label = (isset($icon["label"])) ? $icon["label"] : "";
		$htmlObj->addLabel("osm_icon_label", array(
			"soy2prefix" => self::SOY_ID,
			"text" => $label
		));

		$path = (isset($icon["filename"]) && strlen((string)$icon["filename"])) ? str_replace($_SERVER["DOCUMENT_ROOT"], "", OSMUtil::iconDir()).$icon["filename"] : "";
		$htmlObj->addLabel("osm_icon_path", array(
			"soy2prefix" => self::SOY_ID,
			"text" => $path
		));

		$htmlObj->addImage("osm_icon", array(
			"soy2prefix" => self::SOY_ID,
			"src" => $path,
			"alt" => $label
		));
	}	

	/**
	 * 記事作成時、記事更新時
	 */
	function onEntryUpdate($arg){
		if(!isset($_POST["OpenStreetMap"])) return;

		$entry = $arg["entry"];
		SOY2Logic::createInstance("site_include.plugin.openstreetmap.logic.OSMLogic")->save((int)$entry->getId(), $_POST["OpenStreetMap"]);
	}

	/**
	 * 記事削除時
	 * @param array $args エントリーID
	 */
	function onEntryRemove($args){
		if(!count($args)) return true;
		
		return true;
	}

	/**
	 * 記事投稿画面
	 * @return string HTMLコード
	 */
	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : 0;
		SOY2::import("site_include.plugin.openstreetmap.component.OSMEntryFormComponent");
		return OSMEntryFormComponent::build($entryId, $this->iconConfig);
	}

	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : 0;
		SOY2::import("site_include.plugin.openstreetmap.component.OSMEntryFormComponent");
		return OSMEntryFormComponent::build($entryId, $this->iconConfig);
	}

	function config_page(){
		SOY2::import("site_include.plugin.openstreetmap.config.OSMConfigPage");
		$form = SOY2HTMLFactory::createInstance("OSMConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}
	/**
	 * プラグイン アクティブ 初回テーブル作成
	 */
	function createTable(){
		$dao = new SOY2DAO();

		try{
			$exist = $dao->executeQuery("SELECT * FROM OpenStreetMap", array());
			return;//テーブル作成済み
		}catch(Exception $e){

		}

		$sqls = preg_split('/CREATE TABLE/', file_get_contents(dirname(__FILE__) . "/sql/init_".SOYCMS_DB_TYPE.".sql"), -1, PREG_SPLIT_NO_EMPTY);

		foreach($sqls as $sql){
			try{
				$dao->executeQuery("CREATE TABLE ".$sql);
			}catch(Exception $e){
				//
			}
		}

		return;
	}

	function getHomePosition(){
		return $this->homePosition;
	}
	function setHomePosition(array $homePosition){
		$this->homePosition = $homePosition;
	}

	function getIconConfig(){
		return $this->iconConfig;
	}
	function setIconConfig(array $iconConfig){
		$this->iconConfig = $iconConfig;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new OSMPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
