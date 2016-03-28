<?php
/**
 * im.inc.php
 * 画像のリサイズ処理を行なう
 * サイトディレクトリ直下のim.phpから呼び出されるスクリプト
 */

	/*
	 * 設定
	 */

	//PHPの設定
	include_once(dirname(__FILE__)."/config/php.config.php");
	include_once("lib/magic_quote_gpc.php");

	//設定ファイルのinclude
	if(file_exists(dirname(__FILE__)."/config/custom.config.php")){
		//開発用orカスタマイズ用設定ファイル
		include_once(dirname(__FILE__)."/config/custom.config.php");
	}else{
		//標準設定ファイル
		include_once("soycms.config.php");
	}

	//ユーザの設定ファイル
	if(file_exists(dirname(__FILE__)."/config/user.config.php")){
		include_once(dirname(__FILE__)."/config/user.config.php");
	}


	//リサイズ後の画像の保存先
	if(!defined("SOYCMS_RESIZE_IMAGE_CACHE_DIR"))define("SOYCMS_RESIZE_IMAGE_CACHE_DIR", $site_root."/.cache/soy_resize");
	//リサイズ制限（サイズが大きいとメモリーをより多く消費します）：これより大きい指定は無視します
	if(!defined("SOYCMS_RESIZE_IMAGE_RESIZE_LIMIT"))define("SOYCMS_RESIZE_IMAGE_RESIZE_LIMIT", 1000);
	//最大値（とりあえず幅指定のみ対応）
	//SOYCMS_RESIZE_IMAGE_MAX_WIDTH

	/*
	 * 基本の値
	 */
	$src       = isset($_GET["src"])    ? $_GET["src"]    : null ;
	$width     = isset($_GET["width"])  && strlen($_GET["width"])  && is_numeric($_GET["width"])  ? (int)$_GET["width"]   : null ;
	$height    = isset($_GET["height"]) && strlen($_GET["height"]) && is_numeric($_GET["height"]) ? (int)$_GET["height"]  : null ;
	$site_root = $site_root;// dirname(__FILE__) in im.php
	$isLocal   = strncasecmp($src,"http",4)!==0;

	//最大幅指定
	$max_width = isset($_GET["max_width"])&& strlen($_GET["max_width"])  && is_numeric($_GET["max_width"]) ? (int)$_GET["max_width"] : null ;
	if(defined("SOYCMS_RESIZE_IMAGE_MAX_WIDTH")) $max_width = SOYCMS_RESIZE_IMAGE_MAX_WIDTH;
	$max_width = is_numeric($max_width) && $max_width >0 ? $max_width : null ;

	/*
	 * 入力値のチェック
	 */
	//ファイルが空なら404
	if(strlen($src)<1){
		_not_found();
	}
	//幅も高さも指定がないなら何もしない
	if(is_null($width) && is_null($height) && is_null($max_width)){
		_redirect();
	}
	//幅か高さが0なら何もしない
	if( $width===0 || $height===0 ){
		_redirect();
	}

	//画像の所在
	if(!$isLocal){
		//httpsならhttpに変えておく
		if(strncasecmp($src,"https://",8)!==0){
			$src = strtr($src,array("https://"=>"http://"));
		}

		//同じサーバーのファイル？
		if( strpos($src, "http://".$_SERVER["SERVER_NAME"]."/") === 0 ){
			$file = $_SERVER["DOCUMENT_ROOT"].substr($src, strlen("http://".$_SERVER["SERVER_NAME"]));
		}
		if( strpos($src, "https://".$_SERVER["SERVER_NAME"]."/") === 0 ){
			$file = $_SERVER["DOCUMENT_ROOT"].substr($src, strlen("https://".$_SERVER["SERVER_NAME"]));
		}

		if(isset($file) && is_readable($file)){
			$file = realpath($file);
			$isLocal = true;
		}else{
			//外部のファイルも取得できるならリサイズする
			if(ini_get("allow_url_fopen")){
				ini_set("default_socket_timeout",10);
				$file = $src;
			}else{
				_redirect();
			}
		}

	}else{
		if($src[0] == "/"){
			$src = $_SERVER["DOCUMENT_ROOT"].$src;
		}else{
			//サイトディレクトリからの相対パスにする
			$src = "./" . $src;//$site_root."/".$src
		}

		$file = realpath($src);

		if(!is_readable($file) || strpos($file, realpath($_SERVER["DOCUMENT_ROOT"]))!==0){
			_not_found();
		}
	}


	/**
	 * ここまでは読み込むべきファイルにアクセスができない場合は 404 Not Found とする
	 * ここから変換処理に失敗する場合は 304 で元のファイルにリダイレクトする
	 */


	//画像タイプ判定
	switch(_get_extension($file)){
		case "jpeg":
		case "jpg":
			$data = array(
						"mime"=>'Content-Type: image/jpeg',
					);
			break;
		case "png":
			$data = array(
						"mime"=>'Content-Type: image/jpeg',
					);
			break;
		case "gif":
			$data = array(
						"mime"=>'Content-Type: image/gif',
					);
			break;
		default://対応していない画像は何もしない
			_redirect();
	}

	//キャッシュURL
	if($isLocal){
		$cache_file = md5($file . "_" . filemtime($file) . "_" . filesize($file) . "_" .$width . "_" .$height ."_". $max_width);
	}else{
		$cache_file = md5($file . "___" .$width . "_" .$height ."_". $max_width);
	}
	$cache_dir  = SOYCMS_RESIZE_IMAGE_CACHE_DIR ."/" .$cache_file[0].$cache_file[1] ."/". $cache_file[2].$cache_file[3];
	if(!file_exists($cache_dir."/.") && !mkdir($cache_dir, 0777, true)){
		_redirect();
	}
	$cache = $cache_dir . "/" . $cache_file;

	//すでにキャッシュがあればそれを返す（$_GET["force"]があればキャッシュを無視して再変換）
	if(is_readable($cache) && !isset($_GET["force"])){
		/* 変更がなければ304を返す（If-Modified-Since） */
		$http_headers = function_exists("apache_request_headers") ? apache_request_headers() : null ;
		if(
			   isset($http_headers) && isset($http_headers["If-Modified-Since"])
			&& strtotime($http_headers["If-Modified-Since"]) >= filemtime($cache)
			&& !headers_sent()
		){
			header("HTTP/1.1 304 Not Modified");
			return;
		}
	}else{
		/* 変換してキャッシュとして保存 */

		if($width > SOYCMS_RESIZE_IMAGE_RESIZE_LIMIT || $height > SOYCMS_RESIZE_IMAGE_RESIZE_LIMIT){
			_redirect();
		}

		//変換実行
		_soy2_resizeimage($file,$cache,$width,$height,$max_width);
	}

	//作成したキャッシュを返す
	if(is_readable($cache)){
		//出力
		header("Content-Length: ".filesize($cache));
		header($data["mime"]);
		header("Last-Modified: ".gmdate("D, d M Y H:i:s T", filemtime($cache)));
		readfile($cache);
	}else{
		//変換失敗
		_redirect();
	}

	/* 終わり */
	return;



