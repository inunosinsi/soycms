<?php

class SOYShop_UserPage extends SOYShopPageBase{

	private $myPageId;
	private $args;

	function __construct($args){
		$this->setMyPageId($args[0]);
		$this->setArgs($args[1]);

		parent::__construct();

	}

	function doOperation(){

	}

	function checkSSL(){
		$isUseSSL = SOYShop_DataSets::get("config.mypage.use_ssl", 0);

		if($isUseSSL && !isset($_SERVER["HTTPS"])){
			//携帯の端末がDoCoMoだった場合セッションIDを入れる
			//auはCookieのセッションIDの有無にかかわらず常にセッションIDを付ける
			$param = "";
			if(
				defined("SOYSHOP_IS_MOBILE") && SOYSHOP_IS_MOBILE
				&& defined("SOYSHOP_MOBILE_CARRIER")
				&&
				(
				  SOYSHOP_MOBILE_CARRIER == "DoCoMo" && !isset($_COOKIE[session_name()])
				  ||
				  SOYSHOP_MOBILE_CARRIER == "KDDI"
				)
				&& isset($_GET[session_name()])
			){
				$param = "?".session_name() . "=" . session_id();
			}

			$args = implode($this->args,"/");
			SOY2PageController::redirect(soyshop_get_mypage_url() . "/" . $args.$param);
			exit;
		}
	}

	function common_execute(){
		$this->checkSSL();

		SOYShopPlugin::load("soyshop.mypage");
        $canonical = SOYShopPlugin::invoke("soyshop.mypage", array(
			"mode" => "canonical"
		))->getCanonicalUrl();
		if(!empty($canonical)){
			$this->getHeadElement()->appendHTML(
                '<link rel="canonical" href="' . $canonical . '" />' . "\n"
            );
        }

		$this->buildModules();
		//マイページのタイトルフォーマットで置換文字列を使用
		$this->setTitle(soyshop_get_mypage_page_title($this->args));

	}

	function display(){
		ob_start();
    	parent::display();
    	$html = ob_get_contents();
    	ob_end_clean();

    	if(defined("SOYSHOP_IS_MOBILE") && SOYSHOP_IS_MOBILE){
    		$charset = SOYShop_DataSets::get("config.mypage.mobile.charset","Shift_JIS");
    	}elseif(defined("SOYSHOP_IS_SMARTPHONE") && SOYSHOP_IS_SMARTPHONE){
    		$charset = SOYShop_DataSets::get("config.mypage.smartphone.charset","UTF-8");
    	}else{
    		$charset = SOYShop_DataSets::get("config.mypage.charset","UTF-8");
    	}

    	echo mb_convert_encoding($html,$charset,"UTF-8");
	}

	function getTemplateFilePath(){
		$templateDir = SOYSHOP_SITE_DIRECTORY . ".template/mypage/";

		//隠し機能 ログインしていない時に$this->getMyPageId() . "_no_login.ini"があればそちらを見る
		if(!MyPageLogic::getMyPage()->getIsLoggedin() && file_exists($templateDir . $this->getMyPageId() . "_no_login.ini")){
			return $templateDir . $this->getMyPageId() . "_no_login.html";
		}

		return $templateDir . $this->getMyPageId() . ".html";
    }

    /**
	 * キャッシュファイルのパス
	 *
	 * @return キャッシュファイルのパス
	 */
	function getCacheFilePath($extension = ".html.php"){
		return
			SOY2HTMLConfig::CacheDir(). SOY2HTMLConfig::getOption("cache_prefix") .
			"cache_" . get_class($this) .'_'. $this->myPageId .'_' . $this->getId() .'_'. $this->getParentPageParam() . md5($this->getClassPath().$this->getTemplateFilePath()) . SOY2HTMLConfig::Language() . $extension;
	}

    function getMyPageId() {
    	return $this->myPageId;
    }
    function setMyPageId($myPageId) {
    	$this->myPageId = $myPageId;
    }

    function getMyPage() {
    	return $this->myPage;
    }
    function setMyPage($myPage) {
    	$this->myPage = $myPage;
    }

    function getArgs() {
    	return $this->args;
    }
    function setArgs($args) {
    	$this->args = $args;
    }

    /**
     * @return String ページクラス
     */
    function createPagePath($indexPage = false){
    	$res = array();
    	$args = $this->getArgs();

    	//argsの整理。最後が数字の場合は配列から除く
		$argsCnt = count($args);
    	for($i = 0; $i < $argsCnt; ++$i){
    		if(is_numeric($args[$i])) unset($args[$i]);
    	}

    	$count = count($args);

    	for($i=0;$i<$count;++$i){

    		if(is_numeric($args[$i]))continue;//念の為、数字はスキップ

    		if($i == ($count-1) && !$indexPage){
    			$res[] = ucfirst($args[$i]);
    		}else{
    			$res[] = strtolower($args[$i]);
    		}
    	}

    	//IndexPage
    	if($indexPage)$res[] = "Index";

    	return implode(".",$res);
    }

    /**
     * @return Array(numeric)
     */
    function getPageArgs(){
    	$res = array();
    	$args = $this->getArgs();
    	$count = count($args);

    	for($i=0;$i<$count;++$i){
    		if(is_numeric($args[$i])){
    			$res[] = $args[$i];

    		}
    	}

    	return $res;
    }
}
