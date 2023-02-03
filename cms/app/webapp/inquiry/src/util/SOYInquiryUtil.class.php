<?php

class SOYInquiryUtil{

	const SOYINQUIRY_SESSION_ID = "soyinquiry_";
	const SOYINQUIRY_REPLACEMENT_KEY_PREFIX = "replace_";

	public static function switchConfig(){

		$old["root"] = SOY2::RootDir();
		$old["dao"] = SOY2DAOConfig::DaoDir();
		$old["entity"] = SOY2DAOConfig::EntityDir();
		$old["dsn"] = SOY2DAOConfig::Dsn();
		$old["user"] = SOY2DAOConfig::user();
		$old["pass"] = SOY2DAOConfig::pass();

		//SQLite
		if(SOYCMS_DB_TYPE == "sqlite"){
			$dsn = "sqlite:" . CMS_COMMON . "db/shop.db";
		//MySQL版
		}else{
			//サイト側にSOY Inquiryのデータベースを持つ場合
			if(defined("SOYINQUIRY_USE_SITE_DB") && SOYINQUIRY_USE_SITE_DB){
				$dsn = ADMIN_DB_DSN;
			//通常版
			}else{
				$dsn = $old["dsn"];
			}
		}

		$rootDir = str_replace("/inquiry/", "/shop/", $old["root"]);
		$entityDir = str_replace("/inquiry/", "/shop/", $old["entity"]);

		SOY2::RootDir($rootDir);
		SOY2DAOConfig::DaoDir($entityDir);
		SOY2DAOConfig::EntityDir($entityDir);
		SOY2DAOConfig::Dsn($dsn);
		SOY2DAOConfig::user($old["user"]);
		SOY2DAOConfig::pass($old["pass"]);

		return $old;
	}

	/**
	 * @param string
	 * @return array
	 */
	public static function switchSOYShopConfig(string $shopId="shop"){
		$old["root"] = SOY2::RootDir();
		$old["dao"] = SOY2DAOConfig::DaoDir();
		$old["entity"] = SOY2DAOConfig::EntityDir();
		$old["dsn"] = SOY2DAOConfig::Dsn();
		$old["user"] = SOY2DAOConfig::user();
		$old["pass"] = SOY2DAOConfig::pass();

		if(!strlen($shopId)) $shopId = "shop";
		$soyshopWebapp = dirname(CMS_COMMON) . "/soyshop/webapp/";
		if(!defined("SOYSHOP_SITE_DIRECTORY")) {
			if(file_exists($soyshopWebapp."conf/shop/" . $shopId . ".conf.php")){
				include_once($soyshopWebapp."conf/shop/" . $shopId . ".conf.php");
			}else{
				//なんでも良いからconf.phpファイルを探す
				$cnfDir = $soyshopWebapp."conf/shop/";
				if(is_dir($cnfDir)){
					if($dh = opendir($cnfDir)){
						while(($file = readdir($dh)) !== false && strpos($file, ".conf.php") && !strpos($file, ".admin.")) {
							include_once($cnfDir . $file);
							break;
						}
						closedir($dh);
					}
				}
			}
		}

		$entityDir = $soyshopWebapp . "src/domain/";

		SOY2::RootDir($soyshopWebapp . "/src/");
		SOY2DAOConfig::DaoDir($entityDir);
		SOY2DAOConfig::EntityDir($entityDir);
		SOY2DAOConfig::Dsn(SOYSHOP_SITE_DSN);
		SOY2DAOConfig::user(SOYSHOP_SITE_USER);
		SOY2DAOConfig::pass(SOYSHOP_SITE_PASS);

		return $old;
	}

	public static function resetConfig(array $old){

		SOY2::RootDir($old["root"]);
		SOY2DAOConfig::DaoDir($old["dao"]);
		SOY2DAOConfig::EntityDir($old["entity"]);
		SOY2DAOConfig::Dsn($old["dsn"]);
		SOY2DAOConfig::user($old["user"]);
		SOY2DAOConfig::pass($old["pass"]);
	}

