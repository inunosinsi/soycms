<?php
FirstAndLastEntryLinkPlugin::registerPlugin();

class FirstAndLastEntryLinkPlugin {

	const PLUGIN_ID = "first_and_last_entry_link";

	const MODE_FIRST = 0;	//最初の記事
	const MODE_LAST = 1;	//最後の記事

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=> "最初と最後の記事リンク出力プラグイン",
			"description"=> "ブログページで最初と最後の記事のリンクを出力します",
			"author"=> "齋藤毅",
			"url"=> "https://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.5"
		));

		CMSPlugin::addPluginConfigPage($this->getId(),array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			//公開側
			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onPageOutput', self::PLUGIN_ID, array($this, "onPageOutput"));
			}
		}
	}

	function config_page($message){
		return "ブログページで最初の記事の&lt;a <strong>b_block:id=\"first_entry\"</strong>&gt;最初の記事&lt;/a&gt;と&lt;a <strong>b_block:id=\"last_entry\"</strong>&gt;最後の記事&lt;/a&gt;が使用可能になります。";
	}

	function onPageOutput($obj){
		//ブログページの時のみ動作します
		if(get_class($obj) != "CMSBlogPage") return;

		//現在開いているページに紐付いているラベルID
		$blogLabelId = self::_getBlogLabelId();
		if(is_null($blogLabelId)) return;

		$entryPageUri = "";

		foreach(array(self::MODE_FIRST, self::MODE_LAST) as $mode){
			$values = self::_getEntryByLabelId($blogLabelId, $mode);
			$prefix = ($mode == self::MODE_FIRST) ? "first" : "last";

			$obj->addLink($prefix . "_entry", array(
				"soy2prefix" => "b_block",
				"link" => $obj->getEntryPageURL(true) . rawurlencode($values["alias"])
			));
		}
	}

	private function _getBlogLabelId(){
		try{
			return (int)SOY2DAOFactory::create("cms.BlogPageDAO")->getById($_SERVER["SOYCMS_PAGE_ID"])->getBlogLabelId();
		}catch(Exception $e){
			return null;
		}
	}

	private function _getEntryByLabelId($labelId, $mode=self::MODE_FIRST){
		static $dao;
		if(is_null($dao)) $dao = new SOY2DAO();

		$sql = "SELECT ent.alias FROM EntryLabel lab ".
				"INNER JOIN Entry ent ".
				"ON lab.entry_id = ent.id ".
				"WHERE lab.label_id = :labelId ".
				"AND ent.isPublished = 1 ".
				"AND (ent.openPeriodEnd >= :now AND ent.openPeriodStart < :now) ";

		switch($mode){
			case self::MODE_FIRST:
				$sql .= "ORDER BY ent.cdate ASC ";
				break;
			case self::MODE_LAST:
				$sql .= "ORDER BY ent.cdate DESC ";
				break;
		}

		$sql .= "LIMIT 1";

		try{
			$res = $dao->executeQuery($sql, array(":labelId" => $labelId, ":now" => time()));
		}catch(Exception $e){
			$res = array();
		}

		return (isset($res[0])) ? $res[0] : array("alias" => null);
	}

	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new FirstAndLastEntryLinkPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj,"init"));
	}
}
