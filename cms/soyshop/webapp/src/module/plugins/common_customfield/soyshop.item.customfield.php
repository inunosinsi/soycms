<?php
/*
 */
class CommonItemCustomField extends SOYShopItemCustomFieldBase{

	private $itemAttributeDao;
	private $prefix;	//多言語化のプレフィックスを保持
	private $fieldTable = array();	//商品IDに紐づいたすべてのカスタムフィールドのオブジェクトを保持する

	function doPost(SOYShop_Item $item){
		$this->prepare();

		$list = (isset($_POST["custom_field"])) ? $_POST["custom_field"] : array();
		$extraFields = (isset($_POST["custom_field_extra"])) ? $_POST["custom_field_extra"] : null;

		$array = $this->itemAttributeDao->getByItemId($item->getId());

		$configs = SOYShop_ItemAttributeConfig::load(true);
		
		foreach($list as $key => $value){

			if(!isset($configs[$key])) continue;
			$extra = (isset($extraFields[$key])) ? $extraFields[$key] : array();

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
		
		$this->prepare();

		try{
			$array = $this->itemAttributeDao->getByItemId($item->getId());
		}catch(Exception $e){
			echo $e->getPDOExceptionMessage();
		}

		$html = array();
		$list = SOYShop_ItemAttributeConfig::load();

		foreach($list as $config){
			$value = (isset($array[$config->getFieldId()])) ? $array[$config->getFieldId()]->getValue() : null;
			$extraValues = (isset($array[$config->getFieldId()])) ? $array[$config->getFieldId()]->getExtraValuesArray() : null;

			$html[] = $config->getForm($value, $extraValues);
		}

		return implode("\n", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		
		$this->prepare();
		
		if(!is_null($item->getId())){
			try{
				//多言語の方の値も一緒に取得してる
				$this->fieldTable = $this->itemAttributeDao->getByItemId($item->getId());
			}catch(Exception $e){
			}
		}
		
		$list = SOYShop_ItemAttributeConfig::load();
		
		foreach($list as $config){
			$value = self::getFieldValue($config->getFieldId());
			
			$htmlObj->addModel($config->getFieldId() . "_visible", array(
				"visible" => (strlen(strip_tags($value)) > 0),
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
							"attr:" . htmlspecialchars($config->getOutput()) => nl2br($value),
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}else{
						$htmlObj->addLabel($config->getFieldId(), array(
							"html" => nl2br($value),
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}
					break;
				case "link":
					if(strlen($config->getOutput()) > 0){
						$htmlObj->addModel($config->getFieldId(), array(
							"attr:" . htmlspecialchars($config->getOutput()) => nl2br($value),
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}else{
						$htmlObj->addLink($config->getFieldId(), array(
							"link" => nl2br($value),
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
		}
	}

	function onDelete($id){
		$attributeDAO = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$attributeDAO->deleteByItemId($id);
	}

	function prepare(){
		if(!$this->itemAttributeDao){
			$this->itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		}
		
		//多言語の方も念のため
		if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", "jp");
		
		//多言語化のプレフィックスでも調べてみる
		if(is_null($this->prefix) && SOYSHOP_PUBLISH_LANGUAGE != "jp"){
			SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
			$config = UtilMultiLanguageUtil::getConfig();
			$this->prefix = (isset($config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"])) ? trim($config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"]) : SOYSHOP_PUBLISH_LANGUAGE;
		}
	}
	
	private function getFieldValue($fieldId){
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
		
		return $value;
	}
}

SOYShopPlugin::extension("soyshop.item.customfield","common_customfield","CommonItemCustomField");
?>