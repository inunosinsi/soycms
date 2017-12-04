<?php

class BonusDownloadConfigUtil {

	/* 購入特典タイプ */
	const TYPE_FILE = 1;//ダウンロードファイル管理
	const TYPE_TEXT = 2;//URL

	/* 購入特典ファイル */
	const UPLOAD_DIR = "download/_bonus_download";

	/* 公開状態 */
	const STATUS_INACTIVE = 0;//非公開、無効
	const STATUS_ACTIVE = 1;//公開、有効

	/**
	 * @return array() 購入特典の設定各種
	 */
	public static function getConfig(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		$config = array();
		$config["type"] = SOYShop_DataSets::get("bonus_download.type", BonusDownloadConfigUtil::TYPE_TEXT);
		$config["name"] = SOYShop_DataSets::get("bonus_download.name", "購入特典");
		$config["html"] = SOYShop_DataSets::get("bonus_download.html", null);
		$config["download_url"] = SOYShop_DataSets::get("bonus_download.url", null);
		$config["download_files"] = SOYShop_DataSets::get("bonus_download.download_files", array());
		$config["download_files.time_limit"] = SOYShop_DataSets::get("bonus_download.download_files.time_limit", null);
		$config["condition"] = SOYShop_DataSets::get("bonus_download.condition", array());
		$config["status"] = SOYShop_DataSets::get("bonus_download.status", BonusDownloadConfigUtil::STATUS_INACTIVE);
//		$config[""] = SOYShop_DataSets::get("bonus_download.", null);

		return $config;
	}

	/**
	 * 購入特典内容の設定
	 * @param array $config
	 */
	public static function setConfig($config){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		SOYShop_DataSets::put("bonus_download.type", $config["type"]);
		SOYShop_DataSets::put("bonus_download.name", $config["name"]);
		SOYShop_DataSets::put("bonus_download.html", $config["html"]);
		SOYShop_DataSets::put("bonus_download.url", $config["download_url"]);
		SOYShop_DataSets::put("bonus_download.download_files", $config["download_files"]);
		SOYShop_DataSets::put("bonus_download.download_files.time_limit", $config["download_files.time_limit"]);
		SOYShop_DataSets::put("bonus_download.status", $config["status"]);
	}

	/**
	 * @param SOYShop_Order $order
	 * @return array(1つの場合"ダウンロードURL"、複数の場合には"ファイル名")
	 */
	public static function getDownloadUrls($order){

	}


	/**
	 * @param SOYShop_Order $order
	 * @return array(1つの場合"ダウンロードURL"、複数の場合には"ファイル名")
	 */
	public static function generateDownloadUrls(SOYShop_Order $order=null){

		$config = BonusDownloadConfigUtil::getConfig();

		//URLの場合
		if($config["type"] == BonusDownloadConfigUtil::TYPE_TEXT){
			return array($config["download_url"]);
		}

		//アップロードファイルの場合
		if($config["type"] == BonusDownloadConfigUtil::TYPE_FILE && $order instanceof SOYShop_Order){
			$attr = $order->getAttribute("bonus_download.list");
			$tokens = $attr["value"];
			$tokens = explode("\n", $tokens);

			$list = array();
			foreach($tokens as $token){
				$list[] = BonusDownloadConfigUtil::getDownloadUrl($order, trim($token));
			}

			return $list;
		}

	}

	/**
	 * @param SOYShop_Order $order
	 * @param string $token
	 */
	public static function getDownloadUrl(SOYShop_Order $order, $token){
		$url = array();
		$url[] = SOYSHOP_SITE_URL. soyshop_get_mypage_uri() . "?soyshop_download=bonus_download";
		$url[] =  "&tn=". $order->getTrackingNumber();
		$url[] =  "&t=". $token;
		return implode($url);
	}

	function getMypagePath(){
		return SOYSHOP_SITE_URL.soyshop_get_mypage_uri() . "?soyshop_download=download_assistant&token=";
	}

	/**
	 * アップロードのディレクトリパス
	 */
	public static function getUploadDir(){
		$dir = SOYSHOP_SITE_DIRECTORY. self::UPLOAD_DIR. "/";
		if(!is_dir($dir)){
			@mkdir($dir, 0755);
		}

		return $dir;
	}

