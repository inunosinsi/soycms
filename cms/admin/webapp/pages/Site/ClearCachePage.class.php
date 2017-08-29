<?php

class ClearCachePage extends CMSUpdatePageBase{

	private $id;

	function __construct($args) {

		$this->id = (isset($args[0])) ? $args[0] : null;

		if(!UserInfoUtil::isDefaultUser() || count($args) < 1){
			//デフォルトユーザのみ
			$this->jump("Site.Detail.".$this->id);
			exit;
		}

		if(soy2_check_token() && $this->id){
			$this->clearCache();
		}

		$this->jump("Site.Detail." . $this->id);
		exit;

	}

	private function clearCache(){

		$siteDAO = SOY2DAOFactory::create("admin.SiteDAO");
		try{
			$site = $siteDAO->getById($this->id);
		}catch(Exception $e){
			//$this->addErrorMessage("");
			$this->jump("Site.Detail." . $this->id);
		}

		//キャッシュ削除
		$cacheDir = $site->getPath().".cache";
		if($site && strlen($site->getPath()) && is_dir($cacheDir)){
			CMSUtil::unlinkAllIn($cacheDir);
			$this->addMessage("ADMIN_DELETE_CACHE");
		}

	}
}
