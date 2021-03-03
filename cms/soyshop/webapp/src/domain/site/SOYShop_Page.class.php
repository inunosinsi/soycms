<?php
SOY2::imports("domain.site.page.*");

/**
 * @table soyshop_page
 */
class SOYShop_Page {

	const TYPE_COMPLEX = "complex";
	const TYPE_LIST = "list";
	const TYPE_DETAIL = "detail";
	const TYPE_FREE = "free";
	const TYPE_SEARCH = "search";
	const TYPE_MEMBER = "member";
	const TYPE_CART = "cart";
	const TYPE_MYPAGE = "mypage";

	const URI_HOME = "_home";
	const NOT_FOUND = "_404_not_found";
	const MAINTENANCE = "_maintenance";

	/**
	 * @id
	 */
    private $id;

    private $name;

    private $uri;

    private $type = SOYShop_Page::TYPE_LIST;

	private $template = "default.html";

	private $config;

	/**
	 * @no_persistent
	 */
	private $object;

	/**
	 * @column create_date
	 */
	private $createDate;

	/**
	 * @column update_date
	 */
	private $updateDate;

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getUri() {
		return $this->uri;
	}
	function setUri($uri) {
		if(strlen($uri) > 0 && $uri[0] == "/") $uri = substr($uri, 1);
		$this->uri = $uri;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getTemplate() {
		return $this->template;
	}
	function setTemplate($template) {
		$this->template = $template;
	}
	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		if(is_array($config))$config = soy2_serialize($config);
		$this->config = $config;
	}
	function getObject() {
		if(empty($this->object)){
			$filepath = SOYSHOP_SITE_DIRECTORY . ".page/" . $this->getCustomClassName() . ".conf";
			if(file_exists($filepath)){
				$plain = json_decode(file_get_contents($filepath));
				$this->object = SOY2::cast($this->getPageObjectClassName(), $plain);
			}
		}
		return $this->object;
	}
	function setObject($object) {
		$this->object = $object;
	}
	function getCreateDate() {
		return $this->createDate;
	}
	function setCreateDate($createDate) {
		$this->createDate = $createDate;
	}
	function getUpdateDate() {
		return $this->updateDate;
	}
	function setUpdateDate($updateDate) {
		$this->updateDate = $updateDate;
	}

	/**
	 * unserilze config object
	 */
	function getConfigObject(){
		$obj = soy2_unserialize($this->getConfig());
		return $obj;
	}

	/* config method */
	function getDescription(){
		$obj = $this->getConfigObject();
		return (isset($obj["description"])) ? $obj["description"] :"";
	}

	function getKeyword(){
		$obj = $this->getConfigObject();
		return (isset($obj["keyword"])) ? $obj["keyword"] : "";
	}

	function getCanonicalFormat(){
		$obj = $this->getConfigObject();
		return (isset($obj["canonical_format"])) ? $obj["canonical_format"] : "";
	}

	function getTitleFormat(){
		$obj = $this->getConfigObject();
		return (isset($obj["title_format"]) && strlen($obj["title_format"]) > 0) ? $obj["title_format"] : "%SHOP_NAME% - %PAGE_NAME%";
	}

	function getCharset(){
		$obj = $this->getConfigObject();
		return (isset($obj["charset"])) ? $obj["charset"] : "UTF-8";
	}

	/**
	 * ページオブジェクトを取得s
	 */
	function getPageObject(){
		$page = $this->getObject();

		if($page) $page->setPage($this);

		if(!$page){
			$class = $this->getPageObjectClassName();
			$page = new $class();
			$page->setPage($this);
		}

		//title format
		$page->setTitleFormat($this->getTitleFormat());

		return $page;
	}

	/**
	 * setter page object
	 */
	function setPageObject($page){
		$this->setObject($page);
	}

