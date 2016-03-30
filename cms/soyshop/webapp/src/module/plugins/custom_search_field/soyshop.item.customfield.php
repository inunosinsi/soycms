<?php
class CustomSearchField extends SOYShopItemCustomFieldBase{

	const FIELD_ID = "custom_search_field";
	private $dbLogic;

	/**
	 * 管理画面側で商品情報を更新する際に読み込まれる
	 * 設定内容をデータベースに放り込む
	 * @param object SOYShop_Item
	 */
	function doPost(SOYShop_Item $item){
		
		if(isset($_POST["custom_search"])){
			self::prepare();
			$this->dbLogic->save($item->getId(), $_POST["custom_search"]);
		}
	}

	/**
	 * 管理画面側の商品詳細画面でフォームを表示します。
	 * @param object SOYShop_Item
	 * @return string html
	 */
	function getForm(SOYShop_Item $item){
		
		self::prepare();
		$values = $this->dbLogic->getByItemId($item->getId());
		
		$html = array();
		
		foreach(CustomSearchFieldUtil::getConfig() as $key => $field){
			$html[] = "<dt>" . $field["label"] . " (" . CustomSearchFieldUtil::PLUGIN_PREFIX . ":id=\"" . $key . "\")</dt>";
			
			switch($field["type"]){
				case CustomSearchFieldUtil::TYPE_STRING:
					$value = (isset($values[$key])) ? $values[$key] : null;
					$html[] = "<dd><input type=\"text\" name=\"custom_search[" . $key . "]\" value=\"" . $value . "\" style=\"width:100%;\"></dd>";
					break;
				case CustomSearchFieldUtil::TYPE_TEXTAREA:
					$value = (isset($values[$key])) ? $values[$key] : null;
					$html[] = "<dd><textarea name=\"custom_search[" . $key . "]\" style=\"width:100%;\">" . $value . "</textarea></dd>";
					break;
				case CustomSearchFieldUtil::TYPE_RICHTEXT:
					$value = (isset($values[$key])) ? $values[$key] : null;
					$html[] = "<dd><textarea class=\"custom_field_textarea mceEditor\" name=\"custom_search[" . $key . "]\">" . $value . "</textarea></dd>";
					break;
				case CustomSearchFieldUtil::TYPE_INTEGER:
				case CustomSearchFieldUtil::TYPE_RANGE:
					$value = (isset($values[$key])) ? $values[$key] : null;
					$html[] = "<dd><input type=\"number\" name=\"custom_search[" . $key . "]\" value=\"" . $value . "\"></dd>";
					break;
				case CustomSearchFieldUtil::TYPE_CHECKBOX:
					if(isset($field["option"]) && strlen(trim($field["option"])) > 0){
						$chks = explode(",", $values[$key]);	//valuesを配列化
						$options = explode("\n", $field["option"]);
						$html[] = "<dd>";
						foreach($options as $option){
							$oVal = trim($option);
							if(in_array($oVal, $chks)){
								$html[] = "<label><input type=\"checkbox\" name=\"custom_search[" . $key . "][]\" value=\"" . $oVal . "\" checked=\"\">" . $oVal . "</label>";
							}else{
								$html[] = "<label><input type=\"checkbox\" name=\"custom_search[" . $key . "][]\" value=\"" . $oVal . "\">" . $oVal . "</label>";
							}
						}
						$html[] = "</dd>";
					}
					break;
				case CustomSearchFieldUtil::TYPE_RADIO:
					if(isset($field["option"]) && strlen(trim($field["option"])) > 0){
						$options = explode("\n", $field["option"]);
						$html[] = "<dd>";
						foreach($options as $option){
							$oVal = trim($option);
							if($oVal === $values[$key]){
								$html[] = "<label><input type=\"radio\" name=\"custom_search[" . $key . "]\" value=\"" . $oVal . "\" checked=\"\">" . $oVal . "</label>";
							}else{
								$html[] = "<label><input type=\"radio\" name=\"custom_search[" . $key . "]\" value=\"" . $oVal . "\">" . $oVal . "</label>";
							}
						}
						$html[] = "</dd>";
					}
					break;
				case CustomSearchFieldUtil::TYPE_SELECT:
					if(isset($field["option"]) && strlen(trim($field["option"])) > 0){
						$options = explode("\n", $field["option"]);
						$html[] = "<dd>";
						$html[] = "<select name=\"custom_search[" . $key . "]\">";
						$html[] = "<option value=\"\"></option>";
						foreach($options as $option){
							$oVal = trim($option);
							if($oVal === $values[$key]){
								$html[] = "<option value=\"" . $oVal . "\" selected=\"selected\">" . $oVal . "</option>";
							}else{
								$html[] = "<option value=\"" . $oVal . "\">" . $oVal . "</option>";
							}
						}
						$html[] = "</select>";
						$html[] = "</dd>";
					}
					break;
			}
		}
		
		
		return implode("\n", $html);
	}
	
	/**
	 * 公開側のblock:id="item"で囲まれた箇所にフォームを出力する
	 * @param object htmlObj, object SOYShop_Item
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		self::prepare();
		$values = $this->dbLogic->getByItemId($item->getId());
		
		foreach(CustomSearchFieldUtil::getConfig() as $key => $field){
			
			$htmlObj->addLabel($key . "_visible", array(
				"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
				"visible" => (strlen($values[$key]))
			));
			
			switch($field["type"]){
				default:
					$htmlObj->addLabel($key, array(
						"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
						"html" => (isset($values[$key])) ? $values[$key] : null
					));
					break;
			}
		}
	}

	/**
	 * 管理画面側で商品情報を削除した時にオプション設定も一緒に削除する
	 * @param integer id
	 */
	function onDelete($itemId){
		self::prepare();
		$this->dbLogic->delete($itemId);
	}
	
	private function prepare(){
		if(!$this->dbLogic){
			$this->dbLogic = SOY2Logic::createInstance("module.plugins.custom_search_field.logic.DataBaseLogic");
			SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
		}
		
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "custom_search_field", "CustomSearchField");
?>