<?php
OutputBlogEntriesJsonPlugin::register();

class OutputBlogEntriesJsonPlugin{

	const PLUGIN_ID = "output_blog_entries_json";
	const DEBUG = 0;	//1を指定すると配列で出力する

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name"=>"ブログ記事JSON出力プラグイン",
			"type" => Plugin::TYPE_PAGE,
			"description"=>"ブログページのIDを指定するとJSON形式で記事一覧を出力する",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/article/4505",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"1.1"
		));
		
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this, "config_page"
			));

			if(defined("_SITE_ROOT_")){	//公開側
				//JSONの出力
				CMSPlugin::setEvent('onSiteAccess', self::PLUGIN_ID, array($this, "onSiteAccess"));
			}else{	//管理画面側
				//記事更新時にページャ用のデータベース oje.dbの削除 cms:module="parts.json_entries_multi_sites"で使用するデータベース
				CMSPlugin::setEvent('onEntryUpdate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCreate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCopy', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryRemove', self::PLUGIN_ID, array($this, "onEntryUpdate"));
			}
		}
	}

	function onSiteAccess($obj){
		if(!isset($_SERVER["PATH_INFO"]) || is_bool(strpos($_SERVER["PATH_INFO"], ".json"))) return;

		preg_match('/\/(.*?)\.json/', $_SERVER["PATH_INFO"], $tmp);
		if(!isset($tmp[1]) || !is_numeric($tmp[1])) return;

		$lim = (isset($_GET["limit"]) && is_numeric($_GET["limit"])) ? (int)$_GET["limit"] : -1;
		$offset = (isset($_GET["offset"]) && is_numeric($_GET["offset"])) ? (int)$_GET["offset"] : 0;
		$isRemoveLimit = (isset($_GET["remove_limit"]) && (int)$_GET["remove_limit"] === 1);
		$blogPage = soycms_get_page_object((int)$tmp[1]);
		if(!$blogPage instanceof BlogPage) self::_output();
		
		// 以後の各メソットでEntryクラスを利用する
		if(!class_exists("Entry")) SOY2::import("domain.cms.Entry");

		$labelId = $blogPage->getBlogLabelId();
		
		/** @ToDo sort */

		// 記事の合計を調べる
		$cnt = self::_calcTotal($labelId);
		if($cnt <= 0) self::_output();
		
		$isNext = ($lim >= 0 && $lim * ($offset + 1) < $cnt);

		// 安全装置
		if(!$isRemoveLimit && $lim < 0) $lim = 100;
		
		try{
			$res = self::_dao()->executeQuery(self::_buildSql($lim, $offset), array(":labelId" => $labelId));
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) self::_output();

		$entryIds = array();
		foreach($res as $v){
			$entryIds[] = (int)$v["id"];
		}

		//カスタムフィールド
		$customList = array();
		$customfieldIds = (isset($_GET["customfield"])) ? self::_customfieldIds() : array();
		if(count($customfieldIds)){
			$sql = "SELECT * FROM EntryAttribute ".
					"WHERE entry_id IN (" . implode(",", $entryIds) . ") ".
					"AND entry_field_id IN ('" . implode("','", $customfieldIds) . "') ";
			try{
				$r = self::_dao()->executeQuery($sql);
			}catch(Exception $e){
				$r = array();
			}
			
			if(count($r)){
				foreach($r as $v){
					$entryId = (int)$v["entry_id"];
					if(!isset($customList[$entryId])) $customList[$entryId] = array();
					$customList[$entryId][$v["entry_field_id"]] = (isset($v["entry_value"])) ? $v["entry_value"] : "";
				}
			}
		}

		//サムネイル
		$thumList = array();
		if(isset($_GET["thumbnail"])){
			$sql = "SELECT entry_id, entry_field_id, entry_value FROM EntryAttribute ".
					"WHERE entry_id IN (" . implode(",", $entryIds) . ") ".
					"AND entry_field_id LIKE 'soycms_thumbnail_plugin_%'";
			try{
				$r = self::_dao()->executeQuery($sql);
			}catch(Exception $e){
				$r = array();
			}
			
			$arr = array();
			if(count($r)){
				foreach($r as $v){
					if($v["entry_field_id"] == "soycms_thumbnail_plugin_config") continue;
					$entryId = (int)$v["entry_id"];
					if(!isset($arr[$entryId])) $arr[$entryId] = array();
					$fId = str_replace("soycms_thumbnail_plugin_", "", $v["entry_field_id"]);
					$ffId = ($fId == "resize") ? "thumbnail" : $fId;
					$arr[$entryId][$ffId] = (strlen($v["entry_value"])) ? str_replace("/".soycms_get_site_id_by_frontcontroller()."/", "", soycms_get_site_url_by_frontcontroller(true)) . $v["entry_value"] : "";
				}
			}
			
			foreach($entryIds as $entryId){
				$thumList[$entryId] = (isset($arr[$entryId])) ? $arr[$entryId] : array("resize" => "", "trimming" => "", "upload" => "");
			}
		}
		
		$arr = array();
		$arr["total"] = $cnt;
		$arr["is_next"] = ($isNext) ? 1 : 0;
		$arr["entries"] = array();
		foreach($res as $v){
			$values = $v;

			//記事のURL
			if(isset($_GET["is_url"]) && (int)$_GET["is_url"] === 1){
				$values["url"] = self::_buildBlogPageEntryUri($blogPage) . rawurlencode($values["alias"]);
			}

			//本文と追記の文字数を決める
			foreach(array("content", "more") as $col){
				if(!isset($_GET[$col]) || !isset($values[$col]) || !is_numeric($_GET[$col])) continue;
				$values[$col] = trim(strip_tags($values[$col]));
				if(mb_strlen($values[$col]) <= $_GET[$col]) continue;
				$values[$col] = mb_substr($values[$col], 0, $_GET[$col]);
			}
			

			//カスタムフィールドの値があるか？
			if(count($customList) && isset($customList[(int)$v["id"]]) && count($customList[(int)$v["id"]])){
				foreach($customList[(int)$v["id"]] as $fieldId => $fieldValue){
					$values[$fieldId] = $fieldValue;
				}			
			}

			// 値のないフィールドを空文字で埋める
			if(count($customfieldIds)){
				foreach($customfieldIds as $fieldId){
					if(!isset($values[$fieldId])) $values[$fieldId] = "";
				}
			}

			// サムネイルプラグイン
			if(count($thumList)){
				$values["thumbnail"] = $thumList[$values["id"]];
			}
			
			$arr["entries"][] = $values;
		}

		//JSONを出力
		self::_output($arr);
	}

	/**
	 * 合計を求める
	 * @param int
	 * @return int
	 */
	private function _calcTotal(int $labelId){
		try{
			$res = self::_dao()->executeQuery(self::_buildTotalSql($labelId), array(":labelId" => $labelId));
		}catch(Exception $e){
			$res = array();
		}
		return (isset($res[0]["CNT"])) ? (int)$res[0]["CNT"] : 0;
	}

	/**
	 * 合計を求めるためのSQLを発行する
	 * @param  int
	 * @return string
	 */
	private function _buildTotalSql(int $labelId){
		$now = true;
		$sql = "SELECT COUNT(e.id) AS CNT FROM Entry e ".
			"INNER JOIN EntryLabel l ".
			"ON e.id = l.entry_id ".
			"WHERE l.label_id = :labelId ".
			"AND e.openPeriodStart <= " . $now . " ".
			"AND e.openPeriodEnd >= " . $now . " ".
			"AND e.isPublished = " . Entry::ENTRY_ACTIVE . " ";
	
		$isCustomfields = self::_isCustomfieldIds();
		if(count($isCustomfields)){
			foreach($isCustomfields as $fieldId){
				$sql .= "AND e.id IN (SELECT entry_id FROM EntryAttribute WHERE entry_field_id = '" . $fieldId . "' AND entry_value IS NOT NULL AND entry_value != '') ";
			}
		}
		$sql .= "ORDER BY e.cdate DESC ";
		return $sql;
	}

	/**
	 * @param int, int
	 * @return string
	 */
	private function _buildSql(int $lim, int $offset){
		$cols = "e.id, e.title, e.alias, e.cdate, e.udate";
		if(isset($_GET["content"])) $cols .= ",e.content";
		if(isset($_GET["more"])) $cols .= ",e.more";
		
		$now = time();
		$sql = "SELECT " . $cols . " FROM Entry e ".
				"INNER JOIN EntryLabel l ".
				"ON e.id = l.entry_id ".
				"WHERE l.label_id = :labelId ".
				"AND e.openPeriodStart <= " . $now . " ".
				"AND e.openPeriodEnd >= " . $now . " ".
				"AND e.isPublished = " . Entry::ENTRY_ACTIVE . " ";
		
		$isCustomfields = self::_isCustomfieldIds();
		if(count($isCustomfields)){
			foreach($isCustomfields as $fieldId){
				$sql .= "AND e.id IN (SELECT entry_id FROM EntryAttribute WHERE entry_field_id = '" . $fieldId . "' AND entry_value IS NOT NULL AND entry_value != '') ";
			}
		}
		$sql .= "ORDER BY e.cdate DESC ";
		if($lim >= 0){
			$sql .= "LIMIT " . $lim . " ";
			if($offset > 0){
				$sql .= "OFFSET " . ($lim * $offset);
			}
		}
		return $sql;
	}


	/**
	 * @return array
	 */
	private function _isCustomfieldIds(){
		static $fieldIds;
		if(is_null($fieldIds)){
			$fieldIds = array();
			if(isset($_GET["is_customfield"])){
				if(is_string($_GET["is_customfield"])){
					$fieldIds = array(trim($_GET["is_customfield"]));
				}else if(is_array($_GET["is_customfield"])){
					$fieldIds = $_GET["is_customfield"];
				}
				$fieldIds = (count($fieldIds)) ? self::_filterIsCustomfieldId($fieldIds) : array();
			}
		}
		return $fieldIds;
	}

	/**
	 * @return array
	 */
	private function _customfieldIds(){
		$fieldIds = array();
		if(isset($_GET["customfield"])){
			if(is_string($_GET["customfield"])){
				$fieldIds = array(trim($_GET["customfield"]));
			}else if(is_array($_GET["customfield"])){
				$fieldIds = $_GET["customfield"];
			}
		}
		return (count($fieldIds)) ? self::_filterIsCustomfieldId($fieldIds) : array();
	}

	/**
	 * 存在するフィールドIDであるか？
	 * @param array
	 * @return array
	 */
	private function _filterIsCustomfieldId(array $fieldIds){
		static $c;
		if(is_null($c)){
			$c = array();
			if(CMSPlugin::activeCheck("CustomFieldAdvanced")) {
				SOY2::import("site_include.plugin.self.self", ".php");
				$c = CMSPlugin::loadPluginConfig(self::PLUGIN_ID)->customFields;
			}
		}

		$keys = array_keys($c);
		$tmps = array();
		foreach($fieldIds as $fieldId){
			if(is_bool(array_search($fieldId, $keys))) continue;
			$tmps[] = $fieldId;
		}
		return $tmps;
	}

	private function _output(array $arr=array()){
		if(!isset($arr["total"])) $arr["total"] = 0;
		if(!isset($arr["is_next"])) $arr["is_next"] = 0;
		if(!isset($arr["entries"])) $arr["entries"] = array();
		if(self::DEBUG){
			var_dump($arr);
		}else{
			header("Content-Type: application/json; charset=utf-8");
			echo json_encode($arr);
		}
		exit;
	}

	/**
	 * @param BlogPage
	 * @return string
	 */
	private function _buildBlogPageEntryUri(BlogPage $blogPage){
		static $u;
		if(is_string($u)) return $u;

		$u = soycms_get_site_publish_url_by_frontcontroller(true);
		
		$uri = $blogPage->getUri();
		if(is_string($uri) && strlen($uri)) $u .= $uri . "/";

		$uri = $blogPage->getEntryPageUri();
		if(is_string($uri) && strlen($uri)) $u .= $uri . "/";

		return $u;
	}

	function onEntryUpdate($args){
		//すべてのサイトディレクトリを調べる
		$old = CMSUtil::switchDsn();

		$sites = SOY2DAOFactory::create("admin.SiteDAO")->get();
		foreach($sites as $site){
			$dbPath = $site->getPath() . ".cache/oje.db";
			if(!file_exists($dbPath)) continue;
			unlink($dbPath);
		}
		
		CMSUtil::resetDsn($old);
	}

	private function _dao(){
		static $d;
		if(is_null($d)) $d = new SOY2DAO();
		return $d;
	}

	function config_page(){
		SOY2::import("site_include.plugin.output_blog_entries_json.config.OutputJsonConfigPage");
		$form = SOY2HTMLFactory::createInstance("OutputJsonConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new OutputBlogEntriesJsonPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
