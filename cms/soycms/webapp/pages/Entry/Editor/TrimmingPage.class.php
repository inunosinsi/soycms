<?php

/**
 * トリミングプラグインで使われている
 * GD必須
 *
 */
class TrimmingPage extends CMSWebPageBase {

	private $responseObject;

	function doPost(){

		if(soy2_check_token()){
			$responseObject = new StdClass();
			$responseObject->result = false;
			$responseObject->imagePath = null;

			//Siteのアップロードディレクトリを調べる
			$action = SOY2ActionFactory::createInstance("SiteConfig.DetailAction");
			$result = $action->run();
			$entity = $result->getAttribute("entity");

			$uploadFileDir = str_replace("//" , "/" , UserInfoUtil::getSiteDirectory() . self::_getDefaultUpload()) . "/";
			$uploadThumbDir = $uploadFileDir . "thumb";
			if(!file_exists($uploadThumbDir)) mkdir($uploadThumbDir);

			$imageFileName = substr($_GET["path"], strrpos($_GET["path"], "/") + 1);

			$jpeg_quality = 90;

			//$src = $uploadFileDir . $imageFileName;
			$src = $_SERVER["DOCUMENT_ROOT"] . htmlspecialchars($_GET["path"], ENT_QUOTES, "UTF-8");

			$targ_w = $_POST['w'];
			$targ_h = $_POST['h'];

			if(!function_exists("x_get_properties_by_img_tag")) SOY2::import("site_include.plugin.x_cls.func.fn", ".php");
			$fn = "imagecreatefrom".x_get_extension_by_filepath($_GET["path"]);
			if(!function_exists($fn)) $fn = "imagecreatefromjpeg";
			
			$img_r = $fn($src);
			$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

			imagecopyresampled($dst_r, $img_r, 0, 0, $_POST['x'], $_POST['y'], $targ_w, $targ_h, $_POST['w'], $_POST['h']);

			imagejpeg($dst_r, $uploadThumbDir . "/" . $imageFileName, $jpeg_quality);

			imagedestroy($dst_r);

			// /ドキュメントルート/hoge/siteId/index.php のような一つ下の階層にサイトディレクトリがある場合
			$siteDir = trim(str_replace($_SERVER["DOCUMENT_ROOT"], "", UserInfoUtil::getSiteDirectory()), "/");
			$uploadImagePath = "/" . $siteDir . "/" . self::_getDefaultUpload() . "/thumb/";

			$responseObject->result = true;
			$responseObject->imagePath = $uploadImagePath . $imageFileName;
			$this->responseObject = $responseObject;
		}
	}

	/**
	 * アップロードディレクトリのパスを返す
	 * 最初にも最後にもスラッシュは付かない
	 */
    private function _getDefaultUpload(){
		// 空文字列または/dir/**/path
		$dir = SOY2DAOFactory::create("cms.SiteConfigDAO")->get()->getUploadDirectory();

		//先頭の/を削除
		if(strlen($dir) && $dir[0] == "/") $dir = substr($dir,1);

		return rtrim($dir, "/");
    }

	function __construct($arg) {
		parent::__construct();

		$this->addModel("jcropcss", array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./js/jcrop/css/jquery.Jcrop.min.css")
		));

		$this->addModel("jqueryjs", array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/jquery.js")
		));

		$this->addModel("jcropjs", array(
			"type" => "text/JavaScript",
			"src" => SOY2PageController::createRelativeLink("./js/jcrop/js/jquery.Jcrop.min.js")
		));

		$this->addModel("display_jcrop_image", array(
			"visible" => (!isset($this->responseObject))
		));

		$this->addImage("jcrop_image", array(
			"src" => $_GET["path"],
			"id" => "target"
		));

		$this->addForm("form", array(
			"method" => "post",
		));

		$this->addModel("display_jcrop_thumbnail", array(
			"visible" => (isset($this->responseObject))
		));

		$this->addImage("jcrop_thumbnail", array(
			"src" => (isset($this->responseObject->imagePath)) ? $this->responseObject->imagePath : "",
		));

		$this->addInput("jcrop_thumbnail_path", array(
			"value" => (isset($this->responseObject->imagePath)) ? $this->responseObject->imagePath : "",
			"id" => "thumbnail_path"
		));
	}
}
