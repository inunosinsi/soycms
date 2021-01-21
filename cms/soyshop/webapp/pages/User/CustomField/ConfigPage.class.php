<?php
/**
 * @class Item.CustomField.ConfigPage
 * @date 2010-02-16T21:14:24+09:00
 * @author SOY2HTMLFactory
 */
class ConfigPage extends WebPage{

	function doPost(){

		if(isset($_POST["import"]) && strlen(trim($_POST["configure"])) > 0){
			$value = trim($_POST["configure"]);
			$value = base64_decode($value);

			$dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
			$config = soy2_unserialize($value);
			if(is_array($config)){
				SOYShop_UserAttributeConfig::save($config);
			}else{
				SOY2PageController::jump("User.CustomField.Config?failed");
			}

			SOY2PageController::jump("User.CustomField?updated");
			exit;
		}

	}

	function __construct(){
    	//利用権限があるか
    	$correct = class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_user_customfield"));
    	if(!$correct){
    		SOY2PageController::jump("User");
    	}

		parent::__construct();

		$this->createAdd("import_form","HTMLForm");

		$dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
		$config = SOYShop_UserAttributeConfig::load();
		$value = base64_encode(soy2_serialize($config));

		$this->createAdd("export_value","HTMLTextArea", array(
			"value" => $value,
			"style" => "height:200px;",
			"onclick" => "this.select();"
		));

		$this->createAdd("import_value","HTMLTextArea", array(
			"name" => "configure",
			"style" => "height:200px;"
		));
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("インポート・エクスポート", array("User" => SHOP_USER_LABEL . "管理", "User.CustomField" => "ユーザカスタム項目管理"));
	}
}
