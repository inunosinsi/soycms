<?php

class SiteConfig {

	const CHARSET_UTF_8 = 1;
	const CHARSET_SHIFT_JIS = 2;
	const CHARSET_EUC_JP = 3;

	private $name;
    private $siteConfig;
    private $charset;
    private $description;

	/**
	 * 設定の入った配列がserializeされた文字列を返す
	 */
    function getSiteConfig(){
    	return $this->siteConfig;
    }
    function setSiteConfig($config){
    	//$this->siteConfigには常にserializeされた文字列が入る
    	if(is_string($config)){
    		$this->siteConfig = $config;
    	}else{
    		$this->siteConfig = serialize($config);
    	}
    }
    function getCharset() {
    	return $this->charset;
    }
    function setCharset($charset) {
    	$this->charset = $charset;
    }
    function getName() {
    	return $this->name;
    }
    function setName($name){
    	$this->name = $name;
    }
    function getDescription() {
    	return $this->description;
    }
    function setDescription($description) {
    	$this->description = $description;
    }

    /**
     * 設定の配列を返す
     */
    public function getSiteConfigArray(){
    	if(strlen($this->siteConfig) && strpos($this->siteConfig, "a:") === 0 && ( $config = unserialize($this->siteConfig) ) !== false){
    		return $config;
    	}else{
    		return array();
    	}
    }
    /**
     * 設定値を返す
     */
    public function getConfigValue($key){
    	$config = $this->getSiteConfigArray();
    	if(is_array($config) && isset($config[$key])){
    		return $config[$key];
    	}else{
    		//値が見つからないとき
    		return false;
    	}
    }
    /**
     * 設定値を保持する
     */
    public function setConfigValue($key, $value){
    	$config = $this->getSiteConfigArray();
    	$config[$key] = $value;
    	$this->setSiteConfig($config);
    }

   	/**
   	 * 最終更新時刻を設定
   	 */
    function notifyUpdate(){
    	$this->setConfigValue("udate", time());
    }

    /**
     * 最終更新時刻を取得
     */
    function getLastUpdateDate(){
    	$udate = $this->getConfigValue("udate");
    	if($udate !== false){
    		return $udate;
    	}else{
    		return strtotime(date("Y-m-d 00:00:00"));
    	}
    }

    /**
     * 日付毎にディレクトリを作成するかどうか
     */
    function isCreateDefaultUploadDirectory(){
    	return (boolean)$this->getConfigValue("createUploadDirectoryByDate");
    }

    /**
     * 日付毎にディレクトリを作成するかどうかのフラグを保存
     */
    function setCreateUploadDirectoryByDate($value){
    	$this->setConfigValue("createUploadDirectoryByDate", (int)$value);
    }

    /**
     * 管理側にログインしている時のみ表示するかどうか
     */
    function isShowOnlyAdministrator(){
    	return (boolean)$this->getConfigValue("isShowOnlyAdministrator");
    }

    /**
     * 日付毎にディレクトリを作成するかどうかのフラグを保存
     */
    function setIsShowOnlyAdministrator($value){
    	$this->setConfigValue("isShowOnlyAdministrator", (int)$value);
    }

    function getDefaultUploadDirectory(){
    	$dir = $this->getConfigValue("upload_directory");
    	if($dir === false){
    		return "/files";
    	}

    	// ディレクトリの遡行は許されない
    	$dir = str_replace("..","",$dir);

    	// /始まりを強制
    	if($dir[0] != '/'){
    		$dir = '/'.$dir;
    	}

    	// 末尾の/は全て削除
    	while(substr($dir,-1) == '/'){
    		$dir = substr($dir,0,-1);
    	}

    	// /の連続は削除
    	while(strpos($dir, "//") !== false){
    		$dir = strtr($dir, array("//" => "/"));
    	}

    	return $dir;
    }

	/**
	 * 記事投稿時のイメージの挿入の設定
	 */
	function getDefaultUploadMode(){
		$v = (int)$this->getConfigValue("uploadMode");
		if($v === 0) $v = 1;
		return $v;
	}
	function setDefaultUploadMode($mode){
		$this->setConfigValue("uploadMode", $mode);
	}