	/**
	 * @param int, array
	 */
	public static function saveReplacementStringsConfig(int $formId, array $cnfs){
		SOY2::import("domain.SOYInquiry_DataSets");
		if(count($cnfs)){
			SOYInquiry_DataSets::put(self::SOYINQUIRY_REPLACEMENT_KEY_PREFIX.$formId, $cnfs);
		}else{
			SOYInquiry_DataSets::delete(self::SOYINQUIRY_REPLACEMENT_KEY_PREFIX.$formId);
		}
	}

	/**
	 * @param int
	 * @return array
	 */
	public static function getReplacementStringsConfig(int $formId){
		SOY2::import("domain.SOYInquiry_DataSets");
		return SOYInquiry_DataSets::get(self::SOYINQUIRY_REPLACEMENT_KEY_PREFIX.$formId, array());
	}

	/**
	 * SOY Shopがインストールされているか？
	 * @return boolen
	 */
	public static function checkSOYShopInstall(){
		return (file_exists(dirname(CMS_COMMON) . "/soyshop/"));
	}

	public static function getSOYShopSiteId(){
		if(!defined("SOYSHOP_SITE_ID")){
			$old = self::switchConfig();

			$siteDao = SOY2DAOFactory::create("SOYShop_SiteDAO");
			try{
				$site = $siteDao->getById(SOYINQUERY_SOYSHOP_CONNECT_SITE_ID);
			}catch(Exception $e){
				$site = new SOYShop_Site();
			}
			define("SOYSHOP_SITE_ID", $site->getSiteId());

			self::resetConfig($old);
		}

		return SOYSHOP_SITE_ID;
	}

	/** tr_propertyが使用可能なフォームを選択しているか？ **/
	public static function checkUsabledTrProperty(string $theme){
		static $isTrProp;
		if(isset($isTrProp) && is_bool($isTrProp)) return $isTrProp;
		$dir = self::_getTemplateDir($theme);
		if(!file_exists($dir . "form.php") || !file_exists($dir . "confirm.php")){
			$isTrProp = false;
			return $isTrProp;
		}

		$code = file_get_contents($dir . "form.php");
		if(strpos($code, "getTrProperty") === false){
			$isTrProp = false;
			return $isTrProp;
		}

		$code = file_get_contents($dir . "confirm.php");
		if(strpos($code, "getTrProperty") === false){
			$isTrProp = false;
			return $isTrProp;
		}

		$isTrProp = true;
		return $isTrProp;
	}

	public static function getTemplateDir(string $theme){
		return self::_getTemplateDir($theme);
	}

	private static function _getTemplateDir(string $theme){
		$dir = SOY2::RootDir() . "template/" . $theme . "/";
		if(file_exists($dir)) return $dir;
		return SOY2::RootDir() . "template/default/";
	}

	/**
	 * フォームタグの末尾にsoy2_check_tokenを挿入
	 * @param string
	 * @return string
	 */
	public static function insertSoy2CheckToken(string $html){
		$lines = explode("\n", $html);
		$n = count($lines);	//行数
		for($i = 0; $i < $n; $i++){
			if(!strlen(trim($lines[$i]))) continue;
			if(is_bool(stripos($lines[$i], "form"))) continue;
			
			preg_match('/<form.*?>/i', $lines[$i], $tmp);
			if(count($tmp)) {
				$lines[$i] .= "<input type=\"hidden\" name=\"soy2_token\" value=\"".soy2_get_token()."\">";
				break;
			}

			//念の為に</form>の閉じタグも探しておく
			preg_match('/<\/form.*?>/i', $lines[$i], $tmp);
			if(count($tmp)) {
				$lines[$i] = "<input type=\"hidden\" name=\"soy2_token\" value=\"".soy2_get_token()."\">\n".$lines[$i];
				break;
			}
		}

		return implode("\n", $lines);
	}

	/** 管理画面確認用の便利な関数 **/
	const INQUIRY_LABEL_LENGTH = 20;