	/**
	 * ページObjectのクラス名を返す
	 */
	function getPageObjectClassName(){
		$classes = array(
			self::TYPE_COMPLEX => "SOYShop_ComplexPage",
			self::TYPE_LIST => "SOYShop_ListPage",
			self::TYPE_DETAIL => "SOYShop_DetailPage",
			self::TYPE_FREE => "SOYShop_FreePage",
			self::TYPE_SEARCH => "SOYShop_SearchPage",
			self::TYPE_MEMBER => "SOYShop_MemberPage"
		);

		return $classes[$this->type];
	}

	/* 以下 便利メソッド */

	/**
	 * タイトルフォーマットで変換したタイトルを取得
	 */
	function getConvertedTitle(){
		return $this->getPageObject()->getPageTitle();
	}

	function getConvertedCanonical(){
		$format = trim($this->getCanonicalFormat());
		if(!strlen($format)) return "";
		return str_replace("%PERMALINK%", $this->getCanonicalUrl(), $format);
	}

	function getCanonicalUrl(){
		$url = soyshop_get_site_url(true);
		if($this->getUri() != SOYSHOP_TOP_PAGE_MARKER){
			$url .= $this->getUri();
		}

		switch($this->getType()){
			case self::TYPE_LIST:
				switch($this->getPageObject()->getType()){
					case "category":
						$current = $this->getPageObject()->getCurrentCategory();
						if(method_exists($current, "getAlias") && isset($_SERVER["PATH_INFO"]) && strpos($_SERVER["PATH_INFO"], $current->getAlias()) !== false){
							$url .= "/" . rawurlencode($current->getAlias());
						}
						break;
				}
				break;
			case self::TYPE_DETAIL:
				$current = $this->getPageObject()->getCurrentItem();
				$url .= "/" . rawurlencode($current->getAlias());
				break;
			case self::TYPE_COMPLEX:
			case self::TYPE_FREE:
			default:
				break;
		}

		//スラッシュのみエンコードされた文字列を戻す
		if(strpos($url, "%2F")) $url = str_replace("%2F", "/", $url);

		//カノニカルURLの設定
		$url = rtrim($url, "/");

		//トレイリングスラッシュ
		preg_match('/.+\.(html|htm|php?)/i', $url, $tmp);
		if(!count($tmp) && (int)SOYShop_ShopConfig::load()->getIsTrailingSlash() === 1){
			 $url .= "/";
		}

		//wwwなし設定
		preg_match('/^https?:\/\/www\./', $url, $tmp);
		if(isset($tmp[0]) && (int)SOYShop_ShopConfig::load()->getIsDomainWww() === 0){
			$url = str_replace("//www.", "//", $url);
		}

		return $url;
	}


	function getCustomTemplateFilePath($withDir = true){
		$templateDir = ($withDir) ? SOYSHOP_SITE_DIRECTORY . ".template/" : "";
		$filename = $this->getUri();
		if(false === strpos($filename, ".html")) $filename .= ".html";	//.html
		return $templateDir . "custom/" . $filename;
	}

	function getTemplateFilePath(){
		$templateDir = SOYSHOP_SITE_DIRECTORY . ".template/";

		if(file_exists($this->getCustomTemplateFilePath(true))){
			return $this->getCustomTemplateFilePath(true);
		}

		return $templateDir . $this->getType() . "/" . $this->getTemplate();
	}

	/**
	 * オブジェクトの設定ファイル
	 */
	function getConfigFilePath(){
		return SOYSHOP_SITE_DIRECTORY . ".page/" . $this->getCustomClassName() . ".conf.php";
	}

	/**
	 * 自動挿入されるCSSのURLの取得
	 */
	function getCSSURL(){
		$url = SOYSHOP_SITE_URL;
		$uri = $this->getUri();

		$size = strlen ($uri);
    	$pos = strpos (strrev($uri), "/");

		$dir = ($pos) ? substr($uri, 0, $size - $pos) : "";
		$start = ($pos) ? $size - $pos : 0;

		$file = preg_replace('/\.html$/', "", substr($uri, $start)) . ".css";

		return $url . $dir . $file;
	}

	/**
	 * 実行するクラスファイル名
	 */
	function getCustomClassName(){
		$class = str_replace(array("-", "/", "."), "_", $this->getUri());
		return $class . "_page";
	}