    /**
     * アップロードディレクトリを作成して取得
     */
    function getUploadDirectory(){
    	$dir = $this->getDefaultUploadDirectory();


    	//日付別ディレクトリ
    	if($this->isCreateDefaultUploadDirectory()){
			SOY2::import("util.CMSFileManager");

    		$targetDir = UserInfoUtil::getSiteDirectory() . $dir . "/" . date("Ymd");
    		$targetUrl = $dir . "/" . date("Ymd");

    		//存在しなかったら作成する
    		if(!file_exists($targetDir)){
    			$res = @mkdir($targetDir);
    			if(!$res)return $dir;	//作成に失敗したら$dir

    			@chmod($targetDir, 0777);

    			//ファイルDBに追加
    			CMSFileManager::add($targetDir);
    		}

    		//ファイルDBになかったら追加する
    		try{
    			CMSFileManager::get($targetDir,$targetDir);
    		}catch(Exception $e){
    			CMSFileManager::add($targetDir);
    		}

    		if(file_exists($targetDir) && is_writable($targetDir)){
    			return $targetUrl;
    		}
    	}

    	return $dir;
    }

    function setDefaultUploadDirectory($dir){
    	$this->setConfigValue("upload_directory", $dir);

    	//正規化
    	$this->setConfigValue("upload_directory", $this->getDefaultUploadDirectory());
    }

		function getDefaultUploadResizeWidth(){
    	return $this->getConfigValue("resize_width");
    }

    function setDefaultUploadResizeWidth($w){
    	$this->setConfigValue("resize_width", $w);
    }

    /**
     * 文字コード変換
     * (UTF-8→サイトの文字コード)
     */
    function convertToSiteCharset($contents){
    	switch($this->charset){
    		case SiteConfig::CHARSET_UTF_8:
    			break;
    		case SiteConfig::CHARSET_SHIFT_JIS:
    			$contents = mb_convert_encoding($contents,'SJIS-win','UTF-8');
    			break;
    		case SiteConfig::CHARSET_EUC_JP:
    			$contents = mb_convert_encoding($contents,'eucJP-win','UTF-8');
    			break;
    		default:
    			break;
    	}
    	return $contents;
    }

    function getCharsetText(){
    	switch($this->charset){
    		case SiteConfig::CHARSET_UTF_8:
    			return "UTF-8";
    			break;
    		case SiteConfig::CHARSET_SHIFT_JIS:
    			return "Shift_JIS";
    			break;
    		case SiteConfig::CHARSET_EUC_JP:
    			return "EUC-JP";
    			break;
    		default:
    			break;
    	}
    }

    /**
     * 文字コード変換
     * (サイトの文字コード→UTF8)
     */
    function convertFromSiteCharset($contents){
    	switch($this->charset){
    		case SiteConfig::CHARSET_UTF_8:
    			break;
    		case SiteConfig::CHARSET_SHIFT_JIS:
    			$contents = mb_convert_encoding($contents,'UTF-8','SJIS-win');
    			break;
    		case SiteConfig::CHARSET_EUC_JP:
    			$contents = mb_convert_encoding($contents,'UTF-8','eucJP-win');
    			break;
    		default:
    			break;
    	}
    	return $contents;
    }

    public static function getCharsetLists(){
    	return array(
    		SiteConfig::CHARSET_UTF_8     => "UTF-8",
    		SiteConfig::CHARSET_SHIFT_JIS => "Shift_JIS",
    		SiteConfig::CHARSET_EUC_JP    => "EUC-JP"
    	);
    }

    /**
     * 日付毎にディレクトリを作成するかどうか
     */
    public function useLabelCategory(){
    	return (boolean)$this->getConfigValue("useLabelCategory");
    }
    /**
     * 日付毎にディレクトリを作成するかどうかのフラグを保存
     */
    public function setUseLabelCategory($value){
    	$this->setConfigValue("useLabelCategory", (int)$value);
    }

}
?>