	public static function shapeInquiryContent(string $txt){
		$lines = explode("\n", $txt);
		if(!count($lines)) return "";

		//とても長い項目名を探す
		$mostLongStrlen = 0;
		$lns = array();
		foreach($lines as $line){
			$label = trim(substr($line, 0 , strpos($line, ":")));
			$lns[] = mb_strlen($label);
		}

		//最も長いラベル
		rsort($lns);
		$mostLong = array_shift($lns);

		//labelが20文字以内であればそのまま返す
		if($mostLong <= self::INQUIRY_LABEL_LENGTH) return $txt;

		//2番目に長いラベル
		for(;;){
			if(!count($lns)) {
				$secLong = 1;
				break;
			}
			$secLong = array_shift($lns);
			if($secLong < self::INQUIRY_LABEL_LENGTH) break;
		}


		//組み立てる
		$t = array();
		foreach($lines as $line){
			if(strlen($line)){
				$label = trim(substr($line, 0 , strpos($line, ":")));
				$content = trim(mb_substr($line, mb_strpos($line, ":") + 1));
				$strlen = mb_strlen($label);
				if($strlen > self::INQUIRY_LABEL_LENGTH){
					//$label = mb_substr($label, 0, $ln - 1) . "...";
					$t[] = $label . " :";
					$t[] = $content;
					$t[] = "";
				}else{
					if(strlen($label)){
						// : の位置を合わせる
						$length = mb_strlen($label);
						if(mb_strlen($content) && $secLong > $length){
							for($i = 0; $i < $secLong - $length; $i++){
								$label .= "  ";
							}
						}
						$t[] = $label . " : " . $content;
					}else{	//住所等
						$t[] = $line;
					}
				}
			}else{
				$t[] = "";
			}
		}

		return trim(implode("\n", $t));
	}

	/** 連番カラム用の便利な関数 **/
	public static function buildSerialNumber(array $cnf){
		if(!isset($cnf["serialNumber"])) return "";
		$num = ((int)$cnf["serialNumber"] > 0) ? $cnf["serialNumber"] : 1;

		$str = "";
		if(isset($cnf["prefix"]) && strlen($cnf["prefix"])){
			$str .= $cnf["prefix"];
			$str = str_replace("##YEAR##", date("Y"), $str);
			$str = str_replace("##MONTH##", date("m"), $str);
			$str = str_replace("##DAY##", date("d"), $str);
		}

		if(isset($cnf["digits"]) && is_numeric($cnf["digits"]) && $cnf["digits"] > 0){
			if(strlen($num) > $cnf["digits"]) $num = (int)substr($num, strlen($num) - $cnf["digits"]);
			$zeros = "";
			$cmp = $cnf["digits"] - strlen($num);
			if($cmp > 0){
				for($i = 0; $i < $cmp; $i++){
					$zeros .= "0";
				}
			}
			$str .= $zeros . $num;
		}else{
			$str .= $num;
		}

		return $str;
	}

	public static function getBlogEntryUrlByInquiryId(int $inquiryId){
		try{
			$rel = SOY2DAOFactory::create("SOYInquiry_EntryRelationDAO")->getByInquiryId($inquiryId);
		}catch(Exception $e){
			$rel = new SOYInquiry_EntryRelation();
		}
		if(!is_numeric($rel->getEntryId())) return null;

		//siteIDからページのURLを辿る
		CMSApplication::switchAdminMode();

		try{
			$site = SOY2DAOFactory::create("admin.SiteDAO")->getById($rel->getSiteId());
		}catch(Exception $e){
			$site = new Site();
		}

		if(!is_numeric($site->getId())){
			CMSApplication::switchAppMode();
			return null;
		}

		//サイトのURLを調べる
		$http = (isset($_SERVER["HTTPS"]) || defined("SOY2_HTTPS") && SOY2_HTTPS) ? "https" : "http";
		$host = $_SERVER['SERVER_NAME'];
		if( (!isset($_SERVER["HTTPS"]) && $_SERVER['SERVER_PORT'] != 80) || (isset($_SERVER["HTTPS"]) && $_SERVER['SERVER_PORT'] != 443) ){
			$host .= ":".$_SERVER['SERVER_PORT'];
		}

		$url = $http . "://" . $host . "/";
		if($site->getIsDomainRoot() != 1){
			$url .= $site->getSiteId() . "/";
		}

		$old["dsn"] = SOY2DAOConfig::dsn();
		$old["user"] = SOY2DAOConfig::user();
		$old["pass"] = SOY2DAOConfig::pass();

		SOY2DAOConfig::dsn($site->getDataSourceName());
		if(strpos($site->getDataSourceName(), "mysql") === 0){
			if(!defined("CMS_COMMON")) define("CMS_COMMON", _CMS_COMMON_DIR_);
			include_once(rtrim(CMS_COMMON, "/") . "/config/db/mysql.php");
			SOY2DAOConfig::user(ADMIN_DB_USER);
			SOY2DAOConfig::pass(ADMIN_DB_PASS);
		}

		//ページのURLを調べる
		try{
			$blogPage = SOY2DAOFactory::create("cms.BlogPageDAO")->getById($rel->getPageId());
		}catch(Exception $e){
			$blogPage = new BlogPage();
		}

		SOY2DAOConfig::dsn($old["dsn"]);
		SOY2DAOConfig::user($old["user"]);
		SOY2DAOConfig::pass($old["pass"]);

		CMSApplication::switchAppMode();

		if(!is_numeric($blogPage->getId())) return null;

		if(strlen($blogPage->getUri())) $url .= $blogPage->getUri() . "/";
		if(strlen($blogPage->getEntryPageUri())) $url .= $blogPage->getEntryPageUri() . "/";

		return $url . $rel->getEntryId();
	}

