<?php

class SyncLogic extends SOY2LogicBase{

	private $config;
	private $entryDao;
	private $entryLabelDao;

	function sync($exports){

		$dao = SOY2DAOFactory::create("SOYVoice_CommentDAO");
		$voices = array();
		foreach($exports as $id){
			$voices[] = $dao->getById($id);
		}

		$config = $this->getConfig();
		$this->config = $config;

		$old = $this->switchConfig();
		$this->setCMSDsn();

		$site = $this->getSiteConfig($config);
		$this->setSiteDsn($site->getDataSourceName());

		$res = $this->insert($voices);

		$this->resetConfig($old);

		if($res){
			foreach($voices as $voice){
				$voice->setIsEntry(1);
				try{
					$dao->update($voice);
				}catch(Exception $e){

				}
			}

			return true;
		}

		return false;
	}

	//ポストされたデータを直ちに同期する
	function syncPublic($voice){
		if(!$this->config){
			$this->config = $this->getConfig();
		}

		$config = $this->config;

		$old = $this->switchConfig();
		$this->setCMSDsn();

		$site = $this->getSiteConfig($config);
		$this->setSiteDsn($site->getDataSourceName());

		$array = array();
		$array[] = $voice;

		$res = $this->insert($array);

		$this->resetConfig($old);
		if($res){
			$dao = SOY2DAOFactory::create("SOYVoice_CommentDAO");
			$voice->setIsEntry(1);

			try{
				$dao->update($voice);
			}catch(Exception $e){

			}

			return true;
		}

		return false;



	}

	function insert($voices){

		if(!$this->config){
			$this->config = $this->getConfig();
		}

		$config = $this->config;


		SOY2::import("util.UserInfoUtil");
		if(!$this->entryDao){
			$this->entryDao = SOY2DAOFactory::create("cms.EntryDAO");
		}

		foreach($voices as $voice){
			$title = "voice_".$voice->getId();
			$array = explode("\n",$voice->getContent());

			$content = array();
			if(strlen($voice->getImage()>0)){
				$path = SOY_VOICE_IMAGE_ACCESS_PATH.$voice->getImage();
				$content[] = "<p><img src=\"".$path."\" /></p>";
				$content[] = "<p></p>";
			}
			foreach($array as $value){
				$content[] = "<p>".$value."</p>";
			}
			$obj = new Entry();
			$obj->setTitle($title);
			$obj->setAlias($title);
			$obj->setContent(implode("\n",$content));
			$obj->setCdate($voice->getCreateDate());
			$obj->setUdate($voice->getUpdateDate());
			$obj->setOpenPeriodStart(0);
			$obj->setOpenPeriodEnd(2147483647);
			if($config->getIsPublished()==1){
				$obj->setIsPublished(1);
			}else{
				$obj->setIsPublished(0);
			}

			try{
				$id = $this->entryDao->insert($obj);
			}catch(Exception $e){
				$id = null;
			}

			//ラベルの指定がある場合はラベルを付ける
			if(!is_null($id)&&$config->getLabel()>0){
				$this->insertLabel($id);
			}

		}

		return true;
	}

	function insertEntry(){

	}

	function insertLabel($id){
		if(!$this->config){
			$this->config = $this->getConfig();
		}

		$config = $this->config;

		if(!$this->entryLabelDao){
			$this->entryLabelDao = SOY2DAOFactory::create("cms.EntryLabelDAO");
		}

		$obj = new EntryLabel();
		$obj->setEntryId($id);
		$obj->setLabelId($config->getLabel());
		try{
			$this->entryLabelDao->insert($obj);
		}catch(Exception $e){

		}

		return true;

	}

	function getCMSSites(){
		$old = $this->switchConfig();
		$this->setCMSDsn();

		$dao = SOY2DAOFactory::create("admin.SiteDAO");

		try{
			$sites = $dao->getBySiteType(Site::TYPE_SOY_CMS);
		}catch(Exception $e){
			$sites = array();
		}

		$this->resetConfig($old);

		return $sites;
	}

	function getCMSSiteArray($sites){
		$array = array();
		foreach($sites as $site){
			$array[$site->getId()] = $site->getSiteName();
		}
		return $array;
	}

	function getSiteConfig($config){
		$dao = SOY2DAOFactory::create("admin.SiteDAO");
		try{
			$site = $dao->getById($config->getSyncSite());
		}catch(Exception $e){
			$site = new SiteDAO();
		}

		return $site;
	}


	/** ラベルの取得 **/

	function getLabels($siteId){

		$old = $this->switchConfig();
		$this->setCMSDsn();

		$dao = SOY2DAOFactory::create("admin.SiteDAO");
		try{
			$site = $dao->getById($siteId);
		}catch(Exception $e){
			$site = new Site();
		}

		$this->setSiteDsn($site->getDataSourceName());

		$labelDao = SOY2DAOFactory::create("cms.LabelDAO");
		try{
			$labels = $labelDao->get();
		}catch(Exception $e){
			$labels = new Label();
		}

		$this->resetConfig($old);

		return $labels;
	}


	/** DSNの設定変更周り **/

	function switchConfig(){

		$old = array();

		$old["rooDir"] = SOY2::RootDir();
		$old["daoDir"] = SOY2DAOConfig::DaoDir();
		$old["entityDir"] = SOY2DAOConfig::EntityDir();
		$old["dsn"] = SOY2DAOConfig::Dsn();
		$old["user"] = SOY2DAOConfig::user();
		$old["pass"] = SOY2DAOConfig::pass();

		return $old;
	}

	function resetConfig($old){

		SOY2::RootDir($old["rooDir"]);
		SOY2DAOConfig::DaoDir($old["daoDir"]);
		SOY2DAOConfig::EntityDir($old["entityDir"]);
		SOY2DAOConfig::Dsn($old["dsn"]);
		SOY2DAOConfig::user($old["user"]);
		SOY2DAOConfig::pass($old["pass"]);

	}

	function setCMSDsn(){

		SOY2::RootDir(CMS_COMMON);
		SOY2DAOConfig::DaoDir(CMS_COMMON."domain/");
		SOY2DAOConfig::EntityDir(CMS_COMMON."domain/");
		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		SOY2DAOConfig::user(ADMIN_DB_USER);
		SOY2DAOConfig::pass(ADMIN_DB_PASS);

	}

	function setSiteDsn($dsn,$user=null,$pass=null){

		SOY2::RootDir(CMS_COMMON);
		SOY2DAOConfig::DaoDir(CMS_COMMON."domain/");
		SOY2DAOConfig::EntityDir(CMS_COMMON."domain/");
		SOY2DAOConfig::Dsn($dsn);
		SOY2DAOConfig::user($user);
		SOY2DAOConfig::pass($pass);
	}

	function getConfig(){
		$dao = SOY2DAOFactory::create("SOYVoice_ConfigDAO");
		try{
			$config = $dao->getById(1);
		}catch(Exception $e){
			$config = new SOYVoice_Config();
			$config->setCount(5);
		}

		return $config;
	}
}
?>