<?php
/*
 */
class CommonItemCustomField extends SOYShopItemCustomFieldBase{

	private $itemAttributeDao;
	private $prefix;	//多言語化のプレフィックスを保持
	private $fieldTable = array();	//商品IDに紐づいたすべてのカスタムフィールドのオブジェクトを保持する

	function doPost(SOYShop_Item $item){
		self::prepare();

		$list = (isset($_POST["custom_field"])) ? $_POST["custom_field"] : array();
		$extraFields = (isset($_POST["custom_field_extra"])) ? $_POST["custom_field_extra"] : null;

		$array = $this->itemAttributeDao->getByItemId($item->getId());

		$configs = SOYShop_ItemAttributeConfig::load(true);

		foreach($list as $key => $value){

			if(!isset($configs[$key])) continue;
			$extra = (isset($extraFields[$key])) ? $extraFields[$key] : array();

			//type=checkboxesの時
			if($configs[$key]->getType() === "checkboxes"){
				$value = (isset($value) && count($value)) ? implode(",", $value) : null;
			}

			try{
				if(isset($array[$key])){
					$obj = $array[$key];
					$obj->setValue($value);
					$obj->setExtraValuesArray($extra);
					$this->itemAttributeDao->update($obj);
				}else{
					$obj = new SOYShop_ItemAttribute();
					$obj->setItemId($item->getId());
					$obj->setFieldId($key);
					$obj->setValue($value);
					$obj->setExtraValuesArray($extra);

					$this->itemAttributeDao->insert($obj);
				}
			}catch(Exception $e){
				//
			}

			if($configs[$key]->isIndex()){
				//毎回DAOを読み込まなければソート用のカラムに値が入ってくれない
				$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
				$itemDAO->updateSortValue($item->getId(), $key, $value);
			}
		}

		//チェックボックスが非選択時の処理
		foreach($configs as $key => $value){

			try{
				if(!isset($list[$key]) && isset($array[$key])){
					$obj = $array[$key];
					$obj->setValue("");
					$this->itemAttributeDao->update($obj);
				}
			}catch(Exception $e){
				//
			}
		}

	}

