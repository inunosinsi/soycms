<?php

class CommonItemCustomField extends SOYShopItemCustomFieldBase{

	private $fieldTable = array();	//商品IDに紐づいたすべてのカスタムフィールドのオブジェクトを保持する

	function __construct(){
		SOY2::import("domain.shop.SOYShop_ItemAttribute");

		//多言語の方も念のため
		if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", "jp");

		if(!defined("SOYSHOP_PUBLISH_LANGUAGE_POSTFIX")){	//カスタムフィールドの場合は多言語化プラグインのprefixがpostfixとして使用する
			//多言語化のプレフィックスでも調べてみる
			if(SOYSHOP_PUBLISH_LANGUAGE != "jp"){
				SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
				if(class_exists("UtilMultiLanguageUtil")){
					$cnf = UtilMultiLanguageUtil::getConfig();
					$postfix = (isset($cnf[SOYSHOP_PUBLISH_LANGUAGE]["prefix"])) ? trim($cnf[SOYSHOP_PUBLISH_LANGUAGE]["prefix"]) : SOYSHOP_PUBLISH_LANGUAGE;
					define("SOYSHOP_PUBLISH_LANGUAGE_POSTFIX", $postfix);
				}
			}
		}
		if(!defined("SOYSHOP_PUBLISH_LANGUAGE_POSTFIX")) define("SOYSHOP_PUBLISH_LANGUAGE_POSTFIX", SOYSHOP_PUBLISH_LANGUAGE);
	}

	function doPost(SOYShop_Item $item){
		$list = (isset($_POST["custom_field"])) ? $_POST["custom_field"] : array();
		$extraFields = (isset($_POST["custom_field_extra"])) ? $_POST["custom_field_extra"] : null;

		SOY2::import("module.plugins.common_customfield.util.CustomfieldUtil");
		$array = (is_numeric($item->getId())) ? CustomfieldUtil::getFieldValues($item->getId()) : array();

		$configs = SOYShop_ItemAttributeConfig::load(true);

		if(count($list) && count($configs)){
			foreach($list as $fieldId => $value){

				if(!isset($configs[$fieldId])) continue;
				$extra = (isset($extraFields[$fieldId])) ? $extraFields[$fieldId] : null;
				
				//type=checkboxesの時
				if($configs[$fieldId]->getType() === "checkboxes"){
					$value = (isset($value) && count($value)) ? implode(",", $value) : null;
				}

				$obj = (isset($array[$fieldId])) ? $array[$fieldId] : soyshop_get_item_attribute_object($item->getId(), $fieldId);
				$obj->setValue($value);
				$obj->setExtraValuesArray($extra);
				soyshop_save_item_attribute_object($obj);

				if($configs[$fieldId]->isIndex()){
					//毎回DAOを読み込まなければソート用のカラムに値が入ってくれない
					SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->updateSortValue($item->getId(), $fieldId, $value);
				}
			}

			//チェックボックスが非選択時の処理
			foreach($configs as $fieldId => $cnf){
				if(isset($list[$fieldId])) continue;
				if($cnf->getType() != "checkbox" && $cnf->getType() != "checkboxes" && $cnf->getType() != "radio") continue;
				$attr = soyshop_get_item_attribute_object($item->getId(), $fieldId);
				$attr->setValue(null);
				$attr->setExtraValues(null);
				soyshop_save_item_attribute_object($attr);
			}
		}
	}