	/** サムネイル関連 */
	public static function buildThumbnailPathListTable(array $items, int $formId, string $columnId){
		return SOY2Logic::createInstance("logic.ThumbnailLogic")->buildPathListTable($items, $formId, $columnId);
	}

	/**
	 * @param int, string, int, string
	 * @return string
	 */
	public static function getThumbnailFilePath(int $formId, string $columnId, int $idx, string $itemname){
		return SOY2Logic::createInstance("logic.ThumbnailLogic")->getThumbnailFilePath($formId, $columnId, $idx, $itemname);
	}

	/**
	 * @param string, int
	 * @return string
	 */
	public static function getThumbnailSrc(string $path, int $resizeW){
		$src = "/" . ltrim(str_replace($_SERVER["DOCUMENT_ROOT"], "", $path), "/");
		if(defined("_SITE_ROOT_")){			// case soycms
			return "/" . trim(substr(_SITE_ROOT_, strrpos(_SITE_ROOT_, "/")), "/") . "/im.php?src=" . $src . "&width=" . $resizeW;
		}else if(defined("SOYSHOP_ID")){	//case soyshop
			return "/" . SOYSHOP_ID . "/im.php?src=" . $src . "&width=" . $resizeW;
		}else{
			return $src;
		}
	}

	/** Parsley.js連携 **/
	public static function checkIsParsley(){
		static $isParsley;
		if(is_bool($isParsley)) return $isParsley;
		
		$isParsley = false;
		
		if(!defined("SOYSHOP_INQUIRY_FORM_THEME")) return false;
		
		$path = self::_getTemplateDir(SOYSHOP_INQUIRY_FORM_THEME) . "form.php";
		if(!file_exists($path)) return false;
		
		$lines = explode("\n", file_get_contents($path));
		if(!count($lines)) return false;
		
		foreach($lines as $line){
			if(is_numeric(stripos($line, "<form"))) {
				$isParsley = (is_numeric(strpos($line, "data-parsley-validate")));
				break;
			}
		}
		
		return $isParsley;
	}

	/** セッション関連 **/
	public static function getParameter($key){
		$sess = SOY2ActionSession::getUserSession();
		if(isset($_GET[$key]) && strlen($_GET[$key])){
			$v = $_GET[$key];
			$sess->setAttribute(self::SOYINQUIRY_SESSION_ID . $key, $v);
		}else{
			$v = $sess->getAttribute(self::SOYINQUIRY_SESSION_ID . $key);
		}

		return $v;
	}

	public static function setParameter($key, $v){
		self::_setParameter($key, $v);
	}

	public static function setParameters(){
		foreach(self::_getParameterKeys() as $key){
			if(isset($_GET[$key]) && strlen($_GET[$key])){
				self::_setParameter($key, $_GET[$key]);
			}
		}
	}

	public static function clearParameters(){
		foreach(self::_getParameterKeys() as $key){
			self::_setParameter($key, null);
		}
	}

	private static function _setParameter($key, $v){
		SOY2ActionSession::getUserSession()->setAttribute(self::SOYINQUIRY_SESSION_ID . $key, $v);
	}

	private static function _getParameterKeys(){
		return array("site_id", "page_id", "entry_id");
	}
}
