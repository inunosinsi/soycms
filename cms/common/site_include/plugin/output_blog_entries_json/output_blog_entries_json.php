<?php
OutputBlogEntriesJsonPlugin::register();

class OutputBlogEntriesJsonPlugin{

	const PLUGIN_ID = "output_blog_entries_json";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name"=>"ブログ記事JSON出力プラグイン",
			"description"=>"ブログページのIDを指定するとJSON形式で記事一覧を出力する",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/article/4505",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.6"
		));
		

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this, "config_page"
			));

			CMSPlugin::setEvent('onSiteAccess', self::PLUGIN_ID, array($this, "onSiteAccess"));
		}
	}

	function onSiteAccess($obj){
		if(!isset($_SERVER["PATH_INFO"]) || is_bool(strpos($_SERVER["PATH_INFO"], ".json"))) return;

		preg_match('/\/(.*?)\.json/', $_SERVER["PATH_INFO"], $tmp);
		if(!isset($tmp[1]) || !is_numeric($tmp[1])) self::_output();

		$lim = (isset($_GET["limit"]) && is_numeric($_GET["limit"])) ? (int)$_GET["limit"] : -1;
		$offset = (isset($_GET["offset"]) && is_numeric($_GET["offset"])) ? (int)$_GET["offset"] : 0;
		$blogPage = soycms_get_page_object((int)$tmp[1]);
		if(!$blogPage instanceof BlogPage) self::_output();

		$labelId = $blogPage->getBlogLabelId();
		
		/** @ToDo sort */

		// 記事の合計を調べる
		$cnt = self::_calcTotal($labelId);
		if($cnt <= 0) self::_output();
		
		$isNext = ($lim >= 0 && $lim * ($offset + 1) < $cnt);

		// 安全装置
		if($lim < 0) $lim = 100;
		
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
		
		$arr = array();
		$arr["total"] = $cnt;
		$arr["is_next"] = ($isNext) ? 1 : 0;
		$arr["entries"] = array();
		foreach($res as $v){
			$values = $v;

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

			$arr["entries"][] = $values;
		}

		//JSONを出力
		self::_output($arr);
	}

	/**
	 * 合計金額を求める
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
	 * 合計金額を求めるためのSQLを発行する
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
		$now = time();
		$sql = "SELECT e.id, e.title, e.alias, e.cdate FROM Entry e ".
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
			if(file_exists(_SITE_ROOT_ . "/.plugin/CustomFieldAdvanced.active")) {
				SOY2::import("site_include.plugin.CustomFieldPluginAdvanced.CustomFieldPluginAdvanced", ".php");
				$c = CMSPlugin::loadPluginConfig(CustomFieldPluginAdvanced::PLUGIN_ID)->customFields;
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
		var_dump($arr);
		//header("Content-Type: application/json; charset=utf-8");
		//echo json_encode($arr);
		exit;
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