	function getForm(SOYShop_Item $item){
		$list = SOYShop_ItemAttributeConfig::load(true);
		if(!count($list)) return "";

		SOY2::import("module.plugins.common_customfield.util.CustomfieldUtil");
		$array = (is_numeric($item->getId())) ? CustomfieldUtil::getFieldValues($item->getId()) : array();

		$html = array();

		$associationMode = false;	//カテゴリとの関連付けモード

		foreach($list as $fieldId => $config){
			$value = (isset($array[$fieldId])) ? $array[$fieldId]->getValue() : null;
			$extraValues = (isset($array[$fieldId])) ? $array[$fieldId]->getExtraValuesArray() : null;

			$html[] = $config->getForm($value, $extraValues);

			//関連付けモードを起動するか調べる
			if(!$associationMode && is_string($config->getShowInput()) && strlen($config->getShowInput()) && is_numeric($config->getShowInput())) $associationMode = true;
		}

		if(!$associationMode) return implode("\n", $html);

		//カテゴリマップ
		$map = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->getMapping();
		if(!count($map)) return implode("\n", $html);

		//カテゴリとの関連付けのJavaScript
		$html[] = "<script>";

		//カテゴリマップの連想配列を組み立てる
		$html[] = "var customfield_category_map = {";
		foreach($map as $categoryId => $children){
			$html[] = "	\"" . $categoryId . "\" : [" . implode(",", $children) . "],";
		}
		$html[] = "};";

		$html[] = "setInterval(function(){";
		$html[] = 'var categoryId = $("#item_category").val();';
		$html[] = 'var isCategory';

		foreach($list as $fieldId => $config){
			if(!strlen($config->getShowInput())) continue;
			$html[] = 'isCategory = (categoryId == ' . $config->getShowInput() . ');';
			$html[] = 'if(!isCategory){';	//親カテゴリの方にあるか調べる
			$html[] = '	if(customfield_category_map[categoryId].length > 0){';
			$html[] = '		isCategory = (customfield_category_map[categoryId].indexOf(' . $config->getShowInput(). ') >= 0);';
			$html[] = '	}';
			$html[] = '}';

			$html[] = 'if(isCategory){';
			$html[] = '	$("#custom_field_' . $fieldId . '_group").show();';
			$html[] = '}else{';
			$html[] = '	$("#custom_field_' . $fieldId . '_group").hide();';
			$html[] = '}';
		}
		$html[] = "}, 1000);";
		$html[] = "</script>";

		return implode("\n", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		$list = SOYShop_ItemAttributeConfig::load(true);
		if(!count($list)) return;

		SOY2::import("module.plugins.common_customfield.util.CustomfieldUtil");
		$this->fieldTable = (is_numeric($item->getId())) ? CustomfieldUtil::getFieldValues($item->getId()) : array();
		
		foreach($list as $fieldId => $config){
			$value = self::getFieldValue($fieldId, $config->getEmptyValue());

			//空の時の挙動
			if(!is_null($config->getConfig()) && (is_null($value) || !strlen($value))){
				$fieldConf = $config->getConfig();
				if(isset($fieldConf["hideIfEmpty"]) && !$fieldConf["hideIfEmpty"] && isset($fieldConf["emptyValue"])){
					$value = $fieldConf["emptyValue"];
				}
			}

			$valueLength = (is_string($value)) ? strlen(trim(strip_tags($value))) : 0;

			$htmlObj->addModel($fieldId . "_visible", array(
				"visible" => ($valueLength > 0),
				"soy2prefix" => SOYSHOP_SITE_PREFIX
			));

			switch($config->getType()){
				case "image":
					/**
					 * 隠し機能:携帯自動振り分け、多言語化プラグイン用で画像の配置場所を別で用意する
					 * @ToDo 管理画面でもいじれる様にしたい
					 */
					$value = (is_string($value)) ? soyshop_convert_file_path($value, $item) : null;

					$extraValues = (isset($this->fieldTable[$fieldId])) ? $this->fieldTable[$fieldId]->getExtraValuesArray() : array();
					if(!count($extraValues) && strlen((string)$config->getExtraOutputs())){	//追加属性があるかだけ調べておく
						$outputs = explode("\n", (string)$config->getExtraOutputs());
						if(count($outputs)){
							foreach($outputs as $out){
								$out = trim($out);
								if(!strlen($out)) continue;
								$extraValues[$out] = "";
							}
						}
					}
					if(strlen($config->getOutput() > 0)){
						$class = "HTMLModel";
						$attr = array(
							"attr:" . htmlspecialchars($config->getOutput()) => $value,
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						);
						foreach($extraValues as $key => $extraValue){
							$attr["attr:" . trim(htmlspecialchars($key))] = trim(htmlspecialchars($extraValue));
						}
					}else{
						$class = "HTMLImage";
						$attr = array(
							"src" => $value,
							"soy2prefix" => SOYSHOP_SITE_PREFIX,
							"visible" => (isset($value) && strlen($value))
						);
						foreach($extraValues as $key => $extraValue){
							$attr[trim(htmlspecialchars($key))] = trim(htmlspecialchars($extraValue));
						}
					}

					$htmlObj->createAdd($fieldId, $class, $attr);

					$htmlObj->addLink($fieldId . "_link",  array(
						"link" => $value,
						"soy2prefix" => SOYSHOP_SITE_PREFIX
					));

					$htmlObj->addLabel($fieldId . "_text", array(
						"text" => $value,
						"soy2prefix" => SOYSHOP_SITE_PREFIX
					));
					break;

				case "textarea":
					if(is_string($config->getOutput()) && strlen($config->getOutput()) > 0){
						$htmlObj->addModel($fieldId, array(
							"attr:" . htmlspecialchars($config->getOutput()) => (is_string($value)) ? soyshop_customfield_nl2br($value) : "",
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}else{
						$htmlObj->addLabel($fieldId, array(
							"html" => (is_string($value)) ? soyshop_customfield_nl2br($value) : "",
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}
					break;
				case "link":
					if(strlen($config->getOutput()) > 0){
						$htmlObj->addModel($fieldId, array(
							"attr:" . htmlspecialchars($config->getOutput()) => $value,
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}else{
						$htmlObj->addLink($fieldId, array(
							"link" => $value,
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}

					$htmlObj->addLabel($fieldId . "_text", array(
						"text" => $value,
						"soy2prefix" => SOYSHOP_SITE_PREFIX
					));
					break;

				default:
					if(is_string($config->getOutput()) && strlen($config->getOutput()) > 0){
						if($config->getOutput() == "href" && $config->getType() != "link"){
							$htmlObj->addLink($fieldId, array(
								"link" => $value,
								"soy2prefix" => SOYSHOP_SITE_PREFIX
							));
						}else{
							$htmlObj->addModel($fieldId, array(
								"attr:" . htmlspecialchars($config->getOutput()) => $value,
								"soy2prefix" => SOYSHOP_SITE_PREFIX
							));
						}
					}else{
						$htmlObj->addLabel($fieldId, array(
							"html" => $value,
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}
			}

			$htmlObj->addLabel($fieldId . "_raw", array(
				"html" => $value,
				"soy2prefix" => SOYSHOP_SITE_PREFIX
			));
			$htmlObj->addLabel($fieldId . "_escaped", array(
					"text" => $value,
					"soy2prefix" => SOYSHOP_SITE_PREFIX
			));
			$htmlObj->addModel($fieldId . "_is_empty", array(
					"visible" => ($valueLength === 0),
					"soy2prefix" => SOYSHOP_SITE_PREFIX
			));
			$htmlObj->addLabel($fieldId . "_is_not_empty", array(
					"visible" => ($valueLength > 0),
					"soy2prefix" => SOYSHOP_SITE_PREFIX
			));
		}
	}

	function onDelete(int $itemId){
		SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->deleteByItemId($id);
	}

	private function getFieldValue(string $fieldId, $emptyValue=null){
		if(!is_array($this->fieldTable) || !count($this->fieldTable)) return null;

		$value = null;

		//多言語化の値をとる
		if(SOYSHOP_PUBLISH_LANGUAGE != "jp") {
			$value = (isset($this->fieldTable[$fieldId . "_" . SOYSHOP_PUBLISH_LANGUAGE])) ? $this->fieldTable[$fieldId . "_" . SOYSHOP_PUBLISH_LANGUAGE]->getValue() : null;

			//多言語化のプレフィックスの方でも値を取得してみる
			if(is_null($value) && SOYSHOP_PUBLISH_LANGUAGE != SOYSHOP_PUBLISH_LANGUAGE_POSTFIX){
				$value = (isset($this->fieldTable[$fieldId . "_" . SOYSHOP_PUBLISH_LANGUAGE_POSTFIX])) ? $this->fieldTable[$fieldId . "_" . SOYSHOP_PUBLISH_LANGUAGE_POSTFIX]->getValue() : null;
			}
		}

		//多言語の方で値を取得できなかったら通常設定の値をとる
		if(is_null($value)) $value = (isset($this->fieldTable[$fieldId])) ? $this->fieldTable[$fieldId]->getValue() : null;

		//空の時の値
		if(is_null($value) || $value === "") $value = $emptyValue;

		return $value;
	}
}

SOYShopPlugin::extension("soyshop.item.customfield","common_customfield","CommonItemCustomField");
