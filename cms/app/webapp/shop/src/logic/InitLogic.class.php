<?php

class InitLogic extends SOY2LogicBase{

	/**
	 * SOY Shopを初期化する
	 */
    public function init(){
    	$this->initTable();
    }

    /**
     * テーブルを初期化する
     */
    private function initTable(){

    	$db = new SOY2DAO();
    	$db->begin();

		//shop側のDBは廃止
    	// $sqls = file_get_contents(dirname(__FILE__)."/table_". SOYCMS_DB_TYPE .".sql");
    	// $sqls = explode(";",$sqls);
    	// foreach($sqls as $sql){
    	// 	if(strlen(trim($sql))<1)continue;
    	// 	try{
    	// 		$db->executeUpdateQuery($sql,array());
    	// 	}catch(Exception $e){
    	// 		var_dump($e);
    	// 	}
    	// }

    	if(!file_exists(CMS_COMMON . "db/".APPLICATION_ID.".db")){
    		file_put_contents(CMS_COMMON . "db/".APPLICATION_ID.".db", "created:" . date("Y-m-d H:i:s"));
    	}

		if(file_exists(CMS_COMMON . "db/".APPLICATION_ID.".db")){
			$db->commit();
		}

    }

    /**
     * 既存のシングルサイトをマルチサイト
     * @param Stirng siteId サイトID
     * @param String dir サイトディレクトリ
     * @param String url サイトURL
     * @param String dsn DSN
     */
    public function registSite($siteId,$dir,$url,$dsn=""){

		$name = $this->getSiteName($dsn);

		/* shop.dbに登録 */
    	$obj = new SOYShop_Site();
    	$obj->setSiteId($siteId);
		$obj->setName($name);
    	$obj->setPath($dir);
    	$obj->setUrl($url);
    	$obj->setDsn($dsn);

    	try{
    		$obj->save();
    	}catch(Exception $e){

    	}

    	/* SOY CMSのサイトとして登録 */
		//SOY2 config
		//SOY2::RootDir()の書き換え
		$oldRooDir = SOY2::RootDir();
		$oldDaoDir = SOY2DAOConfig::DaoDir();
		$oldEntityDir = SOY2DAOConfig::EntityDir();
		$oldDsn = SOY2DAOConfig::Dsn();
		$oldUser = SOY2DAOConfig::user();
		$oldPass = SOY2DAOConfig::pass();

		SOY2::RootDir(CMS_COMMON);
		SOY2DAOConfig::DaoDir(CMS_COMMON."domain/");
		SOY2DAOConfig::EntityDir(CMS_COMMON."domain/");
		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		SOY2DAOConfig::user(ADMIN_DB_USER);
		SOY2DAOConfig::pass(ADMIN_DB_PASS);

		$dao = SOY2DAOFactory::create("admin.SiteDAO");

		$site = new Site();
		$site->setSiteId($siteId);
		$site->setPath($dir);
		$site->setSiteType(Site::TYPE_SOY_SHOP);
		$site->setDataSourceName($dsn);
		try{
			$dao->insert($site);
		}catch(Exception $e){

		}

		SOY2::RootDir($oldRooDir);
		SOY2DAOConfig::DaoDir($oldDaoDir);
		SOY2DAOConfig::EntityDir($oldEntityDir);
		SOY2DAOConfig::Dsn($oldDsn);
		SOY2DAOConfig::user($oldUser);
		SOY2DAOConfig::pass($oldPass);

    }

