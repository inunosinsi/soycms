<?php
class CommonItemOptionCustomField extends SOYShopItemCustomFieldBase{

	private $prefix;	//多言語化のプレフィックスを保持
	private $itemAttributeDAO;
	private $optionList = array();

	/**
	 * 管理画面側で商品情報を更新する際に読み込まれる
	 * 設定内容をデータベースに放り込む
	 * @param object SOYShop_Item
	 */
	function doPost(SOYShop_Item $item){
		
		if(isset($_POST["item_option"])){
			$itemId = $item->getId();
			
			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			
			$options = $_POST["item_option"];
			foreach($options as $key => $value){
				
				try{
					$dao->delete($itemId, "item_option_" . $key);
				}catch(Exception $e){
					//
				}
				
				$obj = new SOYShop_ItemAttribute();
				$obj->setItemId($itemId);
				$obj->setFieldId("item_option_" . $key);
				$obj->setValue($value);
				
				try{
					$dao->insert($obj);
				}catch(Exception $e){
					//
				}
			}
		}
	}

	/**
	 * 管理画面側の商品詳細画面でフォームを表示します。
	 * @param object SOYShop_Item
	 * @return string html
	 */
	function getForm(SOYShop_Item $item){
		self::prepare();
		$logic = SOY2Logic::createInstance("module.plugins.common_item_option.logic.ItemOptionLogic");
		$types = $logic->getTypes();
		
		$html = array();
		
		$html[] = "<h1>商品オプションの設定</h1>";
		$html[] = "<dd>";
		$html[] = "<p>商品のオプション項目のセレクトボックスを作成します。<br />";
		$html[] = "表示したいオプション項目を改行で区切って入力してください。</p>";
		$html[] = "</dd>";
		
		foreach($this->optionList as $key => $value){
			$html[] = $this->buildTextArea($key, $value, $item->getId(), $types);
		}
		
		return implode("\n", $html);
	}
	
	/**
	 * プラグイン詳細で設定したオプションのフォームを出力する
	 * @param string key, string value, integer itemId
	 * @retrun string html
	 */
	function buildTextArea($key, $value, $itemId, $types){
		
		$obj = $this->getFieldValue($key, $itemId);
		
		//古いバージョンから使用していて、typeの値がない場合はselectにする
		$type = (isset($value["type"])) ? $value["type"] : "select";
		
		$html = array();
		
		$html[] = "<dt>";
		$html[] = "<label for=\"item_option_" . $key . "\">オプション名：" . $value["name"] . "&nbsp;タイプ：" . $types[$type] . "</label>";
		$html[] = "</dt>";
		$html[] = "<dd>";
		$html[] = "<textarea name=\"item_option[" . $key . "]\">" . $obj->getValue() . "</textarea>";
		$html[] = "</dd>";
		
		return implode("\n", $html);
	}
	
	/**
	 * 公開側のblock:id="item"で囲まれた箇所にフォームを出力する
	 * @param object htmlObj, object SOYShop_Item
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		self::prepare();
		$cart = CartLogic::getCart();
		
		foreach($this->optionList as $key => $value){
			
			$html = array();
			
			$name = "item_option[" . $key . "]";
			
			//古いバージョンから使用していて、typeの値がない場合はselectにする
			$type = (isset($value["type"])) ? $value["type"] : "select";
			$obj = $this->getFieldValue($key, $item->getId());
			
			if(strlen($obj->getValue()) > 0){
				
				$options = explode("\n", trim($obj->getValue()));
				
				//選択したタイプによって、HTMLの出力を変える
				switch($type){
					case "radio":
						$first = true;
						foreach($options as $option){
							if($first){
								$html[] = "<label><input type=\"radio\" name=\"" . $name . "\" value=\"" . $option . "\" checked=\"checked\">" . $option . "</label><br>";
								$first = false;
							}else{
								$html[] = "<label><input type=\"radio\" name=\"" . $name . "\" value=\"" . $option . "\">" . $option . "</label><br>";
							}
							
						}
						break;
						
					case "select":
					default:
					
						$html[] = "<select name=\"" . $name . "\">";
					
						foreach($options as $option){
							$option = str_replace(array("\r", "\n"), "", $option);
							$html[] = "<option>" . $option . "</option>";
						}
					
						$html[] = "</select>";
						break;
				}
			}else{
				$html[] = "";
			}
									
			$htmlObj->addModel($key . "_visible", array(
				"soy2prefix" => SOYSHOP_SITE_PREFIX,
				"visible" => (strlen($obj->getValue()) > 0)
			));

			$htmlObj->addLabel($key, array(
				"soy2prefix" => SOYSHOP_SITE_PREFIX,
				"html" => implode("\n", $html)
			));
		}
	}

	/**
	 * 管理画面側で商品情報を削除した時にオプション設定も一緒に削除する
	 * @param integer id
	 */
	function onDelete($id){
		$attributeDAO = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$attributeDAO->deleteByItemId($id);
	}
	
	/**
	 * 値を取得するメソッド
	 * @param string key, integer itemId
	 * @return object SOYShop_ItemAttribute
	 */
	function getFieldValue($key, $itemId){
		$key = "item_option_" . $key;
		
		if(SOYSHOP_PUBLISH_LANGUAGE != "jp"){
			$obj = self::get($itemId, $key . "_" . SOYSHOP_PUBLISH_LANGUAGE);
			
			if(is_null($obj) && SOYSHOP_PUBLISH_LANGUAGE != $this->prefix){
				$obj = self::get($itemId, $key . "_" . $this->prefix);
			}
		}
		
		//多言語化の方の値を取得できなかった場合
		if(!isset($obj) || is_null($obj)) $obj = self::get($itemId, $key);
		
		//取得できなければ、空のオブジェクトを返す
		if(is_null($obj)) $obj = new SOYShop_ItemAttribute;
		
		return $obj;
	}
	
	private function get($itemId, $key){
		try{
			$obj = $this->itemAttributeDAO->get($itemId, $key);
		}catch(Exception $e){
			$obj = null;
		}
		return $obj;
	}
	
	private function prepare(){
		if(!$this->itemAttributeDAO){
			$this->itemAttributeDAO = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		}
		
		if(!$this->optionList){
			$this->optionList = SOY2Logic::createInstance("module.plugins.common_item_option.logic.ItemOptionLogic")->getOptions();
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
}

SOYShopPlugin::extension("soyshop.item.customfield", "common_item_option", "CommonItemOptionCustomField");
?>