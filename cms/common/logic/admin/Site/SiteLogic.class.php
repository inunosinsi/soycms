<?php

class SiteLogic implements SOY2LogicInterface{

	private $exception;

    public static function getInstance($a,$b){
		return SOY2LogicBase::getInstance($a,$b);
	}

    /**
	 * サイトの一覧を取得し、そのオブジェクトの配列を返す。
	 * 使用するDAO:admin.SiteDAO
	 */
	function getSiteList(){

    	$dao = SOY2DAOFactory::create("admin.SiteDAO");

		try{
			$entities = $dao->get();
			return $entities;
		}catch(Exception $e){
			return array();
		}
    }

    /**
     * SOY CMSのサイト一覧を取得し、そのオブジェクトの配列を返す
     * ショップは含まない。
     */
	function getSiteOnly(){

    	$dao = SOY2DAOFactory::create("admin.SiteDAO");
		try{
			$entities = $dao->getBySiteType(Site::TYPE_SOY_CMS);
			return $entities;
		}catch(Exception $e){
			return array();
		}
    }

    /**
     * ユーザIDに対してログイン可能なサイト一覧を取得する
     * 使用するDAO:admin.AdministratorDAO
     *            :admin.SiteRoleDAO
     *            :admin.SiteDAO
     * @param userId Administor.id(SiteRole.user_id)の値。
     * @return 失敗したときarray()
     */
    function getSiteByUserId($userId){

    	$siteRoleDAO = SOY2DAOFactory::create("admin.SiteRoleDAO");
    	$administoratorDAO = SOY2DAOFactory::create("admin.AdministratorDAO");
    	$siteDAO = SOY2DAOFactory::create("admin.SiteDAO");


    	try{
    		$admin = $administoratorDAO->getById($userId);
    	}catch(Exception $e){
    		return array();
    	}

    	//もし初期管理者ならばすべてのサイトを表示
    	if($admin->getIsDefaultUser()){
	    	return $this->getSiteList();
    	}

    	try{
    		$sites = $siteRoleDAO->getByUserId($userId);
    	}catch(Exception $e){
    		return array();
    	}

    	//SiteRoleに登録されているSiteIDの実体を取得しarrayにする
    	$ret_val = array();
    	try{
	    	foreach($sites as $key => $siteRole){
	    		$site = $siteDAO->getById($siteRole->getSiteId());
	    		array_push($ret_val,$site);
	    	}
    	}catch(Exception $e){
    		return array();
    	}

    	return $ret_val;
    }

	function getLoginableSiteListByUserId($userId){
		$list = self::getSiteByUserId(UserInfoUtil::getUserId());

		//ルート設定されたサイトを先頭にする
		foreach($list as $id => $site){
			if($site->getIsDomainRoot()){
				unset($list[$id]);
				array_unshift($list, $site);
			}
		}

		return $list;
	}

	/**
	 * 現在のユーザIDからログイン可能なサイトのIDのリストを取得する
	 */
	function getLoginableSiteIdsByUserId($userId){
		$ids = array();
		$list = self::getLoginableSiteListByUserId($userId);
		foreach($list as $key => $site){
			$ids[] = $site->getId();
		}
		return $ids;
	}

    /**
	 * 新しくサイトを追加する
	 * @return boolean
	 * @param siteId サイトID
	 * @param siteName サイト名
	 * @param encoding 文字コード
	 * @param flag 管理側DBに作成するかどうか
	 */
	function createSite($siteId, $siteName, $encoding, $flag = true, $copyFrom = false, $dbType = SOYCMS_DB_TYPE){
		$dao = SOY2DAOFactory::create("admin.SiteDAO");

		//すでにディレクトリが存在するかどうか
		$dirAlreadyExists = file_exists(SOYCMS_TARGET_DIRECTORY . $siteId);
		$logic = SOY2Logic::createInstance("logic.admin.Site.SiteCreateLogic");

		//$dao->begin();
		try{

			if($flag){
				//サイトのDBを作成する
				try{
					$logic->createDataBase($siteId);
				}catch(Exception $e){
					throw $e;
				}
			}else{
				//管理側DBと同居
				$logic->dsn = SOY2DAOConfig::Dsn();
			}

			//サイトのディレクトリ、DBを初期化
			try{
					$logic->createNewSite($siteId, $dbType);
					$logic->initSiteConfig($siteName,$encoding);
					$logic->initDefaultPage(UserInfoUtil::getSiteURLBySiteId($siteId));
				}catch(Exception $e){
					//既存のサイトを移管する際のためにエラーとはしない
					//throw $e;
			}


			//サイトの存在チェック：ないときは例外発生
			$logic->checkIfSiteCreated();

			//管理側DBに情報を追加
			$site = new Site();
			$site->setSiteId($siteId);
			$site->setSiteName($siteName);
			$site->setPath(str_replace("\\","/",realpath(SOYCMS_TARGET_DIRECTORY . $siteId)) ."/");
			$site->setUrl(UserInfoUtil::getSiteURLBySiteId($siteId));
			$site->setDataSourceName($logic->dsn);
			$id = $dao->insert($site);

			if($copyFrom){
				try{
					$logic->createNewSite($siteId);
					$this->copySite($copyFrom,$id);
				}catch(Exception $e){
					throw $e;
				}
			}

			//$dao->commit();

			return $id;
		}catch(Exception $e){
			$this->setException($e);

			$logic->log(var_export($e,true));

			//$dao->rollback();

			//新たにディレクトリを作ったときは削除する
			if(!$dirAlreadyExists){
				//ログを移動しておく
				$logic->move_log_to_common_log();

				$siteDir = SOYCMS_TARGET_DIRECTORY.$siteId;
				SOY2Logic::createInstance("logic.admin.Site.SiteDeleteLogic")->deleteSiteDir($siteDir);
			}

			return false;
		}
	}
	/**
	 * サイトを削除する
	 * @return boolean
	 * @param id 削除するサイトのID
	 */
    function removeSite($id, $dropDB = true, $rmDir = true){
		return SOY2Logic::createInstance("logic.admin.Site.SiteDeleteLogic")->deleteSite($id, $dropDB, $rmDir);
    }

    /**
     * サイトIDからサイトの情報を取得する
     * @param id サイトID
     */
    function getById($id){
		try{
			return SOY2DAOFactory::create("admin.SiteDAO")->getById($id);
		}catch(Exception $e){
			return null;
		}
    }

    function getException() {
    	return $this->exception;
    }
    function setException($exception) {
    	$this->exception = $exception;
    }

	/**
	 * サイトをコピーする
	 */
    function copySite($from, $to){
    	return SOY2Logic::createInstance("logic.admin.Site.SiteCopyLogic")->copySite($from, $to);
    }
}
