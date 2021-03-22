<?php

SOYCMSThumbnailPlugin::register();
class SOYCMSThumbnailPlugin{

	const PLUGIN_ID = "soycms_thumbnail";

	const UPLOAD_IMAGE = "soycms_thumbnail_plugin_upload";
	const TRIMMING_IMAGE = "soycms_thumbnail_plugin_trimming";
	const RESIZE_IMAGE = "soycms_thumbnail_plugin_resize";
	const PREFIX_IMAGE = "soycms_thumbnail_plugin_";

	const THUMBNAIL_CONFIG = "soycms_thumbnail_plugin_config";
	const THUMBNAIL_ALT = "soycms_thumbnail_plugin_alt";

	private $entryAttributeDao;

	private $ratio_w = 4;
	private $ratio_h = 3;

	private $resize_w = 120;
	private $resize_h = 90;

	private $no_thumbnail_path;

	private $label_thumbail_paths = array();

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"サムネイルプラグイン",
			"description"=>"サムネイル画像を生成します",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.0"
		));

		if(CMSPlugin::activeCheck($this->getId())){
			SOY2::import("site_include.plugin.soycms_thumbnail.util.ThumbnailPluginUtil");

			//管理側
			if(!defined("_SITE_ROOT_")){
				CMSPlugin::addPluginConfigPage($this->getId(),array(
					$this,"config_page"
				));

				CMSPlugin::setEvent('onEntryCreate', $this->getId(), array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryUpdate', $this->getId(), array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCopy', $this->getId(), array($this, "onEntryCopy"));
				CMSPlugin::setEvent('onEntryRemove', $this->getId(), array($this, "onEntryRemove"));

				CMSPlugin::addCustomFieldFunction($this->getId(), "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction($this->getId(), "Blog.Entry", array($this, "onCallCustomField_inBlog"));
			//公開側
			}else{
				CMSPlugin::setEvent('onEntryOutput', $this->getId(), array($this, "onEntryOutput"));
			}
		}
	}

	function onEntryOutput($arg){
		$entryId = $arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];

		$objects = ThumbnailPluginUtil::getThumbnailObjectsByEntryId($entryId);

		foreach(array(self::UPLOAD_IMAGE, self::TRIMMING_IMAGE, self::RESIZE_IMAGE) as $imageType){
			$obj = (isset($objects[$imageType])) ? $objects[$imageType] : new EntryAttribute();

			$label = str_replace("soycms_thumbnail_plugin_", "", $imageType);
			if($label == "resize") $label = "thumbnail";

			$imagePath = trim($obj->getValue());
			if($label == "thumbnail" && !strlen($imagePath)) $imagePath = $this->no_thumbnail_path;

			$htmlObj->addModel("is_" . $label, array(
				"soy2prefix" => "cms",
				"visible" => (strlen($imagePath) > 0)
			));

			$htmlObj->addModel("no_" . $label, array(
				"soy2prefix" => "cms",
				"visible" => (strlen($imagePath) === 0)
			));

			$htmlObj->addImage($label, array(
				"soy2prefix" => "cms",
				"src" => $imagePath,
				"alt" => (isset($objects["soycms_thumbnail_plugin_alt"])) ? $objects["soycms_thumbnail_plugin_alt"]->getValue() : ""
			));

			$htmlObj->addLabel($label . "_text", array(
				"soy2prefix" => "cms",
				"text" => $imagePath
			));

			$htmlObj->addLabel($label . "_path_text", array(
				"soy2prefix" => "cms",
				"text" => $imagePath
			));
		}
	}

	/**
	 * 記事作成時、記事更新時
	 */
	function onEntryUpdate($arg){

		$entry = $arg["entry"];
		$entryId = $entry->getId();

		try{
			self::_attrDao()->delete($entryId, self::UPLOAD_IMAGE);
			self::_attrDao()->delete($entryId, self::TRIMMING_IMAGE);
			self::_attrDao()->delete($entryId, self::RESIZE_IMAGE);
			self::_attrDao()->delete($entryId, self::THUMBNAIL_CONFIG);
			self::_attrDao()->delete($entryId, self::THUMBNAIL_ALT);
		}catch(Exception $e){
			//
		}

		$images = array();
		$images[self::UPLOAD_IMAGE] = (isset($_POST["jcrop_upload_field"])) ? $_POST["jcrop_upload_field"] : null;
		$images[self::TRIMMING_IMAGE] = (isset($_POST["jcrop_trimming_field"])) ? $_POST["jcrop_trimming_field"] : null;

		//http or httpsからはじまる場合はスラッシュからはじまる絶対パスに変換する
		if(strpos($images[self::UPLOAD_IMAGE], "http") === 0){
			$iPath = str_replace(array("http://", "https://"), "", $images[self::UPLOAD_IMAGE]);
			$iPath = substr($iPath, strpos($iPath, "/"));
			$images[self::UPLOAD_IMAGE] = $iPath;
		}

		$resizeFlag = false;
		$imageFilePath = null;

		//リサイズ
		if(!isset($_POST["jcrop_resize_field"]) || strlen($_POST["jcrop_resize_field"]) === 0){
			if(isset($_POST["jcrop_trimming_field"]) && strlen($_POST["jcrop_trimming_field"]) > 0){
				$resizeFlag = true;
				$imageFilePath = trim($_POST["jcrop_trimming_field"]);
			}else if(isset($_POST["jcrop_upload_field"]) && strlen($_POST["jcrop_upload_field"]) > 0){
				$resizeFlag = true;
				$imageFilePath = trim($images[self::UPLOAD_IMAGE]);
			}
		}

		if($resizeFlag && $imageFilePath){
			$dir = substr(UserInfoUtil::getSiteDirectory(), 0, strrpos(UserInfoUtil::getSiteDirectory(), "/" . UserInfoUtil::getSite()->getSiteId()));
			$path = $dir . $imageFilePath;

			//念の為、指定のパスに画像があるか調べる
			if(!file_exists($path)) {
				$path = $dir . "/" . UserInfoUtil::getSite()->getSiteId() . $imageFilePath;
			}

			$imageInfoArray = (getimagesize($path));
			$w = (int)$imageInfoArray[0];
			$h = (int)$imageInfoArray[1];

			//アスペクト比の維持
			if($w > (int)$_POST["jcrop_resize_w"]){
				$ratio = $w / $h;
				$w = (int)$_POST["jcrop_resize_w"];
				$h = $w / $ratio;
			}

			//Siteのアップロードディレクトリを調べる
			$siteConfig = SOY2DAOFactory::create("cms.SiteConfigDAO")->get();

			$resizeDir = str_replace("//" , "/" , UserInfoUtil::getSiteDirectory() . $siteConfig->getDefaultUploadDirectory()) . "/resize";
			if(!file_exists($resizeDir)) mkdir($resizeDir);

			$imageFileName = substr($imageFilePath, strrpos($imageFilePath, "/") + 1);
			$resizePath = $resizeDir . "/" . $imageFileName;
			soy2_resizeimage($path, $resizePath, $w, $h);
			$images[self::RESIZE_IMAGE] = "/" . UserInfoUtil::getSite()->getSiteId() . $siteConfig->getDefaultUploadDirectory() . "/resize/" . $imageFileName;

		}else{
			$images[self::RESIZE_IMAGE] = trim($_POST["jcrop_resize_field"]);
		}

		$obj = new EntryAttribute();
		$obj->setEntryId($entryId);

		foreach($images as $key => $image){
			$obj->setFieldId($key);
			$obj->setValue($image);
			try{
				self::_attrDao()->insert($obj);
			}catch(Exception $e){
				//
			}
		}

		//記事毎のリサイズ設定を配列に格納しておく
		$config = array(
					"ratio_w" => (int)$_POST["jcrop_ratio_w"],
					"ratio_h" => (int)$_POST["jcrop_ratio_h"],
					"resize_w" => (int)$_POST["jcrop_resize_w"],
					"resize_h" => (int)$_POST["jcrop_resize_h"]
					);

		$obj->setFieldId(self::THUMBNAIL_CONFIG);
		$obj->setValue(soy2_serialize($config));
		try{
			self::_attrDao()->insert($obj);
		}catch(Exception $e){
			//
		}

		//alt
		$obj->setFieldId(self::THUMBNAIL_ALT);
		$obj->setValue($_POST["jcrop_thumbnail_alt"]);
		try{
			self::_attrDao()->insert($obj);
		}catch(Exception $e){
			//
		}
	}

	function onEntryCopy($args){
		list($old, $new) = $args;
		$objects = ThumbnailPluginUtil::getThumbnailObjectsByEntryId($old);

		foreach($objects as $key => $object){
			try{
				$obj = new EntryAttribute();
				$obj->setEntryId($new);
				$obj->setFieldId($key);
				$obj->setValue($object->getValue());
				$obj->setExtraValuesArray(null);
				self::_attrDao()->insert($obj);
			}catch(Exception $e){
				//
			}
		}

		return true;
	}

	function onEntryRemove($args){
		foreach($args as $entryId){
			try{
				self::_attrDao()->deleteByEntryId($entryId);
			}catch(Exception $e){
				//
			}
		}

		return true;
	}

	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : null;

		$htmls = array();

		$htmls[] = "<div class=\"form-inline\">";
		$htmls[] = self::buildFormHtml($entryId);
		$htmls[] = "</div>";

		return implode("\n", $htmls);
	}

	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : null;

		$htmls = array();

		$htmls[] = "<div class=\"form-inline\">";
		$htmls[] = self::buildFormHtml($entryId);
		$htmls[] = "</div>";

		return implode("\n", $htmls);
	}

	/**
	 * カスタムフィールドでフォームを出力
	 * @param integer entryId
	 * @return string html
	 */
	private function buildFormHtml($entryId){
		$objects = ThumbnailPluginUtil::getThumbnailObjectsByEntryId($entryId);
		$config = self::getConfigObject($entryId);

		try{
			$siteConfig = SOY2DAOFactory::create("cms.SiteConfigDAO")->get();
		}catch(Exception $e){
			$siteConfig = new SiteConfig();
		}

		$uploadObject = (isset($objects["soycms_thumbnail_plugin_upload"])) ? $objects["soycms_thumbnail_plugin_upload"] : new EntryAttribute();

		$htmls = array();
		$htmls[] = "<label for=\"custom_field_img\">サムネイルの生成</label>";
		$htmls[] = '<div class="table-responsive"><table class="table">';
		$htmls[] = "<tr>";
		$htmls[] = "<th style=\"width:15%;\">";
		$htmls[] = "アップロード";
		$htmls[] = "<a href=\"javascript:void(0);\" title=\"サムネイルの生成用の画像を選択します。\"><i class=\"fa fa-question-circle\"></i></a>";
		$htmls[] = "</th>";
		$htmls[] = "<td><input type=\"text\" class=\"jcrop_field_input form-control\" style=\"width:70%\" id=\"jcrop_upload_field\" name=\"jcrop_upload_field\" value=\"" . $uploadObject->getValue() . "\" />";
		$htmls[] = "<input type=\"button\" onclick=\"open_jcrop_filemanager($('#jcrop_upload_field'));\" class=\"btn\" value=\"ファイルを指定する\">";
		if(strlen($uploadObject->getValue()) > 0){
			$htmls[] = "<a href=\"#\" onclick=\"return preview_thumbnail_plugin(\$('#jcrop_upload_field'));\" class=\"btn btn-info\">Preview</a>";
		}
		$htmls[] = "</td>";
		$htmls[] = "</tr>";

		$trimmingObject = (isset($objects["soycms_thumbnail_plugin_trimming"])) ? $objects["soycms_thumbnail_plugin_trimming"] : new EntryAttribute();

		$htmls[] = "<tr>";
		$htmls[] = "<th>トリミング</th>";
		$htmls[] = "<td><input type=\"text\" style=\"width:70%;\" id=\"jcrop_trimming_field\" class=\"form-control\" name=\"jcrop_trimming_field\" value=\"" . $trimmingObject->getValue() . "\" readonly=\"readonly\">";
		if(strlen($trimmingObject->getValue()) === 0){
			$htmls[] = "<input type=\"button\" onclick=\"open_jcrop_trimming($('#jcrop_trimming_field'));\" class=\"btn btn-primary\" value=\"トリミング\">";
		}
		if(strlen($trimmingObject->getValue()) > 0){
			$htmls[] = "<input type=\"button\" class=\"btn btn-warning\" onclick=\"clearTrimmingForm();\" value=\"クリア\">";
			$htmls[] = "<a href=\"#\" onclick=\"return preview_thumbnail_plugin(\$('#jcrop_trimming_field'));\" class=\"btn btn-info\">Preview</a>";
		}
		$htmls[] = "<br><span style=\"display:block;margin-top:10px;\">アスペクト比:width:<input type=\"number\" id=\"ratio_w\" class=\"form-control\" name=\"jcrop_ratio_w\" value=\"". (int)$config["ratio_w"] . "\" style=\"width:80px;\">&nbsp;";
		$htmls[] = "height:<input type=\"number\" id=\"ratio_h\" class=\"form-control\" name=\"jcrop_ratio_h\" value=\"" . (int)$config["ratio_h"] . "\" style=\"width:80px;\"></span>";
		$htmls[] = "</td>";
		$htmls[] = "</tr>";

		$resizeObject = (isset($objects["soycms_thumbnail_plugin_resize"])) ? $objects["soycms_thumbnail_plugin_resize"] : new EntryAttribute();

		$htmls[] = "<tr>";
		$htmls[] = "<th>";
		$htmls[] = "サムネイルの<br>リサイズ";
		$htmls[] = "<a href=\"javascript:void(0);\" title=\"トリミングの画像があればリサイズして、トリミング画像がなければアップロードの画像をリサイズしてサムネイルを生成します。リサイズをし直す時は一度クリアを押してください。\"><i class=\"fa fa-question-circle\"></i></a>";
		$htmls[] = "</th>";
		$htmls[] = "<td>";
		$htmls[] = "<input type=\"text\" style=\"width:70%;\" id=\"jcrop_resize_field\" class=\"form-control\" name=\"jcrop_resize_field\" value=\"" . $resizeObject->getValue() . "\" readonly=\"readonly\">";
		if(strlen($resizeObject->getValue()) > 0){
			$htmls[] = "<input type=\"button\" class=\"btn btn-warning\" onclick=\"clearResizeForm();\" value=\"クリア\">";
		}
		if(strlen($resizeObject->getValue()) > 0){
			$htmls[] = "<a href=\"#\" onclick=\"return preview_thumbnail_plugin(\$('#jcrop_resize_field'));\" class=\"btn btn-info\">Preview</a>";
		}

		$altObject = (isset($objects["soycms_thumbnail_plugin_alt"])) ? $objects["soycms_thumbnail_plugin_alt"] : new EntryAttribute();

		$htmls[] = "<br /><span style=\"display:block;margin-top:10px;\">リサイズ:width:<input type=\"number\" class=\"form-control\" name=\"jcrop_resize_w\" value=\"" . (int)$config["resize_w"] . "\" style=\"width:100px;\">&nbsp;";
		$htmls[] = "height:<input type=\"number\" class=\"form-control\" name=\"jcrop_resize_h\" value=\"" . (int)$config["resize_h"] . "\" style=\"width:100px;\"></span><br />";
    	$htmls[] = "<span style=\"display:block;\">alt:<input type=\"text\" class=\"form-control\" name=\"jcrop_thumbnail_alt\" value=\"" . $altObject->getValue() . "\" style=\"width:40%;\"></span>";
		$htmls[] = "</td>";
		$htmls[] = "</tr>";
		$htmls[] = "</table></div>";

		$htmls[] = "<script type=\"text/javascript\">";

		if(count($this->label_thumbail_paths)){
			$htmls[] = "	var list = [];";
			$keys = array();
			foreach($this->label_thumbail_paths as $labelId => $path){
				$htmls[] = "list[" . $labelId . "] = \"" . $path . "\";";
				$keys[] = $labelId;
			}
			$htmls[] = "keys = [".implode(",", $keys) ."];";
			$htmls[] = "for (var i = 0; i < keys.length; i++) {";
			$htmls[] = "	$('#label_' + keys[i]).change(function(){";
			$htmls[] = "		if($(this).is(':checked')){";
			$htmls[] = "			var idx = $(this).prop('id').replace('label_', '');";
			$htmls[] = "			if(list[idx]){";
			$htmls[] = "				$('#jcrop_upload_field').val(list[idx])";
			$htmls[] = "			}";
			$htmls[] = "		}";
			$htmls[] = "	});";
			$htmls[] = "}";
		}

		$htmls[] = "function open_jcrop_filemanager(\$form){";
		$htmls[] = "	common_to_layer(\"" . SOY2PageController::createLink("Page.Editor.FileUpload?jcrop_upload_field") . "\");";
		$htmls[] = "}";
		$htmls[] = "function open_jcrop_trimming(\$form){";
		$htmls[] = "	if(\$(\"#jcrop_upload_field\").val().length > 0){";
		$htmls[] = "		common_to_layer(\"" . SOY2PageController::createLink("Entry.Editor.Trimming") . "?jcrop_trimming_field&path=\" + \$(\"#jcrop_upload_field\").val() + \"&w=\" + \$(\"#ratio_w\").val() + \"&h=\" + \$(\"#ratio_h\").val())";
		$htmls[] = "	}else{";
		$htmls[] = "		alert(\"画像が選択されていません。\")";
		$htmls[] = "	}";
		$htmls[] = "}";

		$htmls[] = "function preview_thumbnail_plugin(\$form){";
		$htmls[] = "	var domainURL = \"". self::getDomainUrl($siteConfig->getConfigValue("url")) . "\";";
		$htmls[] = "	var siteURL = \"" . UserInfoUtil::getSiteUrl() . "\";";
		$htmls[] = "";
		$htmls[] = "	var url = \"\";";
		$htmls[] = "	var href = \$form.val();";
		$htmls[] = "";
		$htmls[] = "	if(href && href.indexOf(\"/\") == 0){";
		$htmls[] = "		url = domainURL + href.substring(1, href.length);";
		$htmls[] = "	}else{";
		$htmls[] = "		url = siteURL + href;";
		$htmls[] = "	}";
		$htmls[] = "";
		$htmls[] = "	var temp = new Image();";
		$htmls[] = "	temp.src = url;";
		$htmls[] = "	temp.onload = function(e){";
		$htmls[] = "		common_element_to_layer(url, {";
		$htmls[] = "			height : Math.min(600, Math.max(400, temp.height + 20)),";
		$htmls[] = "			width  : Math.min(800, Math.max(400, temp.width + 20))";
		$htmls[] = "		});";
		$htmls[] = "	};";
		$htmls[] = "	temp.onerror = function(e){";
		$htmls[] = "		alert(url + \"が見つかりません。\");";
		$htmls[] = "	}";
		$htmls[] = "	return false;";
		$htmls[] = "}";
		$htmls[] = "";
		$htmls[] = "function clearTrimmingForm(){";
		$htmls[] = "	\$(\"#jcrop_trimming_field\").val(\"\");";
		$htmls[] = "}";
		$htmls[] = "function clearResizeForm(){";
		$htmls[] = "	\$(\"#jcrop_resize_field\").val(\"\");";
		$htmls[] = "}";
		$htmls[] = "</script>";

		return implode("\n", $htmls);
	}

	/**
	 * 念の為にURLからサイトIDを除いておく
	 * @param string url
	 * @return string url
	 */
	private function getDomainUrl($url){
		$siteId = UserInfoUtil::getSite()->getSiteId();

		if(strpos($url, "/" . $siteId . "/")){
			$url = str_replace("/" . $siteId . "/", "/" , $url);
		}

		return $url;
	}

	/**
	 * 記事毎のトリミング設定の取得
	 */
	private function getConfigObject($entryId){
		try{
			$config = self::_attrDao()->get($entryId, self::THUMBNAIL_CONFIG);
		}catch(Exception $e){
			$config = new EntryAttribute();
		}

		if(!is_null($config->getValue())){
			$configArray = soy2_unserialize($config->getValue());
		}else{
			$configArray = array(
							"ratio_w" => $this->ratio_w,
							"ratio_h" => $this->ratio_h,
							"resize_w" => $this->resize_w,
							"resize_h" => $this->resize_h
							);
		}

		return $configArray;
	}

	/**
	 * 設定画面
	 */
	function config_page($message){
		include_once(dirname(__FILE__) . "/config/SOYCMSThumbnailConfigPage.class.php");
		$form = SOY2HTMLFactory::createInstance("SOYCMSThumbnailConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function getRatioW(){
		return $this->ratio_w;
	}
	function setRatioW($ratio_w){
		$this->ratio_w = $ratio_w;
	}

	function getRatioH(){
		return $this->ratio_h;
	}
	function setRatioH($ratio_h){
		$this->ratio_h = $ratio_h;
	}

	function getResizeW(){
		return $this->resize_w;
	}
	function setResizeW($resize_w){
		$this->resize_w = $resize_w;
	}

	function getResizeH(){
		return $this->resize_h;
	}
	function setResizeH($resize_h){
		$this->resize_h = $resize_h;
	}

	function getNoThumbnailPath(){
		return $this->no_thumbnail_path;
	}
	function setNoTHumbnailPath($no_thumbnail_path){
		$this->no_thumbnail_path = $no_thumbnail_path;
	}

	function getLabelThumbnailPaths(){
		return $this->label_thumbail_paths;
	}
	function setLabelThumbnailPaths($label_thumbail_paths){
		$this->label_thumbail_paths = $label_thumbail_paths;
	}

	private function _attrDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		return $dao;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new SOYCMSThumbnailPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
