<?php

SOYCMSThumbnailPlugin::register();
class SOYCMSThumbnailPlugin{

	const PLUGIN_ID = "soycms_thumbnail";

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
			"type" => Plugin::TYPE_ENTRY,
			"description"=>"サムネイル画像を生成します",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.6"
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
				CMSPlugin::setEvent('onEntryListBeforeOutput', $this->getId(), array($this, "onEntryListBeforeOutput"));
				CMSPlugin::setEvent('onEntryOutput', $this->getId(), array($this, "onEntryOutput"));
			}
		}
	}

	/**
	 * onEntryListBeforeOutput
	 */
	function onEntryListBeforeOutput($arg){
		$entries = &$arg["entries"];
		$entryIds = soycms_get_entry_id_by_entries($entries);
		
		if(count($entryIds)) ThumbnailPluginUtil::setThumbnailPathesByEntryIds($entryIds);
	}

	function onEntryOutput($arg){
		$entryId = (int)$arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];

		$attrValues = ThumbnailPluginUtil::getThumbnailPathesByEntryId($entryId);
		
		foreach(array(ThumbnailPluginUtil::UPLOAD_IMAGE, ThumbnailPluginUtil::TRIMMING_IMAGE, ThumbnailPluginUtil::RESIZE_IMAGE) as $typ){
			$label = str_replace(ThumbnailPluginUtil::PREFIX_IMAGE, "", $typ);
			if($label ==  "resize") $label = "thumbnail";

			$imagePath = (isset($attrValues[$typ])) ? $attrValues[$typ] : "";
			if($label == "thumbnail" && !strlen($imagePath) && is_string($this->no_thumbnail_path)) $imagePath = $this->no_thumbnail_path;

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
				"alt" => $attrValues[ThumbnailPluginUtil::THUMBNAIL_ALT]
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

		$images = array();
		$images[ThumbnailPluginUtil::UPLOAD_IMAGE] = (isset($_POST["jcrop_upload_field"])) ? $_POST["jcrop_upload_field"] : null;
		$images[ThumbnailPluginUtil::TRIMMING_IMAGE] = (isset($_POST["jcrop_trimming_field"])) ? $_POST["jcrop_trimming_field"] : null;
		$images[ThumbnailPluginUtil::RESIZE_IMAGE] = null;

		//http or httpsからはじまる場合はスラッシュからはじまる絶対パスに変換する
		if(strpos($images[ThumbnailPluginUtil::UPLOAD_IMAGE], "http") === 0){
			$iPath = str_replace(array("http://", "https://"), "", $images[ThumbnailPluginUtil::UPLOAD_IMAGE]);
			$iPath = substr($iPath, strpos($iPath, "/"));
			$images[ThumbnailPluginUtil::UPLOAD_IMAGE] = $iPath;
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
				$imageFilePath = trim($images[ThumbnailPluginUtil::UPLOAD_IMAGE]);
			}
		}

		if($resizeFlag && $imageFilePath){
			//$dir = substr(UserInfoUtil::getSiteDirectory(), 0, strrpos(UserInfoUtil::getSiteDirectory(), "/" . UserInfoUtil::getSite()->getSiteId()));
			$path = $_SERVER["DOCUMENT_ROOT"] . $imageFilePath;
	
			// /ドキュメントルート/hoge/siteId/index.phpのようなサイト設定の場合でも対応できるようにする
			$siteDir = ThumbnailPluginUtil::getSiteDirectoryName();
			
			//念の為、指定のパスに画像があるか調べる
			if(!file_exists($path)) $path = $_SERVER["DOCUMENT_ROOT"] . "/" . $siteDir . "/" . $imageFilePath;

			if(file_exists($path)){
				$imageInfoArray = (getimagesize($path));
				$w = (int)$imageInfoArray[0];
				$h = (int)$imageInfoArray[1];

				//アスペクト比の維持
				if($w > (int)$_POST["jcrop_resize_w"]){
					$ratio = $w / $h;
					$w = (int)$_POST["jcrop_resize_w"];
					$h = $w / $ratio;
				}

				//念の為にwidthの値を確認
				if($w > 0 && $h > 0){
					//Siteのアップロードディレクトリを調べる
					$uploadDir = (string)ThumbnailPluginUtil::getSiteConfig()->getUploadDirectory();
					
					$resizeDir = str_replace("//" , "/" , UserInfoUtil::getSiteDirectory() . $uploadDir) . "/resize";
					if(!file_exists($resizeDir)) mkdir($resizeDir);

					$imageFileName = substr($imageFilePath, strrpos($imageFilePath, "/") + 1);
					$resizePath = $resizeDir . "/" . $imageFileName;
					soy2_resizeimage($path, $resizePath, $w, $h);
					$images[ThumbnailPluginUtil::RESIZE_IMAGE] = "/" . $siteDir . $uploadDir . "/resize/" . $imageFileName;
				}else{
					$images[ThumbnailPluginUtil::RESIZE_IMAGE] = "";
				}
			}
			
		}else{
			$images[ThumbnailPluginUtil::RESIZE_IMAGE] = trim($_POST["jcrop_resize_field"]);
		}
		
		foreach($images as $fieldId => $path){
			$attr = soycms_get_entry_attribute_object($entryId, $fieldId);
			$attr->setValue((string)$path);
			soycms_save_entry_attribute_object($attr);
		}

		//記事毎のリサイズ設定を配列に格納しておく
		$cnf = array(
					"ratio_w" => (int)$_POST["jcrop_ratio_w"],
					"ratio_h" => (int)$_POST["jcrop_ratio_h"],
					"resize_w" => (int)$_POST["jcrop_resize_w"],
					"resize_h" => (int)$_POST["jcrop_resize_h"]
					);

		$attr = soycms_get_entry_attribute_object($entryId, ThumbnailPluginUtil::THUMBNAIL_CONFIG);
		$attr->setValue(soy2_serialize($cnf));
		soycms_save_entry_attribute_object($attr);
		
		//alt
		$attr = soycms_get_entry_attribute_object($entryId, ThumbnailPluginUtil::THUMBNAIL_ALT);
		$attr->setValue($_POST["jcrop_thumbnail_alt"]);
		soycms_save_entry_attribute_object($attr);
	}

	function onEntryCopy($args){
		list($old, $new) = $args;
		$attrValues = ThumbnailPluginUtil::getThumbnailPathesByEntryId($old);

		foreach($attrValues as $fieldId => $attrValue){
			$newAttr = soycms_get_entry_attribute_object($new, $fieldId);
			$newAttr->setValue($attrValue);
			soycms_save_entry_attribute_object($newAttr);
		}

		return true;
	}

	function onEntryRemove($args){
		foreach($args as $entryId){
			$attrValues = ThumbnailPluginUtil::getThumbnailPathesByEntryId(0);
			foreach($attrValues as $fieldId => $_dust){
				$newAttr = soycms_get_entry_attribute_object($entryId, $fieldId);
				$newAttr->setValue(null);
				soycms_save_entry_attribute_object($newAttr);
			}
		}
		
		return true;
	}

	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : 0;
		return self::_buildFormHtml($entryId);
	}

	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : 0;
		return self::_buildFormHtml($entryId);
	}

	/**
	 * カスタムフィールドでフォームを出力
	 * @param integer entryId
	 * @return string html
	 */
	private function _buildFormHtml(int $entryId){
		$attrValues = ThumbnailPluginUtil::getThumbnailPathesByEntryId($entryId);
		$cnf = self::_getTmbConfig($entryId);

		$uploadImagePath = $attrValues[ThumbnailPluginUtil::UPLOAD_IMAGE];

		$html = array();
		$html[] = "<div class=\"alert alert-success\">サムネイル</div>";
		$html[] = "<div class=\"form-inline\">";
		$html[] = "<label for=\"custom_field_img\">サムネイルの生成</label>";
		$html[] = '<div class="table-responsive"><table class="table">';
		$html[] = "<tr>";
		$html[] = "<th style=\"width:15%;\">";
		$html[] = "アップロード";
		$html[] = "<a href=\"javascript:void(0);\" title=\"サムネイルの生成用の画像を選択します。\"><i class=\"fa fa-question-circle\"></i></a>";
		$html[] = "</th>";
		$html[] = "<td><input type=\"text\" class=\"jcrop_field_input form-control\" style=\"width:70%\" id=\"jcrop_upload_field\" name=\"jcrop_upload_field\" value=\"" . $uploadImagePath . "\" />";
		$html[] = "<input type=\"button\" onclick=\"open_jcrop_filemanager($('#jcrop_upload_field'));\" class=\"btn\" value=\"ファイルを指定する\">";
		if(strlen($uploadImagePath) > 0 && soycms_check_is_image_path($uploadImagePath)){
			$html[] = "<a href=\"#\" onclick=\"return preview_thumbnail_plugin(\$('#jcrop_upload_field'));\" class=\"btn btn-info\">Preview</a>";
		}
		$html[] = "</td>";
		$html[] = "</tr>";

		$trimmingImagePath = $attrValues[ThumbnailPluginUtil::TRIMMING_IMAGE];
		
		$html[] = "<tr>";
		$html[] = "<th>トリミング</th>";
		$html[] = "<td><input type=\"text\" style=\"width:70%;\" id=\"jcrop_trimming_field\" class=\"form-control\" name=\"jcrop_trimming_field\" value=\"" . $trimmingImagePath . "\" readonly=\"readonly\">";
		if(strlen($trimmingImagePath) === 0){
			$html[] = "<input type=\"button\" onclick=\"open_jcrop_trimming($('#jcrop_trimming_field'));\" class=\"btn btn-primary\" value=\"トリミング\">";
		}
		if(strlen($trimmingImagePath) > 0){
			$html[] = "<input type=\"button\" class=\"btn btn-warning\" onclick=\"clearTrimmingForm();\" value=\"クリア\">";
			if(soycms_check_is_image_path($trimmingImagePath)){
				$html[] = "<a href=\"#\" onclick=\"return preview_thumbnail_plugin(\$('#jcrop_trimming_field'));\" class=\"btn btn-info\">Preview</a>";
			}
		}
		$html[] = "<br><span style=\"display:block;margin-top:10px;\">アスペクト比:width:<input type=\"number\" id=\"ratio_w\" class=\"form-control\" name=\"jcrop_ratio_w\" value=\"". (int)$cnf["ratio_w"] . "\" style=\"width:80px;\">&nbsp;";
		$html[] = "height:<input type=\"number\" id=\"ratio_h\" class=\"form-control\" name=\"jcrop_ratio_h\" value=\"" . (int)$cnf["ratio_h"] . "\" style=\"width:80px;\"></span>";
		$html[] = "</td>";
		$html[] = "</tr>";

		$resizeImagePath = $attrValues[ThumbnailPluginUtil::RESIZE_IMAGE];
		
		$html[] = "<tr>";
		$html[] = "<th>";
		$html[] = "サムネイルの<br>リサイズ";
		$html[] = "<a href=\"javascript:void(0);\" title=\"トリミングの画像があればリサイズして、トリミング画像がなければアップロードの画像をリサイズしてサムネイルを生成します。リサイズをし直す時は一度クリアを押してください。\"><i class=\"fa fa-question-circle\"></i></a>";
		$html[] = "</th>";
		$html[] = "<td>";
		$html[] = "<input type=\"text\" style=\"width:70%;\" id=\"jcrop_resize_field\" class=\"form-control\" name=\"jcrop_resize_field\" value=\"" . $resizeImagePath . "\" readonly=\"readonly\">";
		if(strlen($resizeImagePath) > 0){
			$html[] = "<input type=\"button\" class=\"btn btn-warning\" onclick=\"clearResizeForm();\" value=\"クリア\">";
			if(soycms_check_is_image_path($resizeImagePath)){
				$html[] = "<a href=\"#\" onclick=\"return preview_thumbnail_plugin(\$('#jcrop_resize_field'));\" class=\"btn btn-info\">Preview</a>";
			}
		}

		$html[] = "<br /><span style=\"display:block;margin-top:10px;\">リサイズ:width:<input type=\"number\" class=\"form-control\" name=\"jcrop_resize_w\" value=\"" . (int)$cnf["resize_w"] . "\" style=\"width:100px;\">&nbsp;";
		$html[] = "height:<input type=\"number\" class=\"form-control\" name=\"jcrop_resize_h\" value=\"" . (int)$cnf["resize_h"] . "\" style=\"width:100px;\"></span><br />";
    	$html[] = "<span style=\"display:block;\">alt:<input type=\"text\" class=\"form-control\" name=\"jcrop_thumbnail_alt\" value=\"" . $attrValues[ThumbnailPluginUtil::THUMBNAIL_ALT] . "\" style=\"width:40%;\"></span>";
		$html[] = "</td>";
		$html[] = "</tr>";
		$html[] = "</table></div>";

		$html[] = "<script type=\"text/javascript\">";

		if(count($this->label_thumbail_paths)){
			$html[] = "	var list = [];";
			$keys = array();
			foreach($this->label_thumbail_paths as $labelId => $path){
				$html[] = "list[" . $labelId . "] = \"" . $path . "\";";
				$keys[] = $labelId;
			}
			$html[] = "keys = [".implode(",", $keys) ."];";
			$html[] = "for (var i = 0; i < keys.length; i++) {";
			$html[] = "	$('#label_' + keys[i]).change(function(){";
			$html[] = "		if($(this).is(':checked')){";
			$html[] = "			var idx = $(this).prop('id').replace('label_', '');";
			$html[] = "			if(list[idx]){";
			$html[] = "				$('#jcrop_upload_field').val(list[idx])";
			$html[] = "			}";
			$html[] = "		}";
			$html[] = "	});";
			$html[] = "}";
		}

		$html[] = "function open_jcrop_filemanager(\$form){";
		$html[] = "	common_to_layer(\"" . SOY2PageController::createLink("Page.Editor.FileUpload?jcrop_upload_field") . "\");";
		$html[] = "}";
		$html[] = "function open_jcrop_trimming(\$form){";
		$html[] = "	if(\$(\"#jcrop_upload_field\").val().length > 0){";
		$html[] = "		common_to_layer(\"" . SOY2PageController::createLink("Entry.Editor.Trimming") . "?jcrop_trimming_field&path=\" + \$(\"#jcrop_upload_field\").val() + \"&w=\" + \$(\"#ratio_w\").val() + \"&h=\" + \$(\"#ratio_h\").val())";
		$html[] = "	}else{";
		$html[] = "		alert(\"画像が選択されていません。\")";
		$html[] = "	}";
		$html[] = "}";

		$html[] = "function preview_thumbnail_plugin(\$form){";
		$html[] = "	var domainURL = \"". self::_getDomainUrl() . "\";";
		$html[] = "	var siteURL = \"" . UserInfoUtil::getSiteUrl() . "\";";
		$html[] = "";
		$html[] = "	var url = \"\";";
		$html[] = "	var href = \$form.val();";
		$html[] = "";
		$html[] = "	if(href && href.indexOf(\"/\") == 0){";
		$html[] = "		url = domainURL + href.substring(1, href.length);";
		$html[] = "	}else{";
		$html[] = "		url = siteURL + href;";
		$html[] = "	}";
		$html[] = "";
		$html[] = "	var temp = new Image();";
		$html[] = "	temp.src = url;";
		$html[] = "	temp.onload = function(e){";
		$html[] = "		common_element_to_layer(url, {";
		$html[] = "			height : Math.min(600, Math.max(400, temp.height + 20)),";
		$html[] = "			width  : Math.min(800, Math.max(400, temp.width + 20))";
		$html[] = "		});";
		$html[] = "	};";
		$html[] = "	temp.onerror = function(e){";
		$html[] = "		alert(url + \"が見つかりません。\");";
		$html[] = "	}";
		$html[] = "	return false;";
		$html[] = "}";
		$html[] = "";
		$html[] = "function clearTrimmingForm(){";
		$html[] = "	\$(\"#jcrop_trimming_field\").val(\"\");";
		$html[] = "}";
		$html[] = "function clearResizeForm(){";
		$html[] = "	\$(\"#jcrop_resize_field\").val(\"\");";
		$html[] = "}";
		$html[] = "</script>";
		$html[] = "</div>";

		return implode("\n", $html);
	}

	/**
	 * 念の為にURLからサイトIDを除いておく
	 * @param string url
	 * @return string url
	 */
	private function _getDomainUrl(){
		$siteUrl = (string)ThumbnailPluginUtil::getSiteConfig()->getConfigValue("url");
		$siteId = UserInfoUtil::getSite()->getSiteId();
		if(is_numeric(strpos($siteUrl, "/" . $siteId . "/"))) $siteUrl = str_replace("/" . $siteId . "/", "/" , $siteUrl);

		//サイトディレクトリの階層をルートドメインから一つ下の階層にした場合
		if(is_numeric(strpos(ThumbnailPluginUtil::getSiteDirectoryName(), "/"))){
			$siteUrl = dirname($siteUrl) . "/";
		}

		return $siteUrl;
	}

	/**
	 * 記事毎のトリミング設定の取得
	 */
	private function _getTmbConfig(int $entryId){
		$cnf = ($entryId > 0) ? soycms_get_entry_attribute_value($entryId, ThumbnailPluginUtil::THUMBNAIL_CONFIG, "string") : "";
		if(!strlen($cnf)){
			return array(
				"ratio_w" => $this->ratio_w,
				"ratio_h" => $this->ratio_h,
				"resize_w" => $this->resize_w,
				"resize_h" => $this->resize_h
			);
		}

		return soy2_unserialize($cnf);
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

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new SOYCMSThumbnailPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