/**
 * $_GET["src"]にリダイレクト
 */
function _redirect(){
	$url = $_GET["src"];

	//httpsでアクセスしているときはリダイレクト先もhttpsにしておく
	if(strncmp($url,"http://",7)===0 && isset($_SERVER["HTTPS"])){
		$url = strtr($url,array("http://" => "https://"));
	}

	header("Location: ".$url);
	exit;
}
/**
 * 404 Not Found
 */
function _not_found(){
	header("HTTP/1.0 404 Not Found");
	exit;
}
/**
 * ファイルの拡張子を取得する
 */
function _get_extension($file){
	if(version_compare(PHP_VERSION,"5.2.1")>=0){
		return strtolower(pathinfo($file, PATHINFO_EXTENSION));
	}else{
		$info = pathinfo($file);
		return strtolower($info['extension']);
	}
}

function _soy2_resizeimage($filepath,$savepath,$width = null,$height = null,$max_width = null){

	//image magick 2.0.0以上
	if(class_exists("Imagick") && method_exists("Imagick","pingImageFile")){

		//allow_url_fopen=YesであってもImagickはファイルシステム外のファイルを扱えない模様
		if(!strncasecmp($filepath,"http",4)){
			//一時ファイルに保存しておく
			$fh = tmpfile();
			fwrite($fh, file_get_contents($filepath));
			fseek($fh,0);
		}else{
			$fh = fopen($filepath, "rb");
		}

		if(!$fh){
			return -1;
		}

		try{
			$thumb = new Imagick();

			//情報だけ取得
			$thumb->pingImageFile($fh);
			$info = $thumb->identifyImage();
			$imageSize = array($info["geometry"]["width"],$info["geometry"]["height"]);

			//size
			if(is_null($width) && is_null($height)){
				$width = $imageSize[0];
				$height = $imageSize[1];
			}else if(is_null($width)){
				$width = (int)( $imageSize[0] * $height / $imageSize[1] );
			}else if(is_null($height)){
				$height = (int)( $imageSize[1] * $width / $imageSize[0] );
			}

			//最大幅指定
			if(isset($max_width) && $max_width >0 && $max_width < $width){
				$height = (int)( $height * $max_width / $width );
				$width = $max_width;
			}

			//変更がなければ何もしない
			if($width == $imageSize[0] && $height == $imageSize[1]){
				return false;
			}

			//変換のために画像を読み込む
			$thumb->readImageFile($fh);

			//画質を90に落とす
			if( strncmp($info["format"],"JPEG",4)===0 && strncmp($info["compression"],"JPEG",4)===0 && method_exists($thumb,"getImageCompressionQuality") && $thumb->getImageCompressionQuality() > 90){
				$thumb->setImageCompressionQuality(90);
			}

			//不要な情報を除去
			$thumb->stripImage();

			//変換
			if(strncmp($info["format"],"GIF",3)===0){

				$images = $thumb->coalesceImages();
				if( $images->getImageIterations()==1 ){
					$thumb->thumbnailImage($width,$height);
					$thumb->writeImage($savepath);
				}else{
					//アニメーションGIFには非対応
					return -1;

					/*
					 * ウノウラボ by Zynga Japan: ImageMagickでGIFアニメをリサイズ
					 * http://labs.unoh.net/2008/12/imagemagickgif.html
					 */
					/*
					$images->setFirstIterator();
					while($images->nextImage()){
						$images->thumbnailImage($width,$height);
					}
					$thumb = $images->deconstructImages();
					$thumb->writeImages($savepath,true);
					*/

					/*
					 * 携帯向けアニメーションgifをimagemagickを使ってリサイズする方法(php) >> cat /dev/random > /dev/null &
					 * http://blog.xcir.net/index.php/2011/08/%E6%90%BA%E5%B8%AF%E5%90%91%E3%81%91%E3%82%A2%E3%83%8B%E3%83%A1%E3%83%BC%E3%82%B7%E3%83%A7%E3%83%B3gif%E3%82%92imagemagick%E3%82%92%E4%BD%BF%E3%81%A3%E3%81%A6%E3%83%AA%E3%82%B5%E3%82%A4%E3%82%BA/
					 */
					/*
					//イテレーターの初期化（フレームを先頭にする）
					$thumb->setFirstIterator();
					$scale_w = $width / $imageSize[0];
					$scale_h = $height / $imageSize[1];
					foreach($thumb as $frame){//各フレーム処理
						//フレームのジオメトリ情報の取得（オフセット位置付き）
						$par=$frame->getImagePage();
						//リサイズする（imagick::FILTER_POINT=アンチエイリアスしない）
						$frame->resizeImage(
							ceil($frame->getImageWidth()*$scale_w),
							ceil($frame->getImageHeight()*$scale_h),
							imagick::FILTER_POINT,
							0
						);
						//フレームのジオメトリ情報の設定
						$frame->setImagePage(
							ceil($par['width']*$scale_w),
							ceil($par['height']*$scale_h),
							ceil($par['x']*$scale_w),
							ceil($par['y']*$scale_h)
						);
					}
					//書き出し
					$thumb->writeImages($savepath, true);
					*/
				}

			}else{
				$thumb->thumbnailImage($width,$height);
				$thumb->writeImage($savepath);
			}


			fclose($fh);
			return true;
		}catch(Exception $e){
			fclose($fh);
			error_log("_soy2_resizeimage [Imagick] \n".var_export($e,true));
			return -1;
		}

	}

	//gd
	return _soy2_image_resizeimage_gd($filepath,$savepath,$width,$height,$max_width);
}

