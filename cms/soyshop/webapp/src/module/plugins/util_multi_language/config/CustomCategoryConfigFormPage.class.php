<?php

class CustomCategoryConfigFormPage extends WebPage{

	private $configObj;

	private $attrDao;
	private $fieldTable = array();
	private $optionTable = array();
	//private $customTable = array(); //カスタムサーチフィールド用
	private $categoryId;
	private $lang;
	private $options = array();

	function __construct(){
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		SOY2::import("util.SOYShopPluginUtil");
		$this->attrDao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
	}

	function doPost(){

		if(isset($_POST["upload"])){
			$urls = $this->uploadImage();

			echo "<html><head>";
			echo "<script type=\"text/javascript\">";
			if($urls !== false){
				foreach($urls as $url){
					echo 'window.parent.ImageSelect.notifyUpload("' . $url . '");';
				}
			}else{
				echo 'alert("failed");';
			}
			echo "</script></head><body></body></html>";
			exit;
		}
		
		if(soy2_check_token()){
			//カスタムフィールド、カスタムオプション
			if(isset($_POST["LanguageConfig"])){
				$indexes = array("LanguageConfig", "custom_field");
				foreach($indexes as $idx){
					foreach($_POST[$idx] as $key => $value) {
						if(is_array($value)) $value = (count($value)) ? implode(",", $value) : null; // checkboxes対策
						$attr = soyshop_get_category_attribute_object($this->categoryId, $key);
						$attr->setValue($value);
						if(isset($_POST[$idx][$key."_option"]) && strlen(trim($_POST[$idx][$key."_option"]))) $attr->setValue2(trim($_POST[$idx][$key."_option"]));
						soyshop_save_category_attribute_object($attr);
					}
				}
			}

			//カスタムサーチフィールド
			// if(isset($_POST["custom_search"]) && count($_POST["custom_search"])){
			// 	SOY2Logic::createInstance("module.plugins.custom_search_field.logic.DataBaseLogic")->save($this->categoryId, $_POST["custom_search"], $this->lang);
			// }

			SOY2PageController::jump("Config.Detail?plugin=util_multi_language&category_id=" . $this->categoryId . "&language=" . $this->lang . "&updated");
		}

		SOY2PageController::jump("Config.Detail?plugin=util_multi_language&category_id=" . $this->categoryId . "&language=" . $this->lang . "&failed");
	}

	function execute(){

		//多言語化のプレフィックスを取得できない場合
		if(!isset($_GET["category_id"])) SOY2PageController::jump("Item.Category");
		if(!isset($_GET["language"])) SOY2PageController::jump("Item.Detail." . $_GET["item_id"]);

		$this->categoryId = $_GET["category_id"];
		$this->lang = $_GET["language"];

		//カテゴリを取得
		$category = soyshop_get_category_object($this->categoryId);
		if(!is_numeric($category->getId())) SOY2PageController::jump("Item.Detail");

		//日本語用のものだけ集める
		self::setLangFieldList();

		parent::__construct();

		$this->addLink("category_name_link", array(
			"text" => $category->getName(),
			"link" => SOY2PageController::createLink("Item.Category.Detail." . $this->categoryId)
		));

		$this->addLink("confirm_link", array(
			"link" => self::getConfirmLink(),
			"target" => "_blank"
		));

		$this->addLabel("category_name_with_lang", array(
			"text" => $category->getName() . " - " . UtilMultiLanguageUtil::getLanguageText($this->lang)
		));

		$this->addForm("form");

		DisplayPlugin::toggle("customfield", count($this->fieldTable) >= 0);
		$this->addLabel("customfield", array(
			"html" => self::buildForm()
		));

		DisplayPlugin::toggle("customsearch", false);
		// DisplayPlugin::toggle("customsearch", count($this->customTable));
		// $this->addLabel("customsearch", array(
		// 	"html" => (count($this->customTable)) ? self::buildCustomSearchForm() : ""
		// ));

		// //upload
		$this->addForm("upload_form");

		$this->createAdd("image_list","_common.Category.ImageListComponent", array(
			"list" => $category->getAttachments()
		));
	}

	private function setLangFieldList(){
		foreach(SOYShop_CategoryAttributeConfig::load() as $field){
			$accord = false;

			foreach(UtilMultiLanguageUtil::getConfig() as $conf){
				if(isset($conf["prefix"]) && strlen($conf["prefix"])){
					if(strpos($field->getFieldId(), "_" . $conf["prefix"]) !== false) $accord = true;
				}
			}
			//一致してないfieldIdを集める
			if(!$accord) $this->fieldTable[] = $field;
		}

		//カスタムサーチフィールド
		// if(SOYShopPluginUtil::checkIsActive("custom_search_field")){
		// 	SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
		// 	$this->customTable = CustomSearchFieldUtil::getConfig();
		// }
	}

	private function getConfirmLink(){
		$category = soyshop_get_category_object($this->categoryId);
		if(!is_numeric($category->getId())) return null;

		return null;

		// $page = soyshop_get_page_object((int)$category->getDetailPageId());
		// if(!is_numeric($page->getId())) return null;

		// return soyshop_get_page_url((string)$page->getUri(), (string)$category->getAlias()) . "?language=" . $this->lang;
	}