	function getForm(SOYShop_Item $item){

		self::prepare();

		try{
			$array = $this->itemAttributeDao->getByItemId($item->getId());
		}catch(Exception $e){
			echo $e->getPDOExceptionMessage();
		}

		$html = array();
		$list = SOYShop_ItemAttributeConfig::load();
		if(!count($list)) return "";

		$associationMode = false;	//カテゴリとの関連付けモード

		foreach($list as $config){
			$value = (isset($array[$config->getFieldId()])) ? $array[$config->getFieldId()]->getValue() : null;
			$extraValues = (isset($array[$config->getFieldId()])) ? $array[$config->getFieldId()]->getExtraValuesArray() : null;

			$html[] = $config->getForm($value, $extraValues);

			//関連付けモードを起動するか調べる
			if(!$associationMode && strlen($config->getShowInput()) && is_numeric($config->getShowInput())) $associationMode = true;
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

		foreach($list as $config){
			if(!strlen($config->getShowInput())) continue;
			$html[] = 'isCategory = (categoryId == ' . $config->getShowInput() . ');';
			$html[] = 'if(!isCategory){';	//親カテゴリの方にあるか調べる
			$html[] = '	if(customfield_category_map[categoryId].length > 0){';
			$html[] = '		isCategory = (customfield_category_map[categoryId].indexOf(' . $config->getShowInput(). ') >= 0);';
			$html[] = '	}';
			$html[] = '}';

			$html[] = 'if(isCategory){';
			// $html[] = '	$("#custom_field_' . $config->getFieldId() . '_dt").show();';
			// $html[] = '	$("#custom_field_' . $config->getFieldId() . '").show();';
			$html[] = '	$("#custom_field_' . $config->getFieldId() . '_group").show();';
			$html[] = '}else{';
			//$html[] = '	$("#custom_field_' . $config->getFieldId() . '_dt").hide();';
			//$html[] = '	$("#custom_field_' . $config->getFieldId() . '").hide();';
			$html[] = '	$("#custom_field_' . $config->getFieldId() . '_group").hide();';
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

		self::prepare();

		if(!is_null($item->getId())){
			try{
				//多言語の方の値も一緒に取得してる
				$this->fieldTable = $this->itemAttributeDao->getByItemId($item->getId());
			}catch(Exception $e){
			}
		}

		$list = SOYShop_ItemAttributeConfig::load();

		foreach($list as $config){
			$value = self::getFieldValue($config->getFieldId(), $config->getEmptyValue());

			//空の時の挙動
			if(!is_null($config->getConfig()) && (is_null($value) || !strlen($value))){
				$fieldConf = $config->getConfig();
				if(isset($fieldConf["hideIfEmpty"]) && !$fieldConf["hideIfEmpty"] && isset($fieldConf["emptyValue"])){
					$value = $fieldConf["emptyValue"];
				}
			}

			$valueLength = strlen(trim(strip_tags($value)));

			$htmlObj->addModel($config->getFieldId() . "_visible", array(
				"visible" => ($valueLength > 0),
				"soy2prefix" => SOYSHOP_SITE_PREFIX
			));

			switch($config->getType()){

				case "image":

					/**
					 * 隠し機能:携帯自動振り分け、多言語化プラグイン用で画像の配置場所を別で用意する
					 * @ToDo 管理画面でもいじれる様にしたい
					 */
					$value = soyshop_convert_file_path($value, $item);

					$extraValues = (isset($this->fieldTable[$config->getFieldId()])) ? $this->fieldTable[$config->getFieldId()]->getExtraValuesArray() : array();
					if(!count($extraValues) && strlen($config->getExtraOutputs())){	//追加属性があるかだけ調べておく
						$outputs = explode("\n", $config->getExtraOutputs());
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


					$htmlObj->createAdd($config->getFieldId(), $class, $attr);

					$htmlObj->addLink($config->getFieldId() . "_link",  array(
						"link" => $value,
						"soy2prefix" => SOYSHOP_SITE_PREFIX
					));

					$htmlObj->addLabel($config->getFieldId() . "_text", array(
						"text" => $value,
						"soy2prefix" => SOYSHOP_SITE_PREFIX
					));
					break;

				case "textarea":
					if(strlen($config->getOutput()) > 0){
						$htmlObj->addModel($config->getFieldId(), array(
							"attr:" . htmlspecialchars($config->getOutput()) => soyshop_customfield_nl2br($value),
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}else{
						$htmlObj->addLabel($config->getFieldId(), array(
							"html" => soyshop_customfield_nl2br($value),
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}
					break;
				case "link":
					if(strlen($config->getOutput()) > 0){
						$htmlObj->addModel($config->getFieldId(), array(
							"attr:" . htmlspecialchars($config->getOutput()) => $value,
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}else{
						$htmlObj->addLink($config->getFieldId(), array(
							"link" => $value,
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}

					$htmlObj->addLabel($config->getFieldId() . "_text", array(
						"text" => $value,
						"soy2prefix" => SOYSHOP_SITE_PREFIX
					));

					break;

				default:
					if(strlen($config->getOutput()) > 0){
						if($config->getOutput() == "href" && $config->getType() != "link"){
							$htmlObj->addLink($config->getFieldId(), array(
								"link" => $value,
								"soy2prefix" => SOYSHOP_SITE_PREFIX
							));
						}else{
							$htmlObj->addModel($config->getFieldId(), array(
								"attr:" . htmlspecialchars($config->getOutput()) => $value,
								"soy2prefix" => SOYSHOP_SITE_PREFIX
							));
						}
					}else{
						$htmlObj->addLabel($config->getFieldId(), array(
							"html" => $value,
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}
			}

			$htmlObj->addLabel($config->getFieldId()."_raw", array(
				"html" => $value,
				"soy2prefix" => SOYSHOP_SITE_PREFIX
			));
			$htmlObj->addLabel($config->getFieldId()."_escaped", array(
					"text" => $value,
					"soy2prefix" => SOYSHOP_SITE_PREFIX
			));
			$htmlObj->addModel($config->getFieldId()."_is_empty", array(
					"visible" => ($valueLength === 0),
					"soy2prefix" => SOYSHOP_SITE_PREFIX
			));
			$htmlObj->addLabel($config->getFieldId()."_is_not_empty", array(
					"visible" => ($valueLength > 0),
					"soy2prefix" => SOYSHOP_SITE_PREFIX
			));
		}
	}

	function onDelete($id){
		$attributeDAO = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$attributeDAO->deleteByItemId($id);
	}

	private function prepare(){
		if(!$this->itemAttributeDao){
			$this->itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		}

		//多言語の方も念のため
		if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", "jp");

		//多言語化のプレフィックスでも調べてみる
		if(is_null($this->prefix) && SOYSHOP_PUBLISH_LANGUAGE != "jp"){
			SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
			if(class_exists("UtilMultiLanguageUtil")){
				$config = UtilMultiLanguageUtil::getConfig();
				$this->prefix = (isset($config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"])) ? trim($config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"]) : SOYSHOP_PUBLISH_LANGUAGE;
			}
		}
	}

	private function getFieldValue($fieldId, $emptyValue = null){
		$value = null;

		//多言語化の値をとる
		if(SOYSHOP_PUBLISH_LANGUAGE != "jp") {
			$value = (isset($this->fieldTable[$fieldId . "_" . SOYSHOP_PUBLISH_LANGUAGE])) ? $this->fieldTable[$fieldId . "_" . SOYSHOP_PUBLISH_LANGUAGE]->getValue() : null;

			//多言語化のプレフィックスの方でも値を取得してみる
			if(is_null($value) && SOYSHOP_PUBLISH_LANGUAGE != $this->prefix){
				$value = (isset($this->fieldTable[$fieldId . "_" . $this->prefix])) ? $this->fieldTable[$fieldId . "_" . $this->prefix]->getValue() : null;
			}
		}

		//多言語の方で値を取得できなかったら通常設定の値をとる
		if(is_null($value)) $value = (isset($this->fieldTable[$fieldId])) ? $this->fieldTable[$fieldId]->getValue() : null;

		//空の時の値
		if(is_null($value) || $value === ""){
			$value = $emptyValue;
		}

		return $value;
	}
}

SOYShopPlugin::extension("soyshop.item.customfield","common_customfield","CommonItemCustomField");
