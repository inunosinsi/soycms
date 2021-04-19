<?php
/**
 * @class Item.CustomField.IndexPage
 * @date 2009-12-01T20:34:16+09:00
 * @author SOY2HTMLFactory
 */
class IndexPage extends WebPage{

	function doPost(){

		if(isset($_POST["create"])){
			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			$configs = SOYShop_ItemAttributeConfig::load();

			$custom_id = $_POST["custom_id"];

			//ダウンロード販売用と多言語化プラグインのIDとバッティングしないようにする
			if(
				!preg_match('/^download_assistant_(.*)/', $custom_id) &&
				!preg_match('/^item_name_(.*)/', $custom_id)
			){
				$config = new SOYShop_ItemAttributeConfig();
				$config->setLabel($_POST["custom_new_name"]);
				$config->setFieldId($custom_id);
				$config->setType($_POST["custom_type"]);

				$configs[] = $config;

				SOYShop_ItemAttributeConfig::save($configs);
				SOY2PageController::jump("Item.CustomField?updated=created");
			}else{
				SOY2PageController::jump("Item.CustomField?error=error");
			}

		}

		//update
		if(isset($_POST["update_submit"])){
			$fieldId = $_POST["update_submit"];

			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			$configs = SOYShop_ItemAttributeConfig::load(true);

			$config = $configs[$fieldId];
			SOY2::cast($config, (object)$_POST["obj"]);

			SOYShop_ItemAttributeConfig::save($configs);
		}

		//advanced config
		if(isset($_POST["update_advance"])){
			$fieldId = $_POST["update_advance"];

			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			$configs = SOYShop_ItemAttributeConfig::load(true);

			$config = $configs[$fieldId];
			$config->setConfig($_POST["config"]);

			SOYShop_ItemAttributeConfig::save($configs);
		}

		//delete
		if(isset($_POST["delete_submit"])){
			$fieldId = $_POST["delete_submit"];

			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			$configs = SOYShop_ItemAttributeConfig::load(true);

			unset($configs[$fieldId]);

			SOYShop_ItemAttributeConfig::save($configs);
		}

		//move
		if(isset($_POST["move_up"]) || isset($_POST["move_down"])){
			$fieldId = $_POST["field_id"];

			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			$configs = SOYShop_ItemAttributeConfig::load(true);

			$keys = array_keys($configs);
			$currentKey = array_search($fieldId, $keys);
			$swap = (isset($_POST["move_up"])) ? $currentKey - 1 :$currentKey + 1;

			if($swap >= 0 && $swap < count($keys)){
				$tmp = $keys[$currentKey];
				$keys[$currentKey] = $keys[$swap];
				$keys[$swap] = $tmp;

				$tmpArray = array();
				foreach($keys as $index => $value){
					$field = $configs[$value];
					$tmpArray[$field->getFieldId()] = $field;
				}

				SOYShop_ItemAttributeConfig::save($tmpArray);
			}
		}

		//隠しモード　項目を一番下へ
		if(isset($_POST["move_bottom"])){
			$fieldId = $_POST["field_id"];

			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			$configs = SOYShop_ItemAttributeConfig::load(true);

			$keys = array_keys($configs);
			$currentKey = array_search($fieldId, $keys);
			//最後の番号
			$swap = count($keys) + 1;

			$tmp = $keys[$currentKey];
			$keys[$currentKey] = $keys[$swap];
			$keys[$swap] = $tmp;

			$tmpArray = array();
			foreach($keys as $index => $value){
				if(isset($value)){
					$field = $configs[$value];
					$tmpArray[$field->getFieldId()] = $field;
				}
			}

			SOYShop_ItemAttributeConfig::save($tmpArray);

		}

		SOY2PageController::jump("Item.CustomField?updated");
	}

	function __construct(){
		if(!AUTH_CONFIG) SOY2PageController::jump("Item");

		parent::__construct();

		DisplayPlugin::toggle("error", isset($_GET["error"]));

		$this->addForm("create_form");


		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$config = SOYShop_ItemAttributeConfig::load();

		$types = SOYShop_ItemAttributeConfig::getTypes();
		$this->addSelect("custom_type_select", array(
			"options" => $types,
			"name" => "custom_type"
		));

		$this->createAdd("field_list", "_common.Item.FieldListComponent", array(
			"list" => $config,
			"types" => $types
		));
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("カスタム項目管理", array("Item" => "商品管理"));
	}

	function getFooterMenu(){
		try{
			return SOY2HTMLFactory::createInstance("Item.FooterMenu.ItemCustomfieldFooterMenuPage")->getObject();
		}catch(Exception $e){
			//
			return null;
		}
	}
}
