<?php

class EntryImportUtil{

	/**
	 * 呼び出すサイトとブログの設定。
	 * @return array(["siteId"],["blogId"],["count"])
	 */
	public static function getConfig(){
		return SOYShop_DataSets::get("parts.entry.import", array(
			"siteId" => null,
			"blogId" => null,
			"count" => 5
		));
	}

	public static function switchSiteDsn($dsn){
		$old = array();

		$old["root"] = SOY2::RootDir();
		$old["page"] = SOY2HTMLConfig::PageDir();
		$old["dao"] = SOY2DAOConfig::DaoDir();
		$old["entity"] = SOY2DAOConfig::EntityDir();
		$old["dsn"] = SOY2DAOConfig::Dsn();
		$old["user"] = SOY2DAOConfig::User();
		$old["pass"] = SOY2DAOConfig::Pass();

		$rooDir = str_replace("/soyshop/webapp/src/", "/common/", $old["root"]);
		$pageDir = str_replace("/soyshop/","/soycms/" , $old["page"]);
		$daoDir = str_replace("/soyshop/webapp/src/", "/common/", $old["dao"]);
		$entityDir = str_replace("/soyshop/webapp/src/", "/common/", $old["entity"]);

		SOY2::RootDir($rooDir);
		SOY2HTMLConfig::PageDir($pageDir);
		SOY2DAOConfig::DaoDir($daoDir);
		SOY2DAOConfig::EntityDir($entityDir);

		SOY2::import("util.SOYShopUtil");

		//DBファイルの在処の有無でMySQL
		$dbFilePath = str_replace("/soyshop/", "/common/config/db/mysql.php", SOYSHOP_ROOT);
		$isMySQL = file_exists($dbFilePath);

		//MySQL版
		if($isMySQL){
			include_once($dbFilePath);
			$user = (defined("ADMIN_DB_USER"))? ADMIN_DB_USER : null;
			$pass = (defined("ADMIN_DB_PASS"))? ADMIN_DB_PASS : null;

		//SQLite版
		}else{
			$user = null;
			$pass = null;
		}

		SOY2DAOConfig::Dsn($dsn);
		SOY2DAOConfig::user($user);
		SOY2DAOConfig::pass($pass);

		return $old;
	}

	public static function resetSiteDsn($old){
		SOY2::RootDir($old["root"]);
		SOY2HTMLConfig::PageDir($old["page"]);
		SOY2DAOConfig::DaoDir($old["dao"]);
		SOY2DAOConfig::EntityDir($old["entity"]);
		SOY2DAOConfig::Dsn($old["dsn"]);
		SOY2DAOConfig::user($old["user"]);
		SOY2DAOConfig::pass($old["pass"]);
	}

	public static function getSite($siteId){
		$dao = SOY2DAOFactory::create("admin.SiteDAO");
		try{
			return $dao->getBySiteId($siteId);
		}catch(Exception $e){
			return new Site();
		}
	}

	public static function getBlogEntiryList($blogId, $count = 5){
		if(!isset($blogId)) return array();

		//DNSはSOY CMSサイトに切り替わっている前提
		SOY2::import("util.CMSUtil");

		//ブログの取得
		$blogDao = SOY2DAOFactory::create("cms.BlogPageDAO");
		try{
			$blog = $blogDao->getById($blogId);
		}catch(Exception $e){
			$blog = new BlogPage();
		}

		//特定ラベルの記事を取得します
		$sql = new SOY2DAO_Query();

		$binds = array(
			":label_id" => $blog->getBlogLabelId(),
			":now" => time()
		);

		$sql->prefix = "select";
		$sql->table = "Entry INNER JOIN EntryLabel ON (Entry.id = EntryLabel.entry_id)";
		$sql->distinct = true;
		$sql->order = "cdate desc";
		$sql->sql = "id,id,alias,title,content,more,cdate,display_order ";
		$sql->where = "label_id = :label_id ";
		$sql->where .= "AND Entry.isPublished = 1 ";
		$sql->where .= "AND (openPeriodEnd >= :now AND openPeriodStart < :now) ";

		$dao = SOY2DAOFactory::create("cms.EntryDAO");
		$dao->setLimit($count);
		try{
			$res = $dao->executeQuery($sql, $binds);
		}catch(Exception $e){
			return array();
		}

		$entries = array();
		foreach($res as $key => $row){

			if(!isset($row)) continue;

			$obj = $dao->getObject($row);//SOY2::cast("Entry",(object)$row);
			$entries[] = $obj;

		}

		return $entries;
	}

	public static function getBlogUrl($blogId, $siteUrl){

		if(!isset($blogId)) return null;

		//ブログの取得
		$blogDao = SOY2DAOFactory::create("cms.BlogPageDAO");
		try{
			$blog = $blogDao->getById($blogId);
		}catch(Exception $e){
			return null;
		}

		//ブログ記事のURL
		$blogUri = $blog->getUri();
		if(!strlen($blogUri) > 0){
			return $siteUrl . $blog->getEntryPageUri() . "/";
		}else{
			return $siteUrl . $blogUri. "/" . $blog->getEntryPageUri() . "/";
		}
	}

	public static function getCustomfieldConfig($siteId){
		$fname = $_SERVER["DOCUMENT_ROOT"] . $siteId . '/.plugin/CustomFieldAdvanced.config';
		if(file_exists($fname)){
			include_once(dirname(dirname(__FILE__)) . "/class/CustomFieldPluginAdvanced.class.php");
			include_once(dirname(dirname(__FILE__)) . "/class/CustomField.class.php");
			$obj = unserialize(file_get_contents($fname));
			return $obj->customFields;
		}else{
			return array();
		}
	}

	public static function getSOYCMSThisIsNewConfig($siteId){
		$fname = $_SERVER["DOCUMENT_ROOT"] . $siteId . '/.plugin/SOYCMS_ThisIsNew.config';
		if(file_exists($fname)){
			preg_match('/"daysToBeNew";s:[0-9]:"(.*?)";/', file_get_contents($fname), $tmp);
			if(isset($tmp[1]) && is_numeric($tmp[1])) return (int)$tmp[1];
			$cnf = SOYShop_DataSets::get("common_this_is_new", array("date" => 7));
			return (int)$cnf["date"];
		}
		return 0;
	}

	//サムネイルプラグインが有効であるか？
	public static function getThumbnailPluginConfig($siteId){
		$fname = $_SERVER["DOCUMENT_ROOT"] . $siteId . '/.plugin/soycms_thumbnail.active';
		if(file_exists($fname)){
			$cnffile = $_SERVER["DOCUMENT_ROOT"] . $siteId . '/.plugin/soycms_thumbnail.config';
			if(file_exists($cnffile)){
				include_once(dirname(dirname(__FILE__)) . "/class/SOYCMSThumbnailPlugin.class.php");
				$cnf = soy2_unserialize(file_get_contents($cnffile));
				return $cnf->getNoThumbnailPath();
			}else{
				return "";
			}
		}else{
			return null;
		}
	}
}
