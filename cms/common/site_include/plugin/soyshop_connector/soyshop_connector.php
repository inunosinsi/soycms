<?php

SOYShopConnectorPlugin::register();
class SOYShopConnectorPlugin{

	const PLUGIN_ID = "soyshop_connector";

	function getId(){
		return self::PLUGIN_ID;
	}
	
	private $siteId = "shop";

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"SOY Shop連携プラグイン",
			"description"=>"SOY Shopと連携するために使用します。<br />SOY Shopのパーツモジュールを呼び出せます。",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.0"
		));

		//プラグインのアクティブかつ、SOY Shopがインストールされているか？
		$soyshopRoot = dirname(SOY2::RootDir()) . "/soyshop/"; 
		if(CMSPlugin::activeCheck($this->getId()) && file_exists($soyshopRoot)){
			
			if(!class_exists("SOYShopUtil")) SOY2::import("util.SOYShopUtil");
			
			//SOY Shopがインストールされていれば動く
			if(SOYShopUtil::checkSOYShopInstall()){
				
				CMSPlugin::addPluginConfigPage($this->getId(),array(
					$this,"config_page"
				));

				//activeな時だけロード
				CMSPlugin::setEvent('onPageLoad'
					,$this->getId()
					,array($this,"onPageLoad")
					,array("filter" => "all")
				);	
			}
		}
	}
	
	function onPageLoad($args){
		$webPage = $args["webPage"];
		
		if(!defined("SOYSHOP_WEBAPP")){
			define("SOYSHOP_WEBAPP", dirname(SOY2::RootDir()) . "/soyshop/webapp/");
		}
		
		SOYCMS_SOYShopPageModulePlugin::configure(array(
			"siteId" => $this->siteId,
			"rootDir" => SOYSHOP_WEBAPP . "src/"
		));
		SOYCMS_SOYShopPageModulePlugin::prepare(true);
		
		//定数の準備
		if(!defined("SOYSHOP_SITE_DIRECTORY")){
			define("SOYSHOP_SITE_DIRECTORY", $_SERVER["DOCUMENT_ROOT"] . "/" . $this->siteId);
		}
		
		
		//プラグインの実行
		$plugin = new SOYCMS_SOYShopPageModulePlugin();
		$webPage->executePlugin("module","[a-zA-Z0-9\.\_]+",$plugin);
		
		//戻す
		SOYCMS_SOYShopPageModulePlugin::tearDown();
	}

	/**
	 * 設定画面
	 */
	function config_page($message){
		
		include_once(dirname(__FILE__) . "/config/SOYShopConnectorConfigPage.class.php");
		$form = SOY2HTMLFactory::createInstance("SOYShopConnectorConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
/**		
		if(isset($_POST["save"])){

			if(isset($_POST["siteId"])){
				$this->siteId = $_POST["siteId"];
			}
			
			CMSPlugin::savePluginConfig($this->getId(),$this);
			CMSPlugin::redirectConfigPage();
			exit;
		}
		
		ob_start();
		include_once(dirname(__FILE__) . "/config.php");
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
**/
	}
	
	public static function register(){
		
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj){
			$obj = new SOYShopConnectorPlugin();
		}
			
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
	
	function getSiteId(){
		return $this->siteId;
	}
	
	function setSiteId($siteId){
		$this->siteId = $siteId;
	}
}


class SOYCMS_SOYShopPageModulePlugin extends PluginBase{

	protected $_soy2_prefix = "shop";
	
	public static function configure($array = null){
		static $_config = array();
		if($array){
			$_config = $array;
		}
		
		return $_config;
	}
	
