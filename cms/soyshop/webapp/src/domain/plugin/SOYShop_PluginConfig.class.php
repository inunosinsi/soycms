<?php
/**
 * @table soyshop_plugins
 */
class SOYShop_PluginConfig {

	const PLUGIN_ACTIVE = 1;
	const PLUGIN_INACTIVE = 0;

	const DISPLAY_ORDER_MAX = 2147483647;
	const DISPLAY_ORDER_MIN = 0;

	const MODE_INSTALLED = "installed";
	const MODE_ALL = "all";

	//プラグイン一覧を見やすくするための定数、下で配列を生成
	const PLUGIN_TYPE_ARRIVAL = "arrival";
	const PLUGIN_TYPE_ADMIN = "admin";
	const PLUGIN_TYPE_AFFILIATE = "affiliate";
	const PLUGIN_TYPE_AFFICODE = "afficode";
	const PLUGIN_TYPE_ANALYTICS = "analytics";
	const PLUGIN_TYPE_API = "api";
	const PLUGIN_TYPE_BONUS = "bonus";
	const PLUGIN_TYPE_BUTTON = "button";
	const PLUGIN_TYPE_CART = "cart";
	const PLUGIN_TYPE_MYPAGE = "mypage";
	const PLUGIN_TYPE_CONNECTOR = "connector";
	const PLUGIN_TYPE_CSV = "csv";
	const PLUGIN_TYPE_DELIVERY = "delivery";
	const PLUGIN_TYPE_DISCOUNT = "discount";
	const PLUGIN_TYPE_DOWNLOAD = "download";
	const PLUGIN_TYPE_DUMMY = "dummy";
	const PLUGIN_TYPE_LIST = "list";
	const PLUGIN_TYPE_MAILBUILDER = "mailbuilder";
	const PLUGIN_TYPE_ORDER = "order";
	const PLUGIN_TYPE_PACKAGE = "package";
	const PLUGIN_TYPE_PARTS = "parts";
	const PLUGIN_TYPE_PAYMENT = "payment";
	const PLUGIN_TYPE_POINT = "point";
	const PLUGIN_TYPE_SEARCH = "search";
	const PLUGIN_TYPE_TAX = "tax";
	const PLUGIN_TYPE_TICKET = "ticket";
	const PLUGIN_TYPE_USER = "user";
	const PLUGIN_TYPE_UTIL = "util";
	const PLUGIN_TYPE_XML = "xml";
	const PLUGIN_TYPE_DEV = "dev";


	/**
	 * @id
	 */
	private $id;

	/**
	 * @column plugin_id
	 */
	private $pluginId;

	/**
	 * @column plugin_type
	 */
	private $type;

	/**
	 * @column config
	 */
	private $config;

	/**
	 * @column display_order
	 */
	private $displayOrder = 2147483647;

	/**
	 * @column is_active
	 */
	private $isActive;

    /**
     * @no_persistent
     */
    private $description = "";

    /**
     * @no_persistent
     */
    private $name;

    /**
     * @no_persistent
     */
	private $version;

	/**
	 * @no_persistent
	 */
	private $link;

	/**
	 * @no_persistent
	 */
	private $label;

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getPluginId() {
		return $this->pluginId;
	}
	function setPluginId($pluginid) {
		$this->pluginId = $pluginid;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}
	function getDisplayOrder(){
		return $this->displayOrder;
	}
	function setDisplayOrder($displayOrder){
		$this->displayOrder = $displayOrder;
	}
	function getIsActive() {
		return $this->isActive;
	}
	function setIsActive($active = 1) {
		$this->isActive = $active;
	}

	/**
	 * @return boolean
	 */
	function isActive(){
		return (boolean)$this->getIsActive();
	}

