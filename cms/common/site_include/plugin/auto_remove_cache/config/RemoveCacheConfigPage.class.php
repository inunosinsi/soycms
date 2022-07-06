<?php

class RemoveCacheConfigPage extends WebPage {

    function __construct(){}

    function execute(){
        parent::__construct();

        $this->addLabel("job_path", array(
			"text" => dirname(dirname(__FILE__)) . "/job/clear.php"
		));

		$this->createAdd("cache_dir_list", "CacheDirectoryListComponent", array(
			"list" => self::_getCacheDirectoryList()
		));
    }

	private function _getCacheDirectoryList(){
		$root = dirname(SOY2::RootDir());
		$list = array();
		foreach(array("admin", "app", "soycms", "soyshop") as $dir){
			$list[] = $root . "/" . $dir . "/cache/";
		}
		
		$old = CMSUtil::switchDsn();
		$sites = SOY2Logic::createInstance("logic.admin.Site.SiteLogic")->getSiteList();
		CMSUtil::resetDsn($old);
		foreach($sites as $site){
			$list[] = $site->getPath() . ".cache/";
		}
		return $list;
	}
}

class CacheDirectoryListComponent extends HTMLList{

	function populateItem($entity){
		$this->addLabel("dir", array(
			"text" => (is_string($entity)) ? $entity : ""
		));
	}
}