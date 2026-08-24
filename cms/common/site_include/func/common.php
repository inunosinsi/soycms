<?php
/**
 * @param string
 * @return bool
 */
function soycms_check_is_image_path(string $path){
	// 拡張子がない場合は調べない
	if(is_bool(strpos($path, ".")) || !strlen(trim(substr($path, strrpos($path, "."))))) return false;
	
	// httpから始まる場合はドメインまでを除いておく
	if(soy2_strpos($path, "http") === 0){
		preg_match('/^https?:\/\//', $path, $tmp);
		if(isset($tmp[0])){
			$path = str_replace($tmp[0], "", $path);
			$path = substr($path, strpos($path, "/"));
		}
	}

	//シンプルにDOCUMENT_ROOT + pathでファイルが存在しているか？
	if(file_exists($_SERVER["DOCUMENT_ROOT"] . $path)) return true;

	// @ToDo サイトの設定で特殊なものがある場合に下記に追加していく

	return false;
}

/**
 * @return SiteConfig
 */
function soycms_get_site_config_object(){
	static $cnf;
	if(is_null($cnf)) $cnf = SOY2DAOFactory::create("cms.SiteConfigDAO")->get();
	return $cnf;
}

/**
 * @return array
 */
function soycms_get_site_list(){
	$old = CMSUtil::switchDsn();

	$dao = SOY2DAOFactory::create("admin.SiteDAO");
	
	/** @ToDo いずれはSOYShopの方でも分けられるようしたい */
	try{
		$sites = $dao->getBySiteType(Site::TYPE_SOY_CMS);
	}catch(Exception $e){
		$sites = array();
	}
	
	CMSUtil::resetDsn($old);
	
	return $sites;
}

/**
 * @return array
 */
function soycms_get_site_name_list(){
	$sites = soycms_get_site_list();
	if(!count($sites)) return array();

	$list = array();
	foreach($sites as $site){
		$list[$site->getSiteId()] = $site->getSiteName();
	}

	return $list;
}

/**
 * @param string
 * @return string
 */
function soycms_avif2jpeg(string $filepath){
	if(is_bool(strpos($filepath, ".avif")) && is_bool(strpos($filepath, ".AVIF"))) return $filepath;
	foreach(array(".jpg", ".JPG", ".png", ".PNG") as $ext){
		$_dust = str_replace(array(".avif", ".AVIF"), $ext, $filepath);
		if(file_exists(rtrim($_SERVER["DOCUMENT_ROOT"], "/").$_dust)){
			return $_dust;
		}	
	}
	return $filepath;
}

/**
 * SOY CMSに内蔵されたアクセスログ(NotFoundページのみ)に記録する
 * 集計しやすいようにデータベースに格納する
 */
function soycms_write_notfound_log(){
	// DSNの切り替え
	$oldDsn = SOY2DAOConfig::Dsn();
	SOY2DAOConfig::Dsn(ADMIN_DB_DSN);

	// IPアドレスの記録
	$ipDao = SOY2DAOFactory::create("admin.IpAddressDAO");	
	$ip = $ipDao->getByV4($_SERVER["REMOTE_ADDR"]);

	if(is_null($ip->getId())){
		try{
			$id = $ipDao->insert($ip);
			$ip->setId($id);
		}catch(Exception $e){
			//
		}
	}

	// 悪意のあるアクセスである場合はチェックを入れておく
	if((int)$ip->getIsSuspicious() === 0 && soycms_check_suspicious_access($_SERVER["REQUEST_URI"])){
		$ip->setIsSuspicious(1);
		try{
			$ipDao->update($ip);
		}catch(Exception $e){
			//
		}
	}
	
	// リクエストURIの記録
	$reqDao = SOY2DAOFactory::create("admin.RequestUriDAO");
	$req = $reqDao->getByUri($_SERVER["REQUEST_URI"]);

	if(is_null($req->getId())){
		try{
			$id = $reqDao->insert($req);
			$req->setId($id);
		}catch(Exception $e){
			//
		}
	}

	if(is_numeric($ip->getId()) && is_numeric($req->getId())) {
		//アクセスログの記録
		$logDao = SOY2DAOFactory::create("admin.NotFoundLogDAO");	
		$log = new NotFoundLog();
		$log->setIpAddressId($ip->getId());
		$log->setRequestUriId($req->getId());

		try{
			$logDao->insert($log);
		}catch(Exception $e){
			//
		}

		$logDao->rotateLogs();
		$ipDao->rotateLogs();
		$reqDao->rotateLogs();
	}
	

	SOY2DAOConfig::Dsn($oldDsn);
}

/**
 * 疑わしいURIにアクセスがあった場合はtrueを返す
 * @param string
 * @return bool
 */
function soycms_check_suspicious_access(string $uri){
	// 部分的に含まれている文字列のリスト
	$includeList = explode("\n", file_get_contents(SOY2::RootDir()."config/log/includes"));

	foreach($includeList as $inc){
		$inc = trim($inc);
		if(!strlen($inc)) continue;
		
		if(is_numeric(strpos($uri, $inc))) return true;
	}

	// URIの末尾に該当するか？
	$uri = rtrim($uri, "/");
	$fileList = explode("\n", file_get_contents(SOY2::RootDir()."config/log/files"));
	foreach($fileList as $f){
		$f = trim($f);
		if(!strlen($f)) continue;
		
		if(preg_match('/'.$f.'$/', $uri) === 1) return true;
	}
	return false;
}