	function SOYShopModule() {

    }
    function getDescription() {
    	if($this->description == "")
    		$this->loadFromIniFile();
    	return $this->description;
    }
    /**
     * iniファイルから読み込み！
     */
    function loadFromIniFile() {
    	$inifile = SOYSHOP_MODULE_DIR . "features/" . $this->pluginId . "/module.ini";

    	//ファイル見つからないときはfalseを返す
    	if(is_readable($inifile)){
	    	$data = parse_ini_file($inifile);
	    	$this->name = $data["name"];
	    	$this->description = $data["description"];
	    	if(isset($data["version"])){
	    		$this->version = $data["version"];
	    	}
	    	if(isset($data["link"])){
	    		$this->link = $data["link"];
	    	}
	    	if(isset($data["label"])){
	    		$this->label = $data["label"];
	    	}
	    	return true;
    	}else{
    		return false;
    	}
    }

    /**
     * スクリプトファイル読み込み
     */
    function load($extensionId = null){
    	if(!$extensionId) $extensionId = $this->pluginId;

    	//拡張も可能
    	$ext_script = SOYSHOP_SITE_DIRECTORY . ".pluigins/" . $this->pluginId . "/" . $extensionId . ".php";
    	if(file_exists($ext_script)){
    		include_once($ext_script);
    		return;
    	}

    	if(defined("SOYSHOP_MODULE_DIR")){
    		$script = SOYSHOP_MODULE_DIR . "plugins/" . $this->pluginId."/" . $extensionId . ".php";
	    	if(file_exists($script)){
	    		include_once($script);
	    	}
    	}
    }

    function getName() {
    	if($this->name == "")
    		$this->loadFromIniFile();
    	return $this->name;
    }
    function setName($name) {
    	$this->name = $name;
    }

    function getVersion(){
    	if($this->version == "")
    		$this->loadFromIniFile();
    	return $this->version;
    }
    function setVersion($version){
    	$this->version = $version;
    }

    function getLink(){
    	if($this->link == "")
    		$this->loadFromIniFile();
    	return $this->link;
    }
    function setLink($link){
    	$this->link = $link;
    }

    function getLabel(){
    	if($this->label == "")
    		$this->loadFromIniFile();
    	return $this->label;
    }
    function setLabel($label){
    	$this->label = $label;
    }

    public static function getPluginTypeList(){
    	return array(
    		self::PLUGIN_TYPE_ARRIVAL => "新着",
    		self::PLUGIN_TYPE_ADMIN => "管理画面の拡張",
			self::PLUGIN_TYPE_AFFILIATE => "アフィリエイト",
			self::PLUGIN_TYPE_AFFICODE => "アフィコード",
			self::PLUGIN_TYPE_ANALYTICS => "統計",
			self::PLUGIN_TYPE_API => "API",
			self::PLUGIN_TYPE_BONUS => "購入特典",
			self::PLUGIN_TYPE_BUTTON => "SNS",
			self::PLUGIN_TYPE_CART => "カート周り",
			self::PLUGIN_TYPE_MYPAGE => "マイページ周り",
			self::PLUGIN_TYPE_CONNECTOR => "SOY App連携",
			self::PLUGIN_TYPE_CSV => "CSV",
			self::PLUGIN_TYPE_DELIVERY => "配送モジュール",
			self::PLUGIN_TYPE_DISCOUNT => "ディスカウント",
			self::PLUGIN_TYPE_DOWNLOAD => "ダウンロード販売",
			self::PLUGIN_TYPE_DUMMY => "ダミープラグイン",
			self::PLUGIN_TYPE_LIST => "商品一覧モジュール",
			self::PLUGIN_TYPE_MAILBUILDER => "メールビルダー",
			self::PLUGIN_TYPE_ORDER => "注文詳細",
			self::PLUGIN_TYPE_PACKAGE => "パッケージ",
			self::PLUGIN_TYPE_PARTS => "部品",
			self::PLUGIN_TYPE_PAYMENT => "支払モジュール",
			self::PLUGIN_TYPE_POINT => "ポイント",
			self::PLUGIN_TYPE_SEARCH => "検索モジュール",
			self::PLUGIN_TYPE_TAX => "消費税",
			self::PLUGIN_TYPE_TICKET => "チケット",
			self::PLUGIN_TYPE_UTIL => "ユーティリティー",
			self::PLUGIN_TYPE_USER => "顧客管理",
			self::PLUGIN_TYPE_XML => "XML",
			self::PLUGIN_TYPE_DEV => "開発"
    	);
    }
}