	public static function prepare($isFirst = false){
		
		$old_dao_dir = SOY2DAOConfig::DaoDir();
		$old_entity_dir = SOY2DAOConfig::EntityDir();
		$old_dsn = SOY2DAOConfig::Dsn();
		$old_user = SOY2DAOConfig::user();
		$old_pass = SOY2DAOConfig::pass();
		
		$config = self::configure();
		
		$siteId = $config["siteId"];
		$confDir = SOYSHOP_WEBAPP . "conf/shop/";
	
		include_once($confDir . $siteId . ".conf.php");
		
		SOY2DAOConfig::DaoDir($config["rootDir"] . "domain/");
		SOY2DAOConfig::EntityDir($config["rootDir"] . "domain/");
		
		if(defined("SOYSHOP_SITE_DSN")){
					
			SOY2DAOConfig::Dsn(SOYSHOP_SITE_DSN);
			SOY2DAOConfig::user(SOYSHOP_SITE_USER);
			SOY2DAOConfig::pass(SOYSHOP_SITE_PASS);
		//1.5.1以前のSQLite対応
		}else{
			$dsn = "sqlite:" . SOYSHOP_SITE_DIRECTORY . "/.db/sqlite.db";
			SOY2DAOConfig::Dsn($dsn);
		}
		
		$config["db_old"] = array(
			"dao_dir" => $old_dao_dir,
			"entity_dir" => $old_entity_dir,
			"dsn" => $old_dsn,
			"user" => $old_user,
			"pass" => $old_pass
		);
		
		//rootdir
		$config["old_rootdir"] = SOY2::RootDir();
		SOY2::RootDir($config["rootDir"]);
		
		//必須クラスはここで読み込む
		if($isFirst){
			SOY2::import("domain.config.SOYShop_DataSets");
			SOY2::import("logic.plugin.SOYShopPlugin");
			SOY2::import("logic.cart.CartLogic");
			SOY2::import("logic.mypage.MyPageLogic");
			SOY2::imports("base.*");
			SOY2::imports("base.func.*");
			SOY2::imports("domain.user.*");
			
			if(!defined("SOYSHOP_SITE_PREFIX"))define("SOYSHOP_SITE_PREFIX","cms");
			if(!defined("SOYSHOP_CURRENT_CART_ID")){
				$cartId = SOYShop_DataSets::get("config.cart.cart_id","bryon");
				define("SOYSHOP_CURRENT_CART_ID",$cartId);
			}
			if(!defined("SOYSHOP_CURRENT_MYPAGE_ID")){
				$mypageId = SOYShop_DataSets::get("config.mypage.id","bryon");
				define("SOYSHOP_CURRENT_MYPAGE_ID",$mypageId);
			}
		}
		
		self::configure($config);
	}
	
	public static function tearDown(){
		
		$config = self::configure();
		$db = $config["db_old"];
		
		SOY2DAOConfig::DaoDir($db["dao_dir"]);
		SOY2DAOConfig::EntityDir($db["entity_dir"]);
		SOY2DAOConfig::Dsn($db["dsn"]);
		SOY2DAOConfig::user($db["user"]);
		SOY2DAOConfig::pass($db["pass"]);
		
		//rootdir
		SOY2::RootDir($config["old_rootdir"]);
		
	}

	function execute(){
		$soyValue = $this->soyValue;

		$array = explode(".",$soyValue);
		if(count($array)>1){
			unset($array[0]);
		}
		$func = "soyshop_" . implode("_",$array);

		$modulePath = SOYSHOP_SITE_DIRECTORY . "/.module/" . str_replace(".","/",$soyValue) . ".php";
		
		$this->setInnerHTML('<?php SOYCMS_SOYShopPageModulePlugin::prepare(); ob_start(); ' .
						'if(file_exists("'.$modulePath.'")){include_once("'.$modulePath.'");}else{@SOY2::import("module.site.'.$soyValue.'",".php");} ?>'.
						$this->getInnerHTML().'' .
						'<?php $tmp_html=ob_get_contents();ob_end_clean(); '.
						'if(function_exists("'.$func.'")){echo call_user_func("'.$func.'",$tmp_html,$this);}else{ echo "function not found : '.$func.'";} SOYCMS_SOYShopPageModulePlugin::tearDown(); ?>');
	}

}
?>