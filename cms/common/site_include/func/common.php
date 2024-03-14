<?php
/**
 * @param string
 * @return bool
 */
function soycms_check_is_image_path(string $path){
	// 拡張子がない場合は調べない
	if(is_bool(strpos($path, ".")) || !strlen(trim(substr($path, strrpos($path, "."))))) return false;
	
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