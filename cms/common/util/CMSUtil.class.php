<?php

class CMSUtil {

	const DATE_MIN = 0;
	const DATE_MAX = 2147483647;

	const MODE_ADMIN = 0;	//adminの管理画面を見ているか？
	const MODE_SOYCMS = 1;	//サイト毎の管理画面を見ているか？

	/**
	 * Convert Unix time stamp to CMS time format
	 * @param boolean true:startDate false:endDate
	 */
	public static function encodeDate($date,$startFlag = true){

		if(is_null($date)){
			if($startFlag){
				//min date
				return self::DATE_MIN;
			}else{
				//Max date
				return self::DATE_MAX;
			}
		}else{
			return $date;
		}
	}

	/**
	 * Convert CMS time format to UNIX time stamp
	 */
	public static function decodeDate($date){
		if($date == self::DATE_MIN || $date == self::DATE_MAX){	//
			return null;
		}else{
			return $date;
		}
	}

	/**
	 * サイトURLをサイト側のデータベースから取得する。取得できなければUserInfoUtilの公開URLを調べる
	 */
	public static function getSiteUrl(){
		static $siteUrlBySiteUrl;
		if(is_null($siteUrlBySiteUrl)){
			$siteConfigDao = SOY2DAOFactory::create("cms.SiteConfigDAO");
	    	try{
	    		$siteConfig = $siteConfigDao->get();
	    	}catch(Exception $e){
	    		$siteConfig = new SiteConfig();
	    	}

	    	//SiteConfigに入っているURLを取得する
			$url = $siteConfig->getConfigValue("url");
			if(is_bool($url) && !$url) $url = null;	//falseで返ってくることがあった。
			if(isset($url) && is_string($url) && strlen($url) > 0){
				$siteUrlBySiteUrl = $url;
			//SiteConfigにURLが入っていなかった場合はUserInfoUtilから公開URLを取得する
			}else{
				$siteUrlBySiteUrl = UserInfoUtil::getSitePublishURL();
			}
		}

    	return $siteUrlBySiteUrl;
	}

	public static function getEntryHiddenInputHTML($entryId,$title){
		$str = CMSMessageManager::get("SOYCMS_PREVIEW_EDIT_BUTTON");
		$str = str_replace("%TITLE%", "[".$title."]", $str);
		return "<button type=\"button\" class=\"cms_hidden_entry_id\" entryid=\"$entryId\" style=\"display:none;\">".$str."</button>";
	}

	public static function getEntryAddHiddenInputHTML($labelId){
		$str = CMSMessageManager::get("SOYCMS_PREVIEW_ADD_BUTTON");
		return "<button type=\"button\" class=\"cms_hidden_entry_id\" labelid=\"$labelId\" style=\"display:none;\">".$str."</button>";
	}

	/**
	 * notifyUpdate
	 */
	public static function notifyUpdate(){
		static $dao;
		if(!$dao){
			$dao = SOY2DAOFactory::create("cms.SiteConfigDAO");
		}
		return $dao->notifyUpdate();
	}

	/**
	 * ディレクトリ内のファイルを全削除（キャッシュ削除用）
	 */
	public static function unlinkAllIn($dir, $recursive = false, $rmdir = false){
		if(file_exists($dir) && is_dir($dir)){
			if($dir[strlen($dir)-1] != "/") $dir .= "/";
			$files = @scandir($dir);
			if(is_array($files) && count($files)){
				foreach($files as $file){
					if(!is_file($dir.$file) && !is_dir($dir.$file)) continue;
					if($file[0] == ".") continue;
					if(is_dir($dir.$file) && $recursive){
						self::unlinkAllIn($dir.$file, $recursive, $rmdir);
						if($rmdir) @rmdir($dir.$file);
					}else{
						@unlink($dir.$file);
					}
				}
			}
		}
	}

	/**
	 * エントリーやラベルのエイリアスでURLに使われると困る文字列を除去する
	 * ?#/%\&+@;:$,=
	 * 2009-04-24 半角スペース, ", ' を追加
	 * 2009-06-11 半角スペースは_に変換する（SEOや読みやすさのために）
	 * 2010-02-19 RFC2396のreservedのうち +  @ ; : $ , = を追加し、すべて _ に変換することにした。
	 *            残りのreservedのうち / ? & は既存。なお @ ; : $ , = はアクセス可能。
	 * 2011-07-07 リンクを張る際に不都合が出やすいので<>も追加
	 */
	public static function sanitizeAlias($alias){
		$alias = str_replace(array("?","#","/","%","\\", "&", "'", '"', "+", "@", ";", ":", '$', ",", "=", "<", ">")," ",$alias);
		$alias = trim($alias);
		$alias = preg_replace("/ +/","_",$alias);
		return $alias;
	}