	/**
	 * アップロードしたファイル
	 * @param array $selected 選択された場合
	 * @return array(array("name", "filesize"))
	 */
	public static function getBonusFiles($selected = null){
		$list = array();

		$dir = BonusDownloadConfigUtil::getUploadDir();

		if(!file_exists($dir))return $list;
		$files = opendir($dir);

		if(!is_resource($files))return $list;

		while($file = readdir($files)){
			if(BonusDownloadConfigUtil::checkFileType($file)){

				//選択されたファイルのみの場合
				if(is_null($selected) || (is_array($selected) && in_array($file, $selected))){
					$info = array();
					$info["name"] = $file;
					$info["filesize"] = BonusDownloadConfigUtil::getFileSize(filesize($dir. $file));
					$list[] = $info;
				}
			}

		}

		return $list;
	}

	//登録可能な拡張子
	public static function getAllowExtension(){
		$allow = array();
		$allow[".zip"] = "application/zip";
		$allow[".epub"] = "application/epub+zip";
		$allow[".pdf"] = "application/pdf";
		$allow[".mp3"] = "audio/mpeg";
		$allow[".mp4"] = "application/mp4";

		return $allow;
	}
	/**
	 * 登録するファイルの拡張子をチェック
	 * @param string
	 * @return boolean
	 */
	public static function checkFileType($file){
		$flag = false;
		$allowes = BonusDownloadConfigUtil::getAllowExtension();
		foreach($allowes as $key => $value){
			if(preg_match('/' . $key . '$/', $file)){
				$flag = true;
				break;
			}
		}
		return $flag;
	}

	/**
	 * ファイルサイズをhに
	 * @param integer $size
	 * @return string ファイルサイズ
	 */
	public static function getFileSize($size){
		$sizes = array('Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
		 $ext = $sizes[0];

    	for ($i=1; (($i <count($sizes)) && ($size>= 1024)); $i++) {
    	   	$size = $size / 1024;
        	$ext = $sizes[$i];
    	}
    	return round($size, 2) . $ext;
	}

	/**
	 * @param integer $userId ユーザID
	 * @param integer $i 何個目か
	 * @return string トークン
	 */
	public static function generateToken($orderId, $userId, $i){
		return md5(time(). $orderId. $userId. $i. rand(0, 65535));
	}

	/**
	 * 有効期限のタイムスタンプ取得
	 * @param array $config
	 * @return integer || null
	 */
	public static function getTimelimit($config){
		$limit = null;

		if(isset($config["download_files.time_limit"]) && !is_null($config["download_files.time_limit"]) && is_numeric($config["download_files.time_limit"])){
			$limit = time() + $config["download_files.time_limit"] * 60 * 60 * 24;
		}

		return $limit;
	}

	/**
	 * 購入特典が付いている注文を取得
	 * @param integer $id 注文ID
	 * @param string $trackingNumber 注文のトラッキングナンバー
	 * @return SOYShop_Order
	 */
	public static function getBonusOrder($id=null, $trackingNumber=null){
		$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");

		try{

			//注文IDで取得
			if(!is_null($id)){
				$order = $orderDao->getById($id);
			}

			//トラッキングナンバーで取得
			if(!is_null($trackingNumber)){
				$order = $orderDao->getByTrackingNumber($trackingNumber);
			}

		}catch(Exception $e){
			$order = new SOYShop_Order();
		}

		return $order;
	}

	/**
	 * @param SOYShop_Order $order
	 * @param integer $id orderAttributeのid
	 * @param string $name orderAttributeのname 項目名
	 * @param string $value orderAttributeのname 値
	 * @param string $hidden orderAttributeのhidden マイページの非表示フラグ
	 * @return SOYShop_Order
	 */
	public static function setOrderAttribute($order, $id, $name, $value, $hidden=false){

		$attr = array(
			"name" => $name,
			"value" => $value,
			"hidden" => $hidden,
		);

		$order->setAttribute($id, $attr);
		return $order;
	}

	/**
	 * @param SOYShop_Order $order
	 * @param string $key orderAttributeのkey
	 * @return string orderAttributeのvalue
	 */
	public static function getOrderAttribute($order, $key){
		$attr = $order->getAttribute($key);
		return isset($attr["value"]) ? $attr["value"] : null;
	}

	/**
	 * @param SOYShop_Order $order
	 * @param string $key orderAttributeのkey
	 * @return string orderAttributeのvalue
	 */
	public static function getListOrderAttribute($order, $key){
		$attr = $order->getAttribute($key);
		$value = isset($attr["value"]) ? $attr["value"] : null;
		$value = explode("\n", $value);

		$list = array();
		foreach($value as $line){
			$list[] = trim($line);
		}

		return $list;
	}

}