function _soy2_image_resizeimage_gd($filepath,$savepath,$width = null,$height = null,$max_width = null){

	/* 読み込み */
	$filetype = _get_extension($filepath);
	if($filetype == "jpg")$filetype = "jpeg";

	//読み込み関数
	$from = "imagecreatefrom" . $filetype;
	if(!function_exists($from)){
		trigger_error("Failed [Function $from does not exist to load ".$filetype."] " . __FILE__ . ":" . __LINE__,E_USER_ERROR);
		return -1;
	}

	if(!function_exists("getimagesize")){
		trigger_error("Failed [Function getimagesize does not exist] " . __FILE__ . ":" . __LINE__,E_USER_ERROR);
		return -1;
	}

	//source
	$srcImage = $from($filepath);

	/* 出力サイズ */
	$imageSize = getimagesize($filepath);
	if(is_null($width) && is_null($height)){
		$width = $imageSize[0];
		$height = $imageSize[1];
	}else if(is_null($width)){
		$width = (int)( $imageSize[0] * $height / $imageSize[1] );
	}else if(is_null($height)){
		$height = (int)( $imageSize[1] * $width / $imageSize[0] );
	}

	//最大幅指定
	if(isset($max_width) && $max_width >0 && $max_width < $width){
		$height = (int)( $height * $max_width / $width );
		$width = $max_width;
	}

	//変更がなければ何もしない
	if($width == $imageSize[0] && $height == $imageSize[1]){
		return false;
	}

	/* 変換 */
	$dstImage = imagecreatetruecolor($width,$height);
	imagecopyresampled(
		$dstImage,$srcImage,
		0, 0, 0, 0,
  		$width, $height, $imageSize[0], $imageSize[1]
  	);

	/* 保存 */
	$savetype = _get_extension($savepath);
	//保存先のファイル名に拡張子がなければ元と同じ種類にする
	if(strlen($savetype)<1) $savetype = $filetype;

	switch($savetype){
		case "jpg":
		case "jpeg":
			return imagejpeg($dstImage,$savepath,100);
			break;
		default:
			$to = "image" . $savetype;
			if(function_exists($to)){
				$to($dstImage,$savepath);
				return true;
			}

			//invalid type
			trigger_error("Failed [Invalid Type:".$savetype."] " . __FILE__ . ":" . __LINE__,2);
			return -1;
			break;
	}
}