	/**
	 * mkdirしてchmodする
	 * mkdir(dir,mode)がうまく動かないサーバーがある
	 * @return boolean
	 */
	public static function makeDir($dir, $mode=0700){
		$res = file_exists($dir);
		if( is_writable(dirname($dir)) && !file_exists($dir) ) $res = @mkdir($dir);
		if( $res) $res = @chmod($dir,$mode);
		return $res;
	}

	/**
	 * ファイルのバックアップを作成
	 * @return boolean
	 */
	public static function createBackup($file){
		$file = realpath($file);
		$dir = dirname($file);

		if( file_exists($file) && is_writable($dir) ){
			$backup = $backup_filename_base = "{$file}.old";

			$i = 1;
			while(file_exists($backup) && $i<100){
				$backup = sprintf("{$backup_filename_base}.%02d",$i);
				$i++;
			}

			return @copy($file,$backup);
		}

		return false;
	}

	/**
	 * バックアップファイルのリスト
	 */
	public static function getBackupList($original){
		$list = array();

		$backup_filename_base = "{$original}.old";

		if(file_exists($backup_filename_base)){
			$list[] = $backup_filename_base;
		}

		for($i=1;$i<100;++$i){
			$backup = sprintf("{$backup_filename_base}.%02d",$i);
			if(file_exists($backup)){
				$list[] = $backup;
			}
		}

		return $list;
	}

	/**
	 * 12時間以内なら 時:分 を、半年以内なら 月/日 を、他は 年-月-日 を返す
	 */
	public static function getRecentDateTimeText($unixtime){
		if(!is_numeric($unixtime)) $unixtime = 0;
		$diff = abs(time() - $unixtime);
		switch(true){
			case $diff < 12*60*60:
				return date("H:i", $unixtime);
			case $diff < 180*24*60*60:
				return date("n/j", $unixtime);
			default:
				return date("Y-n-j", $unixtime);
		}
	}

	/**
	 * 公開期間設定の文字列を返す（多言語対応）
	 * TODO とりあえずここに書くけどもっとふさわしい場所があるはず。CMSMessageManagerとか。
	 */
	public static function getOpenPeriodMessage($start, $end){
		if( !is_null($start) AND !is_null($end) ){
			$text = CMSMessageManager::get("SOYCMS_PUBLISH_FROM_TO", array(
				"FROM" => date("Y-m-d H:i:s",$start),
				"TO"   => date("Y-m-d H:i:s",$end)
			));
		}elseif(!is_null($start)){
			$text = CMSMessageManager::get("SOYCMS_PUBLISH_FROM", array(
				"FROM" => date("Y-m-d H:i:s",$start)
			));
		}elseif(!is_null($end)){
			$text = CMSMessageManager::get("SOYCMS_PUBLISH_TO", array(
				"TO" => date("Y-m-d H:i:s",$end)
			));
		}else{
			$text = CMSMessageManager::get("SOYCMS_NO_SETTING");
		}
		return $text;
	}

	/** ロゴ画像 **/
	public static function getLogoFile($mode=self::MODE_ADMIN){
		switch($mode){
			case self::MODE_SOYCMS:
				$logoDir = dirname(SOY2::RootDir()) . "/soycms/image/logo/";
				break;
			case self::MODE_ADMIN:
			default:
				$logoDir = dirname(SOY2::RootDir()) . "/admin/image/logo/";
				break;
		}
		if(strpos($logoDir, "/app/webapp/") && defined("APPLICATION_ID")){	// SOY Appから開いている場合
			$logoDir = str_replace("/app/webapp/" . APPLICATION_ID, "", $logoDir);
		}

		if(file_exists($logoDir) && is_dir($logoDir)){
			foreach(glob($logoDir . "*") as $f){
				if(is_file($f) && !strpos($f, ".txt")){
					$fileName = trim(substr($f, strrpos($f, "/") + 1), "/");
					if(preg_match('/\.(jpg|jpeg|gif|png|bmp)/', $fileName, $tmp)){
						$src = SOY2PageController::createRelativeLink("image/logo/" . $fileName);
						if(strpos($src, "/app/image/logo/") && defined("APPLICATION_ID")) {
							$src = str_replace("/app/image/logo/", "/admin/image/logo/", $src);
						}
						return $src;
					}
				}
			}
		}
		$src = SOY2PageController::createRelativeLink("css/img/logo_big.gif");
		if(strpos($src, "/app/css/") && defined("APPLICATION_ID")) {
			$src = str_replace("/app/css/", "/admin/css/", $src);
		}
		return $src;
	}