	/**
	 * 実行するクラスファイルパス
	 */
	function getCustomClassFileName(){
		return $this->getCustomClassName() . ".php";
	}

	/**
	 * 実行するクラスファイルのベースクラス名
	 */
	function getBaseClassName(){
		return $this->getPageObjectClassName() . "Base";
	}

	/**
	 *
	 */
	function getWebPageObject($args){
		include(SOYSHOP_SITE_DIRECTORY . ".page/" . $this->getCustomClassFileName());
		return SOY2HTMLFactory::createInstance($this->getCustomClassName(), array(
			"arguments" => array("page" => $this, "arguments" => $args)
		));
	}

	function getTypeText(){
		$texts = self::getTypeTexts();
		return $texts[$this->getType()];
	}

	public static function getTypeTexts(){
		$texts = array(
			self::TYPE_COMPLEX => "ナビゲーションページ",
			self::TYPE_LIST => "商品一覧ページ",
			self::TYPE_DETAIL => "商品詳細ページ",
			self::TYPE_FREE => "フリーページ",
			self::TYPE_SEARCH => "検索結果ページ",
			self::TYPE_MEMBER => "会員詳細ページ"
		);
		return $texts;
	}

	/**
	 * テンプレート一覧を出力
	 */
	function getTemplateList(){
		$templateDir = SOYSHOP_SITE_DIRECTORY . ".template/" . $this->getType() . "/";

		$res = array();
		if(!is_dir($templateDir)) return array();

		$files = @scandir($templateDir);
		foreach($files as $file){
			if($file[0] == ".")continue;
			if(preg_match('/(.*)\.html$/', $file, $tmp)){

				if(file_exists($templateDir . $tmp[1] . ".ini")){
					$array = parse_ini_file($templateDir . $tmp[1] . ".ini");
					$res[$file] = (isset($array["name"]) && strlen($array["name"]) > 0) ? $array["name"] : $file;
				}
			}
		}

		return $res;
	}

	/**
	 * ページの初期化ファイルがあるか
	 */
	public static function checkPageListFile(){
		return (file_exists(SOY2::RootDir() . "logic/init/page/ini.csv"));
	}
}

/**
 * 各ページのベースクラス
 */
class SOYShop_PageBase{

	private $page;

	private $titleFormat;

	final function setPage($page){
		$this->page = $page;
	}

	final function getPage(){
		return $this->page;
	}

	final function getPageTitle(){
		return $this->convertPageTitle($this->page->getTitleFormat());
	}

	function convertPageTitle($title){
		return $title;
	}

	function getTitleFormat() {
		return $this->titleFormat;
	}
	function setTitleFormat($titleFormat) {
		$this->titleFormat = $titleFormat;
	}

	/**
	 * タイトルフォーマットの説明文
	 */
	function getTitleFormatDescription(){
		return "";
	}

	/**
	 * Keyword formatの説明文
	 */
    function getKeywordFormatDescription(){
    	return "";
    }

    /**
	 * Description formatの説明文
	 */
    function getDescriptionFormatDescription(){
    	return "";
    }

	/**
	 * Canonical formatの説明文
	 */
    function getCanonicalFormatDescription(){
    	$html = array();
		$html[] = "<table style=\"margin-top:5px;\">";
    	$html[] = "<tr><td>表示されるページのURL：</td><td><strong>%PERMALINK%</strong></td></tr>";
		$html[] = "</table>";
    	return implode("\n", $html);
    }

	/**
	 * フォーマットが共通の時
	 */
	function getCommonFormat(){
		$html = array();
		$html[] = "<table style=\"margin-top:5px;\">";
    	$html[] = "<tr><td>ショップ名：</td><td><strong>%SHOP_NAME%</strong></td></tr>";
    	$html[] = "<tr><td>ページ名：</td><td><strong>%PAGE_NAME%</strong></td></tr>";
		$html[] = "</table>";
    	return implode("\n", $html);
	}
}
