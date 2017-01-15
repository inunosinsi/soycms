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
		
		SOY2::import("module.plugins." . self::FIELD_ID . ".component.FieldFormComponent");
		foreach(CustomSearchFieldUtil::getConfig() as $key => $field){
			$html[] = "<dt>" . $field["label"] . " (" . CustomSearchFieldUtil::PLUGIN_PREFIX . ":id=\"" . $key . "\")</dt>";
			$html[] = "<dd>";
			
			$value = (isset($values[$key])) ? $values[$key] : null;
			$html[] = FieldFormComponent::buildForm($key, $field, $value);
			$html[] = "</dd>";
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
			
			$htmlObj->addModel($key . "_visible", array(
				"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
				"visible" => (strlen($values[$key]))
			));
			
			$htmlObj->addLabel($key, array(
				"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
				"html" => (isset($values[$key])) ? $values[$key] : null
			));
			
			switch($field["type"]){
				case CustomSearchFieldUtil::TYPE_CHECKBOX:
					if(strlen($field["option"])){
						$vals = explode(",", $values[$key]);
						$opts = explode("\n", $field["option"]);
						foreach($opts as $i => $opt){
							$opt = trim($opt);
							$htmlObj->addModel($key . "_"  . $i . "_visible", array(
								"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
								"visible" => (in_array($opt, $vals))
							));
							
							$htmlObj->addLabel($key . "_" . $i, array(
								"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
								"text" => $opt
							));
						}
					}
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