	public static function getCMSName(){
		return (defined("SOYCMS_CMS_NAME")) ? SOYCMS_CMS_NAME : "SOY CMS";
	}

	public static function getDeveloperName(){
		return (defined("SOYCMS_DEVELOPER_NAME")) ? SOYCMS_DEVELOPER_NAME : "Brassica, Inc.";
	}


	/* 以下使わなくなったメソッド */

	/**
	 * 翻訳ファイルを設定する
	 */
	public static function Text($lang = null){
		static $_lang;

		if($lang)$_lang = $lang;

		return $_lang;
	}

	/**
	 * 翻訳を行う
	 * ソースコードはjaで書かれていることを基本にする
	 */
	public static function getText($text){

		$soycms_language = self::Text();

		if(isset($soycms_language[$text])){
			return $soycms_language[$text];
		}

		return $text;
	}

	/**
	 * バイト数をGBやMBなどの文字列に変換する
	 */
	public static function GetHumanReadableSize($byte){
		$byte *= 10;

		if($byte >= 1073741824){//1024*1024*1024 = 1073741824
			$valueX10 = floor($byte / 1073741824);
			$unit = "GB";
		}elseif($byte >= 1048576){//1024*1024 = 1048576
			$valueX10 = floor($byte / 1048576);
			$unit = "MB";
		}elseif($byte >= 1024){
			$valueX10 = floor($byte / 1024);
			$unit = "KB";
		}else{
			$valueX10 = floor($byte);
			$unit = "B";
		}

		return ($valueX10/10).$unit;
	}

	/**
	 * ◯GBや◯MBなどの文字列をバイト数に変換する
	 */
	function GetNumricByte($val) {
		$val = trim($val);

		//末尾のBを削除
		if(strlen($val) && strtoupper($val[strlen($val)-1]) == "B" ){
			$val = substr($val,0,strlen($val)-1);
		}

		$last = strtoupper($val[strlen($val)-1]);
		switch($last) {
			case 'G':
				$val *= 1024;
			case 'M':
				$val *= 1024;
			case 'K':
				$val *= 1024;
		}

		return $val;
	}

	/* 以下ServerInfoUtilに移したメソッド */

	/**
	 * mod_rewriteが使えるかどうか
	 * 2010-02-19 ServerInfoUtil::isEnableModRewriteに移動（メソッドは元からあった）
	 * @return boolean
	 */
	public static function checkEnableModRewrite(){
		SOY2::import("util.ServerInfoUtil");
		return ServerInfoUtil::isEnableModRewrite();
	}

	/**
	 * Zipが利用可能かどうか判断
	 * 2010-02-19 ServerInfoUtilに移動
	 * @return クラス名
	 */
	public static function checkZipEnable($expandOnly = false){
		SOY2::import("util.ServerInfoUtil");
		return ServerInfoUtil::checkZipEnable($expandOnly);
	}

	/**
	 * DSNをadminに切り替える
	 */
	public static function switchDsn(){
		$old["dsn"] = SOY2DAOConfig::Dsn();
		$old["user"] = SOY2DAOConfig::User();
		$old["pass"] = SOY2DAOConfig::Pass();

		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		SOY2DAOConfig::User(ADMIN_DB_USER);
		SOY2DAOConfig::Pass(ADMIN_DB_PASS);

		return $old;
	}

	public static function resetDsn($old){
		SOY2DAOConfig::Dsn($old["dsn"]);
		SOY2DAOConfig::User($old["user"]);
		SOY2DAOConfig::Pass($old["pass"]);
	}

	/**
	 * 記事のエイリアスをURLとして出力するためにエンコードする
	 */
	public static function urlencodeForEntryAlias($alias){
		return rawurlencode($alias);
	}

	/**
	 * 記事雛形が利用可能かどうか
	 * @return boolean
	 */
	public static function isEntryTemplateEnabled(){
		return self::_isSimpleXmlEnabled();
	}

	/**
	 * ページ雛形が利用可能かどうか
	 * @return boolean
	 */
	public static function isPageTemplateEnabled(){
		return self::checkZipEnable() && self::_isSimpleXmlEnabled();
	}

	/**
	 * simplexml_load_fileが利用可能かどうか
	 * @return boolean
	 */
	private static function _isSimpleXmlEnabled(){
		return function_exists("simplexml_load_file");
	}

}