    /**
     * /soyshop/webapp/config/shop/xxx.admin.conf.php
     * /soyshop/webapp/config/shop/xxx.conf.php
     * @param Stirng siteId サイトID
     * @param String dir サイトディレクトリ
     * @param String url サイトURL
     */
    function outputConfig($siteId,$siteDir,$url){

    	/* admin.conf.phpの作成 */
		$dir = dirname(CMS_COMMON)."/soyshop/webapp/conf/shop/";
		$name = $siteId . ".admin.conf.php";

		$config = array();
		$config[] = "<?php";
		$config[] = 'define("'.$siteId.'_SOYSHOP_ID","'.$siteId.'");';
		$config[] = 'define("'.$siteId.'_SOYSHOP_SITE_DIRECTORY","'. $siteDir.'");';
		$config[] = 'define("'.$siteId.'_SOYSHOP_SITE_URL","'.$url.'");';
		if(defined("SOYSHOP_SITE_DSN")){
			$config[] = '/* configure for mysql */';
			$config[] = 'define("'.$siteId.'_SOYSHOP_SITE_DSN","'.SOYSHOP_SITE_DSN.'");';
			$config[] = 'define("'.$siteId.'_SOYSHOP_SITE_USER","'.SOYSHOP_SITE_USER.'");';
			$config[] = 'define("'.$siteId.'_SOYSHOP_SITE_PASS","'.SOYSHOP_SITE_PASS.'");';
		}
		$config[] = '?>';

		file_put_contents($dir . $name, implode("\n",$config));


    	/* conf.phpの作成 */
    	$name = $siteId . ".conf.php";

		$config = array();
		$config[] = "<?php";
		$config[] ='include(dirname(__FILE__)."/'.$siteId. '.admin.conf.php");';
		$config[] = 'if(!defined("SOYSHOP_ID")) define("SOYSHOP_ID", '.$siteId.'_SOYSHOP_ID);';
		$config[] = 'if(!defined("SOYSHOP_SITE_DIRECTORY")) define("SOYSHOP_SITE_DIRECTORY", '. $siteId .'_SOYSHOP_SITE_DIRECTORY);';
		$config[] = 'if(!defined("SOYSHOP_SITE_URL")) define("SOYSHOP_SITE_URL", '.$siteId.'_SOYSHOP_SITE_URL);';
		if(defined("SOYSHOP_SITE_DSN")){
			$config[] = '/* configure for mysql */';
			$config[] = 'if(!defined("SOYSHOP_SITE_DSN")) define("SOYSHOP_SITE_DSN", '. $siteId. '_SOYSHOP_SITE_DSN);';
			$config[] = 'if(!defined("SOYSHOP_SITE_USER")) define("SOYSHOP_SITE_USER", '. $siteId. '_SOYSHOP_SITE_USER);';
			$config[] = 'if(!defined("SOYSHOP_SITE_PASS")) define("SOYSHOP_SITE_PASS", '. $siteId. '_SOYSHOP_SITE_PASS);';
		}
		$config[] = '?>';

		file_put_contents($dir . $name,implode("\n",$config));

    }

    /**
     * @param String dns DSN
     * @return Stirng 既存のショップ名
     */
    function getSiteName($dsn){

		//SOY2 config
		//SOY2::RootDir()の書き換え
		$oldRooDir = SOY2::RootDir();
		$oldDaoDir = SOY2DAOConfig::DaoDir();
		$oldEntityDir = SOY2DAOConfig::EntityDir();
		$oldDsn = SOY2DAOConfig::Dsn();
		$oldUser = SOY2DAOConfig::user();
		$oldPass = SOY2DAOConfig::pass();

		// /soyshop
		$soyshopDir = dirname(CMS_COMMON)."/soyshop/webapp/src/";
		SOY2::RootDir($soyshopDir);
		SOY2DAOConfig::DaoDir($soyshopDir."domain/");
		SOY2DAOConfig::EntityDir($soyshopDir."domain/");
		SOY2DAOConfig::Dsn($dsn);
		if(defined("SOYSHOP_SITE_DSN")){//mysql
			SOY2DAOConfig::user(SOYSHOP_SITE_USER);
			SOY2DAOConfig::pass(SOYSHOP_SITE_PASS);
		}
		SOY2::import("domain.config.SOYShop_DataSets");
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$config = SOYShop_ShopConfig::load();
		$name = $config->getShopName();

		SOY2::RootDir($oldRooDir);
		SOY2DAOConfig::DaoDir($oldDaoDir);
		SOY2DAOConfig::EntityDir($oldEntityDir);
		SOY2DAOConfig::Dsn($oldDsn);
		SOY2DAOConfig::user($oldUser);
		SOY2DAOConfig::pass($oldPass);


    	return $name;
    }


    /**
     * 既存のSOYShopサイトのIDが、既にSOY CMSで使われているかのチェック
     * @param String siteId soyshop site id
     * @param Boolean
     */
    public function checkSiteId($siteId){
    	$old = ShopUtil::switchConfig();
    	ShopUtil::setCMSDsn();
    	$dao = SOY2DAOFactory::create("admin.SiteDAO");

    	try{
    		$dao->getBySiteId($siteId);
    		$res = false;//サイトIDが存在する
    	}catch(Exception $e){
    		$res = true;
    	}

    	ShopUtil::resetConfig($old);

    	return $res;
    }
}