	private function buildForm(){
		$html = array();

		$key = "category_name_" . $this->lang;

		//先頭に商品名(多言語)のフォームを追加
		$html[] = "<div class=\"form-group\">";
		$html[] = "<label>カテゴリ名(" . $this->lang . ")</label>";
		$field = soyshop_get_category_attribute_object($this->categoryId, $key);
		$html[] = "<input name=\"LanguageConfig[" . $key . "]\" value=\"" . $field->getValue() . "\" type=\"text\" class=\"form-control\">";
		$html[] = "</div>";

		$config = UtilMultiLanguageUtil::getConfig();
		$langConf = $config[$this->lang];

		if(count($this->fieldTable) > 0 && strlen($langConf["prefix"])){
			foreach($this->fieldTable as $field){
				$obj = soyshop_get_category_attribute_object($this->categoryId, $field->getFieldId() . "_" . $langConf["prefix"]);

				if(is_null($obj->getValue()) || strlen((string)$obj->getValue()) === 0){
					$obj = soyshop_get_category_attribute_object($this->categoryId, $field->getFieldId() . "_" . $this->lang);
				}

				$field->setFieldId($field->getFieldId() . "_" . $this->lang);
				$html[] = $field->getForm((string)$obj->getValue(), (string)$obj->getValue2());
			}
		}

		return implode("\n", $html);
	}

	private function buildOptionForm(){
		$html = array();

		$accord = false;

		foreach($this->options as $key => $option){
			foreach(UtilMultiLanguageUtil::getConfig() as $conf){
				if(isset($conf["prefix"]) && strlen($conf["prefix"])){
					if(strpos($key, "_" . $conf["prefix"]) !== false) $accord = true;
				}
			}
			//一致してないoptionIdを集める
			if(!$accord){
				$option["type"] = (isset($option["type"])) ? $option["type"] : "select";
				$this->optionTable[$key] = $option;
			}
		}

		$config = UtilMultiLanguageUtil::getConfig();
		$langConf = $config[$this->lang];
		if(strlen($langConf["prefix"])){

			$prefix = "item_option_";

			foreach($this->optionTable as $key => $option){
				$obj = soyshop_get_item_attribute_object($this->categoryId, $prefix . $key . "_" . $langConf["prefix"]);

				if(is_null($obj->getValue()) || strlen((string)$obj->getValue()) === 0){
					$obj = soyshop_get_item_attribute_object($this->categoryId, $prefix . $key . "_" . $this->lang);
				}
				$obj->setFieldId($prefix . $key . "_" . $langConf["prefix"]);
				$html[] = self::buildTextArea($obj, $key);
			}
		}

		return implode("\n", $html);
	}

	private function buildTextArea(SOYShop_ItemAttribute $attr, $key){

		//古いバージョンから使用していて、typeの値がない場合はselectにする
//		$type = (isset($value["type"])) ? $value["type"] : "select";

		$type = ($this->optionTable[$key]["type"] == "select") ? "セレクトボックス" : "ラジオ";
		$optionName = (isset($this->optionTable[$key]["name_" . $this->lang])) ? $this->optionTable[$key]["name_" . $this->lang] : $this->optionTable[$key]["name"];

		$html = array();
		$html[] = "<div class=\"form-group\">";
		$html[] = "<label for=\"" . $attr->getFieldId() . "\">オプション名：" . $optionName . "&nbsp;&nbsp;タイプ：" . $type . "</label>";
		$html[] = "<textarea name=\"custom_field[" . $attr->getFieldId() . "]\" class=\"form-control\">" . $attr->getValue() . "</textarea>";
		$html[] = "</div>";
		return implode("\n", $html);
	}

	private function buildCustomSearchForm(){
		$html = array();

		if(count($this->customTable)){
			//登録されている値を取得
			SOY2::import("module.plugins.custom_search_field.component.FieldFormComponent");
			$values = SOY2Logic::createInstance("module.plugins.custom_search_field.logic.DataBaseLogic")->getBycategoryId($this->categoryId, $this->lang);

			// @ToDo HTMLを組み立てる
			foreach($this->customTable as $key => $field){
				$html[] = "<label>" . $field["label"] . " (" . CustomSearchFieldUtil::PLUGIN_PREFIX . ":id=\"" . $key . "\")</label>";
				$value = (isset($values[$key])) ? $values[$key] : null;
				$html[] = FieldFormComponent::buildForm($key, $field, $value, $this->lang);
			}
		}

		return implode("\n", $html);
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}

	/**
	 * 画像のアップロード
	 *
	 * @return url
	 * 失敗時には false
	 */
	function uploadImage(){
		$category = soyshop_get_category_object($this->categoryId);

		$urls = array();

		foreach($_FILES as $upload){
			foreach($upload["name"] as $key => $value){
				//replace invalid filename
				$upload["name"][$key] = strtolower(str_replace("%","",rawurlencode($upload["name"][$key])));

				$pathinfo = pathinfo($upload["name"][$key]);
				if(!isset($pathinfo["filename"]))$pathinfo["filename"] = str_replace("." . $pathinfo["extension"], $pathinfo["basename"]);

				//get unique file name
				$counter = 0;
				$filepath = "";
				$name = "";
				while(true){
					$name = ($counter > 0) ? $pathinfo["filename"] . "_" . $counter . "." . $pathinfo["extension"] : $pathinfo["filename"] . "." . $pathinfo["extension"];
					$filepath = $category->getAttachmentsPath() . $name;

					if(!file_exists($filepath)) break;
					$counter++;
				}

				//一回でも失敗した場合はfalseを返して終了（rollbackは無し）
				$result = move_uploaded_file($upload["tmp_name"][$key], $filepath);
				@chmod($filepath,0604);

				if($result){
					$url = $category->getAttachmentsUrl() . $name;
					$urls[] = $url;
				}else{
					return false;
				}
			}
		}

		return $urls;
	}
}
