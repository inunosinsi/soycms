<?php

class GravatarEntryLogic extends SOY2LogicBase {

	const PLUGIN_ID = "gravatar";

	function __construct(){}

	function getEachAuthorEntries(){
		static $entries;
		if(is_null($entries)){
			$entries = array();

			$args = self::__getArgs();
			if(!isset($args[0])) return array();

			$alias = trim(htmlspecialchars($args[0], ENT_QUOTES, "UTF-8"));
			$account = SOY2Logic::createInstance("site_include.plugin.gravatar.logic.GravatarLogic")->getGravatarByAlias($alias);
			if(!strlen($account->getMailAddress())) return array();

			//検索結果ブロックプラグインのUTILクラスを利用する
			SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");
			$pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];

			//データベースから記事の取得件数指定
			$count = PluginBlockUtil::getLimitByPageId($pageId);

			//gravatarのメールアドレスに紐付いた記事を取得
			$entryDao = SOY2DAOFactory::create("cms.EntryDAO");
			$sql = "SELECT ent.* FROM Entry ent ".
							"INNER JOIN EntryAttribute attr ".
							"ON ent.id = attr.entry_id ".
							"WHERE attr.entry_field_id = :pluginId ".
							"AND attr.entry_value = :email ".
							"AND ent.openPeriodStart < :now ".
							"AND ent.openPeriodEnd > :now ".
							"AND ent.isPublished > " . Entry::ENTRY_NOTPUBLIC . " ".
							"ORDER BY ent.cdate DESC ";

			if(isset($count) && $count > 0){
				$sql .= "LIMIT " . $count;

				//ページャ
				if(isset($args[1]) && strpos($args[1], "page-") === 0){
					$pageNumber = (int)str_replace("page-", "", $args[1]);
					if($pageNumber > 0){
						$offset = $count * $pageNumber;
						$sql .= " OFFSET " . $offset;
					}
				}
			}

			try{
				$res = $entryDao->executeQuery($sql, array(":pluginId" => self::PLUGIN_ID, ":email" => $account->getMailAddress(), ":now" => time()));
			}catch(Exception $e){
				return array();
			}

			if(!count($res)) return array();

			foreach($res as $v){
				$entries[] = $entryDao->getObject($v);
			}
		}

		return $entries;
	}

	function getTotalEachAuthorEntries(){
		$args = self::__getArgs();
		if(!isset($args[0])) return 0;

		$alias = trim(htmlspecialchars($args[0], ENT_QUOTES, "UTF-8"));
		$account = SOY2Logic::createInstance("site_include.plugin.gravatar.logic.GravatarLogic")->getGravatarByAlias($alias);
		if(!strlen($account->getMailAddress())) return 0;

		$dao = new SOY2DAO();
		$sql = "SELECT COUNT(*) AS TOTAL FROM Entry ent ".
						"INNER JOIN EntryAttribute attr ".
						"ON ent.id = attr.entry_id ".
						"WHERE attr.entry_field_id = :pluginId ".
						"AND attr.entry_value = :email ".
						"AND ent.openPeriodStart < :now ".
						"AND ent.openPeriodEnd > :now ".
						"AND ent.isPublished = " . Entry::ENTRY_ACTIVE . "";

		try{
			$res = $dao->executeQuery($sql, array(":pluginId" => self::PLUGIN_ID, ":email" => $account->getMailAddress(), ":now" => time()));
		}catch(Exception $e){
			return 0;
		}

		return (isset($res[0]["TOTAL"])) ? (int)$res[0]["TOTAL"] : 0;
	}

	function getArgs(){
		return self::__getArgs();
	}

	private function __getArgs(){
		if(!isset($_SERVER["PATH_INFO"])) return array();
		//末尾にスラッシュがない場合はスラッシュを付ける
		$pathInfo = $_SERVER["PATH_INFO"];
		if(strrpos($pathInfo, "/") !== strlen($pathInfo) - 1){
			$pathInfo .= "/";
		}
		$argsRaw = rtrim(str_replace("/" . $_SERVER["SOYCMS_PAGE_URI"] . "/", "", $pathInfo), "/");
		return explode("/", $argsRaw);
	}
}
